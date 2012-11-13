<?php

Cb::import('CbAbstractProvider', 'CbResourceHandlerInterface', 'CbResourceHandler', 'CbContentAdapter');

/**
 * Resource providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You should register handlers for specific resources. You can also specify an
 * authorization provider which checks if the specified method is allowed on the
 * selected resource. If no authorization provider is given all actions are
 * allowed.
 *
 * While CbRpcProvider ist descigned to provide an RPC-like interface this is
 * designed to provide a standard REST interface. Depending on what you want to
 * do you should choose one or the other.
 */
class CbResourceProvider extends CbAbstractProvider {
   protected $default_resource; ///< Resource to be used if none is specified.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param array $config Application Configuration.
    * @param @deprecated CbAuthorizationProvider $auth_provider Authorization Provider to be used.
    * @param @deprecated string $default_resource Default resource to be used if none is specified.
    * @param @deprecated CbContentFormatter $formatter Content formatter for the output.
    * @param @deprecated number $deprecated Ignored.
    */
   public function __construct(array $handlers = array(), $config = null,
         $auth_provider = null, string $default_resource = null,
         CbContentFormatter $formatter = null, $deprecated = null) {
      if (is_array($config)) {
         $config = array_merge(array(
            'default_handler' => $config['default_handler'] ? null : 'CbResourceHandler',
            'auth_provider' => null,
            'formatter' => null
         ), $config);
      } else {
         $config = array(
            'default_handler' => $config ? $config : 'CbResourceHandler',
            'auth_provider' => $auth_provider,
            'formatter' => $formatter
         );
      }
      parent::__construct($handlers, $config);
      $this->default_resource = isset($config['default_resource']) ?
         $config['default_resource'] : $default_resource;
   }

   private function resolveHandler($request) {
      $resource = isset($request['resource']) ? $request['resource'] : $this->default_resource;
      return $this->getHandler($resource);
   }

   protected function execHandler($method, $request) {
      // TODO: throw a proper exception if method doesn't exist (and things like
      //       __call aren't implemented either).
      $unsafe = in_array($method, array('delete', 'put', 'post'));
      return new CbContentAdapter($this->resolveHandler($request), $method, $request, $unsafe);
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
      $meta = array('name' => $request['resource']);
      if (method_exists($handler, 'meta')) {
         return array_merge($meta, $handler->meta($method, $request));
      } else {
         return $meta;
      }
   }
}