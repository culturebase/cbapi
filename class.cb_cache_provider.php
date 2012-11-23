<?php
/* This file is part of cbapi.
 * Copyright © 2010-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
 *
 * cbapi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * cbapi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with cbapi.  If not, see <http://www.gnu.org/licenses/>.
 */

class CbCacheProvider {
   public function __construct() {}

   private function condition_fails($only_412 = false, $only_304 = false)
   {
      if (!$only_412 && in_array($_SERVER['REQUEST_METHOD'], array('GET', 'HEAD'))) {
         header('Not Modified', true, 304);
      } else if (!$only_304) {
         header('Precondition Failed', true, 412);
      } else {
         header('Bad Request', true, 400);
      }
   }

   /**
    * Emulate PHP's session cache so that we get the same caching with or
    * without starting a session. I know this stuff is braindead, but I strongly
    * suspect some of our applications are depending on it. So we cannot just
    * set session.cache_limiter to '' in the config.
    */
   private function sendHeaders()
   {
      $future = gmdate("D, d M Y H:i:s T", time() + session_cache_expire() * 60);
      $interval = session_cache_expire() * 60;
      switch (session_cache_limiter()) {
         case "public":
            header("Expires: $future");
            header("Cache-Control: public, max-age=$interval");
            break;
         case "private_no_expire":
            header("Cache-Control: private, max-age=$interval, pre-check=$interval");
            break;
         case "private":
            header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
            header("Cache-Control: private, max-age=$interval, pre-check=$interval");
            break;
         default: // covers 'nocache' and ''
            header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            break;
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
   public function run($meta = array())
   {
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
      $this->sendHeaders();
      return $content;
   }
}