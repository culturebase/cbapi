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

/**
 * Content formatters should create a string to be output from a result given
 * in any supported format.
 */
interface CbContentFormatterInterface {

   /**
    * @param $name Name of Resource or Method being handled.
    * @param $content Content to be formatted.
    */
   public function format($name, $content);

   /**
    * Return the content type this formatter will produce.
    */
   public function contentType();

   /**
    * If supported, also implement a negotiate($additional, $override) method
    * to figure out the content type.
    * @see CbContentFormatter
    */
}
