<?php

require_once 'HTTP.php';

/**
 * Default formatter creates JSON.
 */
class CbContentFormatter implements CbContentFormatterInterface {
   protected $types = array(
         'application/json'      => 'CbJsonFormatter',
         'text/html'             => 'CbHtmlFormatter',
         'application/xhtml+xml' => 'CbXhtmlFormatter',
         'application/xml'       => 'CbXmlFormatter',
         'text/plain'            => 'CbPlainFormatter'
   );
   
   protected $selected = null;
   
   public function contentType($additional = null) {
      if ($additional !== null) {
         $this->types = array_merge($this->types, $additional);
      }
      $type = $this->types[HTTP::negotiateMimeType(array_keys($this->types), 'application/json')];
      $class = new ReflectionClass($type);
      $this->selected = $class->newInstance();
      return $this->selected->contentType(); // may add charset info
   }

   public function format($content) {
      if ($this->selected === null) throw new CbApiException(500,
            "You have to negotiate a content type before formatting the content.");
      echo $this->selected->format($content);
   }
}
