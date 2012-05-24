<?php

/**
 * Content formatters should create a string to be output from a result given
 * in any supported format.
 */
class CbContentFormatter {

   /**
    * Default formatter creates JSON.
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($content) {
      return json_encode($content);
   }

   public function contentType() {
      return "application/json; charset=utf-8";
   }
}