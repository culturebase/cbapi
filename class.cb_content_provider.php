<?php

/**
 * Content providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You can either subclass this and override the handle method or register
 * handlers for specific methods. You can also specify an authorization provider
 * which checks if the specified method is allowed. If no authorization provider
 * is given all actions are allowed.
 */
class CbContentProvider {
   protected $auth_provider;   ///< Authorization provider to check ACLs.
   protected $handlers;        ///< Handlers for specific methods.
   protected $default_handler; ///< Handler to be called if no specific handler is given.
   protected $default_method;  ///< Method to be called if none is specified.
   protected $formatter;       ///< Formatter for the output.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param CbRequestHandler $default_handler Handler to be called for unspecified methods.
    * @param CbAuthorizationProvider $auth_provider Authorization provider. If null anything is allowed.
    * @param string $default_method Default method to be called if none is specified.
    * @param CbContentFormatter $formatter Content formatter for the output.
    */
   function __construct(array $handlers = array(),
           CbRequestHandler $default_handler = null,
           CbAuthorizationProvider $auth_provider = null,
           string $default_method = '',
           CbContentFormatter $formatter = null) {
      $this->auth_provider = $auth_provider;
      if ($auth_provider) CbSession::start();
      $this->handlers = $handlers;
      $this->default_handler = $default_handler ? $default_handler : new CbRequestHandler();
      $this->default_method = $default_method;
      $this->formatter = $formatter ? $formatter : new CbContentFormatter();
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   function handle(array $request = null) {
      /* allow inline HTTP login; as we provide 401 we should do this. */
      if (isset($_SERVER['PHP_AUTH_USER']) && (!isset($_SESSION['auth']) ||
              !isset($_SESSION['auth']['isAuthenticated']) ||
              $_SESSION['auth']['account'] != $_SERVER['PHP_AUTH_USER'])) {
         CbAuth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
      }

      if (!$request) $request = $_REQUEST;
      $method = isset($request['method']) ? $request['method'] : $this->default_method;
      $handler = isset($this->handlers[$method]) ? $this->handlers['method'] : $this->default_handler;
      $result = '';
      try {
         if ($this->auth_provider) $auth_provider->assert($method, $request);
         $result = $handler->handle($request);
      } catch (CbApiExeption $e) {
         $e->outputHeaders();
         $result = $e->getMessage();
      }
      echo $this->formatter->format($result);
   }
}