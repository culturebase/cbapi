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

/**
 * Render a content-less HTML file. This is intended for "dumb" templates to be
 * used by Javascript user interfaces. The goal is to get allow for very
 * aggressive caching by not allowing any content in the HTML.
 */
class CbHtmlFileFormatter implements CbContentFormatterInterface {
   public function __construct() {}

   public function contentType()
   {
      return "text/html ;charset=utf-8";
   }

   /**
    * We expect that to be HTML already. For specialized treatment subclass
    * this.
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($name, $content)
   {
      $content = $content->get();
      if ($content === null) return;
      if (is_string($content)) {
         require $content;
      } else {
         throw new CbApiException(500, "Generic CbHtmlFileFormatter doesn't know what to do with a ".gettype($content));
      }
   }
}