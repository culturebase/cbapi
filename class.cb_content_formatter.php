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

require_once 'HTTP.php';

class CbContentFormatter implements CbContentFormatterInterface {
   protected $name;
   protected $config;
   protected $default_format = 'text/html';

   protected $formats = array(
         'application/json'      => 'CbJsonFormatter',
         'text/html'             => 'CbHtmlFormatter',
         'application/xhtml+xml' => 'CbXhtmlFormatter',
         'application/xml'       => 'CbXmlFormatter',
         'text/plain'            => 'CbPlainFormatter'
   );

   /**
    * Shorthand notation for format overrides as part of URL.
    * @var type
    */
   protected $format_abbrevs = array(
         'json'  => 'application/json',
         'html'  => 'text/html',
         'xhtml' => 'application/xhtml+xml',
         'xml'   => 'application/xml',
         'txt'   => 'text/plain'
   );

   public function contentType()
   {
      throw new CbApiException(500,
            "You cannot request the content type from the meta formatter.");
   }

   /**
    * Negotiate the content type.
    * @param array $additional Additional or different formatters to be used.
    * @param string $override If set force the given format, even if content
    *                         negotiation would choose something else. You can
    *                         give a shorthand format specifier here.
    * @return CbContentFormatterInterface The chosen formatter.
    */
   public function negotiate($additional = null, $override = null)
   {
      if (is_array($additional)) {
         $this->formats = array_merge($this->formats, $additional);
      }
      if (isset($this->format_abbrevs[$override])) {
         $selected = $this->formats[$this->format_abbrevs[$override]];
      } else if (isset($this->formats[$override])) {
         $selected = $this->formats[$override];
      } else {
         $selected = $this->formats[HTTP::negotiateMimeType(
               array_keys($this->formats), $this->default_format)];
      }
      if (is_string($selected)) {
         $class = new ReflectionClass($selected);
         $selected = $class->newInstance($this->config);
      }
      return $selected;
   }

   public function format($name, $content_adapter)
   {
      throw new CbApiException(500,
            "You cannot use the meta formatter for actual formatting.");
   }

   public function __construct($config)
   {
      $this->config = $config;
      if (isset($config['default_format'])) {
         $this->default_format = $config['default_format'];
      }
      if (is_array($config['formats'])) {
         $this->formats = array_merge($this->formats, $config['formats']);
      }
   }
}
