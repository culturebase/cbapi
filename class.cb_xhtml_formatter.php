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

class CbXhtmlFormatter implements CbContentFormatterInterface {

   protected $config;

   public function contentType()
   {
      return "application/xhtml+xml";
   }

   private function content($document, $element, $content, $depth = 1)
   {
      foreach($content as $key => $val) {
         if (!is_int($key)) {
            if ($depth < 7) {
               $title = $document->createElement('h'.$depth, htmlspecialchars($key, ENT_QUOTES));
            } else {
               $title = $document->createElement('span', htmlspecialchars($key, ENT_QUOTES));
            }
            $element->appendChild($title);
         }
         if ($val !== null && $val !== '') {
            if (!is_array($val)) {
               $child = $document->createElement('div', htmlspecialchars($val, ENT_QUOTES));
            } else {
               $child = $document->createElement('div');
               $this->content($document, $child, $val, $depth + 1);
            }
            $element->appendChild($child);
         }
      }
   }

   public function format($name, $content)
   {
      $content = $content->get();
      $doctype = DOMImplementation::createDocumentType('html',
            '-//W3C//DTD XHTML 1.1//EN',
            'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd');

      $document = DOMImplementation::createDocument(
            'http://www.w3.org/1999/xhtml', 'html', $doctype);
      $html = $document->getElementsByTagName('html')->item(0);

      $head = $document->createElement('head');
      $title = $document->createElement('title', htmlspecialchars($name, ENT_QUOTES));
      $head->appendChild($title);

      $js = $this->config['bootstrap_javascript'];
      if (is_string($js)) $js = array($js);
      if (is_array($js)) {
         foreach($js as $script) {
            $el = $document->createElement('script');
            $src = $document->createAttribute('src');
            $src->appendChild($document->createTextNode(htmlspecialchars($script, ENT_QUOTES)));
            $el->appendChild($src);
            $type = $document->createAttribute('type');
            $type->appendChild($document->createTextNode('text/javascript'));
            $el->appendChild($type);
            $head->appendChild($el);
         }
      }

      $html->appendChild($head);

      if (!is_array($content)) {
         $root = $document->createElement("body", htmlspecialchars($content, ENT_QUOTES));
      } else {
         $root = $document->createElement("body");
         $this->content($document, $root, $content);
      }
      $html->appendChild($root);
      echo $document->saveXML();
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}