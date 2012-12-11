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

class CbPlainFormatter implements CbContentFormatterInterface {

   protected $config;
   protected $name;
   protected $content;

   public function contentType()
   {
      return "text/plain ;charset=utf-8";
   }

   private function content($content = null, $level = 0)
   {
      $margin = $level > 0 ? str_repeat("\t", $level - 1) : '';
      if ($content === null) $content = $this->content->get();
      if (!is_array($content)) {
         if ($content === false) {
            $content = 'false';
         } else if ($content === true) {
            $content = 'true';
         }
         echo "$margin$content\n";
      } else {
         foreach($content as $key => $val) {
            if (is_int($key)) $key = 'item '.$key;
            if (is_array($val)) {
               echo "\n$margin$key\n$margin".str_repeat('-', strlen($key))."\n";
            } else {
               echo "\n$margin$key:";
            }
            if ($val !== null && $val !== '') {
               $this->content($val, $level + 1);
            } else {
               echo "\n";
            }
         }
      }
   }

   public function format($name, $content)
   {
      echo "$name\n".str_repeat('=', strlen($name))."\n\n\n";
      $this->content($content->get());
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}