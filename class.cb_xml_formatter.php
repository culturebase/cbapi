<?php

Cb::import('CbContentFormatterInterface');

class CbXmlFormatter implements CbContentFormatterInterface {

   protected $config;

   public function contentType()
   {
      return "application/xml";
   }

   private function content($xml, $content)
   {
      if (!is_array($content)) {
         $xml->addAttribute('value', $content);
      } else {
         foreach($content as $key => $val) {
            if ($val !== null && $val !== '') {
               $child = $xml->addChild('item');
               if (!is_int($key)) $child->addAttribute('name', $key);
               $this->content($child, $val);
            }
         }
      }
   }

   public function format($name, $content)
   {
      $xml = new SimpleXMLElement("<root/>");
      $xml->addAttribute('name', $name);
      $this->content($xml, $content->get());
      echo $xml->asXML();
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}