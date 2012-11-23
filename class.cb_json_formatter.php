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

Cb::import('CbContentFormatterInterface');

class CbJsonFormatter implements CbContentFormatterInterface {

   public function __construct() {}

   /**
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($name, $content) {
      echo json_encode($content->get());
   }

   public function contentType() {
      return "application/json";
   }
}