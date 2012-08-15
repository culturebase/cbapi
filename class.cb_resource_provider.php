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
    */
   public function __construct(array $handlers = array(),
           CbResourceHandlerInterface $default_handler = null,
           CbAuthorizationProviderInterface $auth_provider = null,
           string $default_resource = null,
           CbContentFormatter $formatter = null) {
      parent::__construct($handlers,
            isset($default_handler) ? $default_handler : new CbResourceHandler(),
            $auth_provider, $formatter);
      $this->default_resource = $default_resource;
   }

   protected function execHandler($method, $request) {
      $resource = isset($request['resource']) ? $request['resource'] : $this->default_resource;
      $handler = isset($this->handlers[$resource]) ? $this->handlers[$resource] : $this->default_handler;
      // TODO: throw a proper exception if method doesn't exist (and things like
      //       __call aren't implemented either).
      return $handler->$method($request);
   }

   public function handle(array $request = null) {
      // Make sure the method override is lowercase so that it matches ACLs
      // This cannot be done for the RPC interface as we accept arbitrary
      // methods there.
      if (isset($_POST['method'])) $_POST['method'] = strtolower ($_POST['methos']);
      return parent::handle($request);
   }
}