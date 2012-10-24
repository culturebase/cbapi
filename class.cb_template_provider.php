<?php

Cb::import('CbAbstractProvider');

/**
 * Template providers return dumb templates to be filled from JS. No
 * authentication is necessary and the templates are expected as an array of
 * name => absolute_file_name.
 */
class CbTemplateProvider extends CbAbstractProvider {
   protected $default_template; ///< Resource to be used if none is specified.

   /**
    * Create a template provider.
    * @param array $templates Mapping of name => path for templates.
    * @param string $default_template Template to be used if none is given.
    */
   public function __construct(array $templates = array(), $default_template = null) {
      $params = array(
         'formatter' => new CbHtmlFileFormatter(),
      );
      parent::__construct(array(), $params);
      $this->default_template = $default_template;
      $this->templates = $templates;
   }

   private function resolveTemplate($request)
   {
      return isset($request['template']) ? $this->templates[$request['template']] : $this->default_template;
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
      return array(
         'last_modified' => filemtime($this->resolveTemplate($request)),
         'privacy' => 'public'
      );
   }
}