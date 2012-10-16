<?php

Cb::import('CbAbstractProvider');

/**
 * Template providers return dumb templates to be filled from JS. No
 * authentication is necessary and the templates are expected as an array of
 * name => absolute_file_name.
 */
class CbResourceProvider extends CbAbstractProvider {
   protected $default_template; ///< Resource to be used if none is specified.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param CbResourceHandler $default_handler Handler to be called for unspecified methods.
    * @param CbAuthorizationProvider $auth_provider Authorization provider. If null anything is allowed.
    * @param string $default_resource Default resource to be used if none is specified.
    * @param CbContentFormatter $formatter Content formatter for the output.
    * Alternately specify all params except handlers as hash in second parameter
    */
   public function __construct(array $templates = array(), $default_template) {
      $params = array(
         'formatter' => new CbHtmlFileFormatter(),
         'cache_type' => 'public',
         'cache_strategy' => 'modified_since'
      );
      parent::__construct(null, $params);
      $this->default_template = $default_template;
   }

   private function resolveTemplate($request)
   {
      return isset($request['template']) ? $request['template'] : $this->default_template;
   }

   protected function execHandler($method, $request) {
      if ($method !== 'get') {
         throw new CbApiException(403, 'You cannot modify templates.');
      }
      return $this->resolveTemplate($request);
   }

   protected function getMetadata($method, $request)
   {
      if ($method !== 'get') {
         throw new CbApiException(403, 'You cannot modify templates.');
      }
      return array('last_changed' => filemtime($this->resolveTemplate($request)));
   }
}