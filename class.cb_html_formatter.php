<?php

Cb::import('CbContentFormatterInterface');

abstract class CbHtmlFormatter implements CbContentFormatterInterface {

   abstract public function format($content);

   public function contentType() {
      return "text/html ;charset=utf-8";
   }
}