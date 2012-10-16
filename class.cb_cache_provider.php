<?php

class CbCacheProvider {
   private function condition_fails($only_412 = false, $only_304 = false) {
      if (!$only_412 && in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'))) {
         header('Not Modified', true, 304);
      } else if (!$only_304) {
         header('Precondition Failed', true, 412);
      } else {
         header('Bad Request', true, 400);
      }
   }

   /**
    * Output cache headers and determine if content should be created.
    * @param type $meta Meta data about content to be served. Can contain any
    *    combination of the following fields:
    * - "max_age" To indicate maximum age of cached content
    * - "privacy" Either of "private", "public", "no-cache" with the obvious
    *   implications
    * - "last_modified" To indicate the timestamp the content was last modified.
    *   Will be used to check if-modified-since and to generate Last-Modified
    * - "etag" Unique hash of the data to check if-none-match and generate
    *   Etag.
    * @return type true if content is necessary, false otherwise
    */
   public function run($meta = array()) {
      $content = true;
      if (isset($meta['max_age'])) {
         if (!isset($meta['privacy'])) $meta['privacy'] = 'private';
         session_cache_expire($meta['max_age'] / 60); // minutes ...
      }

      if (isset($meta['last_modified'])) {
         if (!isset($meta['privacy'])) $meta['privacy'] = 'private';
         if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
               strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $meta['last_modified']) {
            $content = false;
            $this->condition_fails(false, true);
         } else if (isset($_SERVER['HTTP_IF UNMODIFIED_SINCE']) &&
               $_SERVER['HTTP_IF_UNMODIFIED_SINCE'] < $meta['last_modified']) {
            $content = false;
            $this->condition_fails(true);
         } else {
            header("Last-Modified: ".gmdate('D, d M Y H:i:s', $meta['last_modified'])." GMT");
         }
      }

      if (isset($meta['etag'])) {
         $qetag = ($meta['etag_weak'] ? 'W/"' : '"').$meta['etag'].'"';
         if (!isset($meta['privacy'])) $meta['privacy'] = 'private';
         $match = isset($_SERVER['HTTP_IF_MATCH']) ?
               array_map("trim", explode(',', $_SERVER['HTTP_IF_MATCH'])) :
               array('*');
         $none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ?
               array_map("trim", explode(',', $_SERVER['HTTP_IF_NONE_MATCH'])) :
               array();
         if (in_array($qetag, $none_match) || in_array('*', $none_match)) {
            $content = false;
            $this->condition_fails();
         } else if (!in_array('*', $match) && !in_array($qetag, $match)) {
            $content = false;
            $this->condition_fails(true);
         } else {
            header('ETag: '.$qetag);
         }
      }

      if (isset($meta['privacy'])) {
         if ($meta['privacy'] === 'private' && isset($meta['max_age'])) {
            $meta['privacy'] = 'private_no_expire'; // silly PHP will set conflicting Expire and max-age otherwise
         }
         session_cache_limiter($meta['privacy']);
      }
      return $content;
   }
}