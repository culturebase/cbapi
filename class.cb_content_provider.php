<?php

Cb::import('CbAbstractProvider', 'CbRequestHandler');

/**
 * Content providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You can either subclass this and override the handle method or register
 * handlers for specific methods. You can also specify an authorization provider
 * which checks if the specified method is allowed. If no authorization provider
 * is given all actions are allowed.
 */
class CbContentProvider extends CbAbstractProvider {
   protected $default_method;  ///< Method to be called if none is specified.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param CbRequestHandler $default_handler Handler to be called for unspecified methods.
    * @param CbAuthorizationProvider $auth_provider Authorization provider. If null anything is allowed.
    * @param string $default_method Default method to be called if none is specified.
    * @param CbContentFormatter $formatter Content formatter for the output.
    */
   function __construct(array $handlers = array(),
           CbRequestHandlerInterface $default_handler = null,
           CbAuthorizationProviderInterface $auth_provider = null,
           string $default_method = null,
           CbContentFormatter $formatter = null, $cache_timeout = 3600) {
      parent::__construct($handlers,
            $default_handler ? $default_handler : new CbRequestHandler(),
            $auth_provider, $formatter, $cache_timeout);
      $this->default_method = $default_method;
   }

   protected function execHandler($method, $request) {
      $handler = isset($this->handlers[$method]) ? $this->handlers[$method] : $this->default_handler;
      return $handler->handle($request);
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   function handle(array $request = null) {
      if (!$request) $request = array_merge($_COOKIE, $_POST, $_GET);
      if (!isset($request['method']) && isset($this->default_method)) {
         $request['method'] = $this->default_method;
      }
      return parent::handle($request);
   }
}