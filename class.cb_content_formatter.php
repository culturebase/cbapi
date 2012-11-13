<?php

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

   protected $selected = null;

   public function contentType($additional = null)
   {
      if (is_array($additional)) {
         $this->formats = array_merge($this->formats, $additional);
      }
      $this->selected = $this->formats[HTTP::negotiateMimeType(array_keys($this->formats), $this->default_format)];
      if (is_string($this->selected)) {
         $class = new ReflectionClass($this->selected);
         $this->selected = $class->newInstance($this->config);
      }
      return $this->selected->contentType(); // may add charset info
   }

   public function format($name, $content_adapter)
   {
      if ($this->selected === null) throw new CbApiException(500,
            "You have to negotiate a content type before formatting the content.");
      echo $this->selected->format($name, $content_adapter);
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
