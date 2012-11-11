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

   public function contentType($additional = null);
}
