<?php

Cb::import('CbContentFormatterInterface');

class CbXmlFormatter implements CbContentFormatterInterface {

   protected $config;

   public function contentType()
   {
      return "application/xml";
   }

   private function content($document, $element, $content)
   {
      foreach($content as $key => $val) {
         if (!is_array($val)) {
            $child = $document->createElement('item', htmlspecialchars($val, ENT_QUOTES));
         } else {
            $child = $document->createElement('item');
            $this->content($document, $child, $val);
         }
         if (is_int($key)) {
            self::addAttribute($child, 'index', $key);
         } else {
            self::addAttribute($child, 'name', $key);
         }
         $element->appendChild($child);
      }
   }

   /**
    * Simplifies adding an attribute to a DOM node.
    *
    * @param $node DOM node
    * @param $name Attribute name
    * @param $value Attribute value
    */
   protected static function addAttribute(DOMNode $node, $name, $value) {
      $attribute = $node->ownerDocument->createAttribute(htmlspecialchars($name, ENT_QUOTES));
      $attributeTextNode = $node->ownerDocument->createTextNode(htmlspecialchars($value, ENT_QUOTES));
      $attribute->appendChild($attributeTextNode);
      $node->appendChild($attribute);
   }

   public function format($name, $content)
   {
      $content = $content->get();
      $document = new DOMDocument('1.0', 'UTF-8');
      if (!is_array($content)) {
         $root = $document->createElement("root", htmlspecialchars($content, ENT_QUOTES));
      } else {
         $root = $document->createElement("root");
         $this->content($document, $root, $content);
      }
      self::addAttribute($root, 'name', $name);
      $document->appendChild($root);
      echo $document->saveXML();
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}