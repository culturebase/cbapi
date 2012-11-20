<?php

/**
 * Content formatters should create a string to be output from a result given
 * in any supported format.
 */
interface CbContentFormatterInterface {

   /**
    * @param $name Name of Resource or Method being handled.
    * @param $content Content to be formatted.
    */
   public function format($name, $content);

   /**
    * Return the content type this formatter will produce.
    */
   public function contentType();

   /**
    * If supported, also implement a negotiate($additional, $override) method
    * to figure out the content type.
    * @see CbContentFormatter
    */
}
