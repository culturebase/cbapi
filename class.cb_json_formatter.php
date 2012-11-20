<?php

Cb::import('CbContentFormatterInterface');

class CbJsonFormatter implements CbContentFormatterInterface {

   public function __construct() {}

   /**
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($name, $content) {
      echo json_encode($content->get());
   }

   public function contentType() {
      return "application/json";
   }
}