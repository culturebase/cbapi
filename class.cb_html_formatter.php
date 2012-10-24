<?php

Cb::import('CbContentFormatterInterface');

abstract class CbHtmlFormatter implements CbContentFormatterInterface {

   public function contentType() {
      return "text/html ;charset=utf-8";
   }
}