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
      $lastKey = -1;
      $numeric = true;
      foreach($content as $key => $val) {
         if (!is_array($val)) {
            $child = $document->createElement('item', htmlspecialchars($val, ENT_QUOTES));
         } else {
            $child = $document->createElement('item');
            $this->content($document, $child, $val);
         }
         self::addAttribute($child, 'type', self::getType($val));
         if ($numeric) {
            if (is_int($key)) {
               if ($key !== ++$lastKey) $numeric = false;
            } else {
               $numeric = false;
            }
         }
         self::addAttribute($child, 'key', $key);
         $element->appendChild($child);
      }
      self::addAttribute($element, 'numeric', $numeric);
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

   protected static function getType($val)
   {
      switch (gettype($val)) {
         case "boolean":
            return "boolean";
         case "integer":
         case "double":
            return "number";
         case "string":
         case "object": // we don't properly serialize objects. Result will be a string
         case "resource";
            return "string";
         case "NULL":
            return "null";
         case "array":
            return "object";
      }
   }

   public function format($name, $content)
   {
      $content = $content->get();
      $document = new DOMDocument('1.0', 'UTF-8');
      if (!is_array($content)) {
         $root = $document->createElement("item", htmlspecialchars($content, ENT_QUOTES));
      } else {
         $root = $document->createElement("item");
         $this->content($document, $root, $content);
      }
      self::addAttribute($root, 'type', self::getType($content));
      self::addAttribute($root, 'key', $name);
      $document->appendChild($root);
      echo $document->saveXML();
   }

   public function __construct($config)
   {
      $this->config = $config;
   }
}