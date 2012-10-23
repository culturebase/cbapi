<?php

Cb::import('CbAbstractProvider', 'InterfaceCbResourceHandler', 'CbResourceHandler');

/**
 * Resource providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You should register handlers for specific resources. You can also specify an
 * authorization provider which checks if the specified method is allowed on the
 * selected resource. If no authorization provider is given all actions are
 * allowed.
 *
 * While CbRequestHandler ist descigned to provide an RPC-like interface this is
 * designed to provide a standard REST interface. Depending on what you want to
 * do you should choose one or the other.
 */
class CbResourceProvider extends CbAbstractProvider {
   protected $default_resource; ///< Resource to be used if none is specified.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param CbResourceHandler $default_handler Handler to be called for unspecified methods.
    * @param CbAuthorizationProvider $auth_provider Authorization provider. If null anything is allowed.
    * @param string $default_resource Default resource to be used if none is specified.
    * @param CbContentFormatter $formatter Content formatter for the output.
    * Alternately specify all params except handlers as hash in second parameter
    */
   public function __construct(array $handlers = array(),
           CbResourceHandlerInterface $default_handler = null,
           CbAuthorizationProviderInterface $auth_provider = null,
           string $default_resource = null,
           CbContentFormatter $formatter = null, $cache_timeout = 3600) {
      if (is_array($default_handler)) {
         $params = array_merge(array(
            'default_handler' => $default_handler['default_handler'] ? null : new CbResourceHandler(),
            'auth_provider' => null,
            'formatter' => null,
            'cache_timeout' => 3600
         ), $default_handler);
      } else {
         $params = array(
            'default_handler' => $default_handler ? $default_handler : new CbResourceHandler(),
            'auth_provider' => $auth_provider,
            'formatter' => $formatter,
            'cache_timeout' => $cache_timeout
         );
      }
      parent::__construct($handlers, $params);
      $this->default_resource = $default_resource;
   }

   private function resolveHandler($request) {
      $resource = isset($request['resource']) ? $request['resource'] : $this->default_resource;
      return isset($this->handlers[$resource]) ? $this->handlers[$resource] : $this->default_handler;
   }

   protected function execHandler($method, $request) {
      // TODO: throw a proper exception if method doesn't exist (and things like
      //       __call aren't implemented either).
      return $this->resolveHandler()->$method($request);
   }

   public function handle(array $request = null) {
      // Make sure the method override is lowercase so that it matches ACLs
      // This cannot be done for the RPC interface as we accept arbitrary
      // methods there.
      if (isset($request['method'])) $request['method'] = strtolower($request['method']);
      if (isset($_POST['method'])) $_POST['method'] = strtolower($_POST['method']);
      parent::handle($request);
   }

   protected function getMetadata($method, $request)
   {
      $handler = $this->resolveHandler($request);
      return (method_exists($handler, 'meta') ?
            $handler->meta($method, $request) :
            array());
   }
}