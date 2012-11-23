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

Cb::import("CbHtmlFormatter");

/**
 * HTML formatter that doesn't include any content. This is useful for pages
 * which shall be completely created by Javascript. You can set a cookie in the
 * client and depending on its value have the resource handler instruct the
 * provider to use this formatter. This will output an HTML document with only
 * Javascript and an empty body. Don't forget to state 'vary' => 'Cookie' in the
 * meta() method then.
 */
class CbEmptyHtmlFormatter extends CbHtmlFormatter {
   function content() {
      return '';
   }
}