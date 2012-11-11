<?php

Cb::import('InterfaceCbAuthorizationProvider', 'CbAuthorizationProvider', 'CbContentFormatter', 'CbApiException');

/**
 * Providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You can specify an authorization provider which checks if the specified
 * method is allowed. If no authorization provider is given all actions are
 * allowed.
 */
abstract class CbAbstractProvider {
   protected $auth_provider;   ///< Authorization provider to check ACLs.
   protected $handlers;        ///< Handlers for specific methods.
   protected $default_handler; ///< Handler to be called if no specific handler is given.
   protected $formatter;       ///< Formatter for the output.
   protected $cache_provider;  ///< Timout for the HTTP cache.
   protected $config;          ///< Application configuration.

   protected function instantiate($thing)
   {
      if (is_string($thing)) {
         $class = new ReflectionClass($thing);
         $thing = $class->newInstance($this->config);
      }
      return $thing;
   }

   protected function getHandler($key)
   {
      if (isset($this->handlers[$key])) {
         return ($this->handlers[$key] = $this->instantiate($this->handlers[$key]));
      } else {
         return ($this->default_handler = $this->instantiate($this->default_handler));
      }
   }

   /**
    * Create an abstract provider.
    * @param array $handlers Handlers for various methods.
    * @param array $config Application Configuration.
    */
   protected function __construct(array $handlers = array(), $config = array())
   {
      $this->config = $config;
      $this->cache_provider = $config['cache_provider'] ?
            $this->instantiate($config['cache_provider']) :
            new CbCacheProvider($config);
      $this->auth_provider = $this->instantiate($config['auth_provider']);
      $this->handlers = $handlers;
      $this->default_handler = $config['default_handler'];
      $this->formatter = $config['formatter'] ?
            $this->instantiate($config['formatter']) :
            new CbContentFormatter($config);
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   public function handle(array $request = null)
   {
      if (!$request) $request = array_merge($_COOKIE, $_POST, $_GET);
      $method = isset($request['method']) ? $request['method'] : strtolower($_SERVER['REQUEST_METHOD']);
      $meta = $this->getMetadata($method, $request);
      header('Content-type: ' . $this->formatter->contentType(isset($meta['formats']) ? $meta['formats'] : null));

      try {
         if (!$this->cache_provider->run($meta)) return;

         if ($this->auth_provider) CbSession::start();

         /* allow inline HTTP login; as we provide 401 we should do this. */
         if (isset($_SERVER['PHP_AUTH_USER']) && (!isset($_SESSION['auth']) ||
                 !isset($_SESSION['auth']['isAuthenticated']) ||
                 $_SESSION['auth']['account'] !== $_SERVER['PHP_AUTH_USER'])) {
            CbAuth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
         }

         if ($this->auth_provider) $this->auth_provider->assert($method, $request);
         $result = $this->execHandler($method, $request);
      } catch (CbApiException $e) {
         $e->outputHeaders();
         $result = $e->getUserData();
      }
      try {
         $this->formatter->format(isset($meta['name']) ? $meta['name'] : '', $result);
      } catch (CbApiException $e) {
         $e->outputHeaders();
         echo "fatal error during output formatting: ".$e->getUserData();
      }
   }

   abstract protected function execHandler($method, $request);

   abstract protected function getMetadata($method, $request);
}