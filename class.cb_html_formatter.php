<?php

Cb::import('CbContentFormatterInterface');

class CbHtmlFormatter implements CbContentFormatterInterface {

   public function contentType($additional = null) {
      return "text/html ;charset=utf-8";
   }

   private function title() {
      echo htmlentities($this->name);
   }
   
   private function content($content = null, $level = 1) {
      if ($content === null) $content = $this->content;
      if (!is_array($content)) {
         echo "<div>".htmlentities($content)."</div>";
      } else {
         foreach($content as $key => $val) {
            if ($val !== null) {
               if (!is_int($key)) {
                  if ($level < 7) {
                     echo "<h$level>".htmlentities($key)."</h$level>";
                  } else {
                     echo "<span>".htmlentities($key)."</span>";
                  }
               }
               $this->content($val, $level + 1);
            }
         }
      }
   }
   
   public function format($content, $name = '') {
      $this->name = $name;
      $this->content = $content;
      require 'templates/html_format.inc.php';
   }
}