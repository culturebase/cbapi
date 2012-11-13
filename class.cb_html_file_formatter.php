<?php

/**
 * Render a content-less HTML file. This is intended for "dumb" templates to be
 * used by Javascript user interfaces. The goal is to get allow for very
 * aggressive caching by not allowing any content in the HTML.
 */
class CbHtmlFileFormatter implements CbContentFormatterInterface {
   public function __construct() {}

   public function contentType($additional = null)
   {
      return "text/html ;charset=utf-8";
   }

   /**
    * We expect that to be HTML already. For specialized treatment subclass
    * this.
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($name, $content)
   {
      $content = $content->get();
      if ($content === null) return;
      if (is_string($content)) {
         require $content;
      } else {
         throw new CbApiException(500, "Generic CbHtmlFileFormatter doesn't know what to do with a ".gettype($content));
      }
   }
}