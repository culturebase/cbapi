<?php

class CbHtmlFileFormatter extends CbHtmlFormatter {

   /**
    * We expect that to be HTML already. For specialized treatment subclass
    * this.
    * @param $content Content to be formatted.
    * @return String to be output.
    */
   public function format($content)
   {
      if ($content === null) return;
      if (is_string($content)) {
         require $content;
      } else {
         throw new CbApiException(500, "Generic CbHtmlFileFormatter doesn't know what to do with a ".gettype($content));
      }
   }
}