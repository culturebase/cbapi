<?php
/* This file is part of cbapi.
 * Copyright © 2011-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
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

Cb::import('CbContentFormatterInterface');

class CbHtmlFormatter implements CbContentFormatterInterface {

   protected $config;
   protected $name;
   protected $content;

   public function contentType()
   {
      return "text/html ;charset=utf-8";
   }

   private function javascript()
   {
      $js = $this->config['bootstrap_javascript'];
      if (is_string($js)) $js = array($js);
      if (is_array($js)) {
         foreach($js as $script) {
            echo "<script type='text/javascript' src='$script'></script>";
         }
      }
   }

   private function title()
   {
      echo htmlentities($this->name);
   }

   private function debug()
   {
      return isset($this->config['debug']) && $this->config['debug'] === true;
   }

   private function content($content = null, $level = 1)
   {
      if ($content === null) $content = $this->content->get();
      if (!is_array($content)) {
         if ($content === false) {
            $content = 'false';
         } else if ($content === true) {
            $content = 'true';
         }
         echo "<div>".htmlentities($content)."</div>";
      } else {
         foreach($content as $key => $val) {
            if ($val !== null && $val !== '') {
               if (!is_int($key)) {
                  if ($level < 7) {
                     echo "<h$level>".htmlentities($key)."</h$level>";
                  } else {
                     echo "<span>".htmlentities($key)."</span>";
                  }
               }
               $this->content($val, $level + 1);
            }
         }
      }
   }

   public function format($name, $content)
   {
      $this->name = $name;
      $this->content = $content;
      require 'templates/html_format.inc.php';
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}