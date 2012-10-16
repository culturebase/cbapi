<?php

class CbCacheProvider {
   protected $params;

   public function __construct($params) {
      if (isset($params['cache_timeout'])) {
         session_cache_expire($params['cache_timeout'] / 60); // minutes ...
      }
      session_cache_limiter(isset($params['cache_type']) ? $params['cache_type'] : 'private');
      $this->params = $params;
   }

   /**
    * Output cache headers and determine if content should be created.
    * @param type $content_properties
    * @return type true if content is necessary, false otherwise
    */
   public function run($meta = array()) {
      if (isset($this->params['cache_strategy']) && method_exists($this, $this->params['cache_strategy'])) {
         return $this->{$this->params['cache_strategy']}($meta);
      } else {
         return true;
      }
   }

   protected function max_age($meta) {
      return true; // if the request arrives here we have to generate content ...
   }

   protected function modified_since($meta) {
      if (isset($meta['modified']) && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $meta['modified']) {
         header('Not Modified', true, 304);
         return false;
      } else {
         header("Last-Modified: ".gmdate('D, d M Y H:i:s', $meta['modified'])." GMT", true);
         return true;
      }
   }

   protected function etag($meta) {
      // TODO: implement
      return true;
   }
}