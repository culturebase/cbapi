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

   /**
    * Create an abstract provider.
    * @param array $handlers Handlers for various methods.
    * @param $default_handler Handler to be called for unspecified methods.
    * @param CbAuthorizationProvider $auth_provider Authorization provider. If null anything is allowed.
    * @param CbContentFormatter $formatter Content formatter for the output.
    */
   protected function __construct(array $handlers = array(), $params = array()) {
      $this->cache_provider = $params['cache_provider'] ?
            $params['cache_provider'] : new CbCacheProvider();
      $this->auth_provider = $params['auth_provider'];
      $this->handlers = $handlers;
      $this->default_handler = $params['default_handler'];
      $this->formatter = $params['formatter'] ?
            $params['formatter'] : new CbContentFormatter();
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   public function handle(array $request = null) {
      header('Content-type: ' . $this->formatter->contentType());

      if (!$request) $request = array_merge($_COOKIE, $_POST, $_GET);
      $method = isset($request['method']) ? $request['method'] : strtolower($_SERVER['REQUEST_METHOD']);

      try {
         if (!$this->cache_provider->run($this->getMetadata($method, $request))) {
            return;
         }

         if ($this->auth_provider) CbSession::start();

         /* allow inline HTTP login; as we provide 401 we should do this. */
         if (isset($_SERVER['PHP_AUTH_USER']) && (!isset($_SESSION['auth']) ||
                 !isset($_SESSION['auth']['isAuthenticated']) ||
                 $_SESSION['auth']['account'] != $_SERVER['PHP_AUTH_USER'])) {
            CbAuth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
         }

         // TODO: there is a standard for providing the HTTP method via POST or GET.
         if ($this->auth_provider) $this->auth_provider->assert($method, $request);
         $result = $this->execHandler($method, $request);
      } catch (CbApiException $e) {
         $e->outputHeaders();
         $result = $e->getUserData();
      }
      $this->formatter->format($result);
   }

   abstract protected function execHandler($method, $request);

   abstract protected function getMetadata($method, $request);
}