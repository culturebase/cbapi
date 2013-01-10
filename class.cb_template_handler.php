<?php

class CbTemplateHandler implements CbResourceHandlerInterface {

   protected $default_template; ///< Resource to be used if none is specified.
   protected $templates;

   /**
    * Create a template provider.
    * @param $config Global configuration.
    */
   public function __construct($config)
   {
      $this->default_template = $config['default_template'];
      $this->templates = $config['templates'];
   }

   private function resolveTemplate($request)
   {
      return isset($request['template']) ? $this->templates[$request['template']] : $this->default_template;
   }

   public function delete(array $params)
   {
      throw new CbApiException(403, 'You cannot modify templates.');
   }

   public function get(array $params)
   {
      return $this->resolveTemplate($params);
   }

   public function post(array $params)
   {
      throw new CbApiException(403, 'You cannot modify templates.');
   }

   public function put(array $params)
   {
      throw new CbApiException(403, 'You cannot modify templates.');
   }

   public function meta($request)
   {
      return array(
         'last_modified' => filemtime($this->resolveTemplate($request)),
         'privacy' => 'public',
         'name' => $request['template'],
         'formats' => array(
            'text/html' => 'CbHtmlFileFormatter'
         )
      );
   }
}