<?php

/**
 * Content formatters should create a string to be output from a result given
 * in any supported format.
 */
interface CbContentFormatterInterface {

   /**
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($content);

   public function contentType();
}
