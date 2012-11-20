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
         if (is_string($content)) {
            $xml->addAttribute($content, null);
         } else {
            $xml->addAttribute('value', $content);
         }
      } else {
         foreach($content as $key => $val) {
            if ($val !== null && $val !== '') {
               if (is_array($val)) {
                  if (is_int($key)) {
                     $this->content($xml->addChild('item'), $val);
                  } else {
                     $this->content($xml->addChild($key), $val);
                  }
               } else if (!is_int($key)) {
                  $xml->addAttribute($key, $val);
               } else {
                  $xml->addAttribute($val, null);
               }
            }
         }
      }
   }

   public function format($name, $content)
   {
      $xml = new SimpleXMLElement("<root/>");
      $this->content($xml->addChild($name), $content->get());
      echo $xml->children()->asXML();
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}