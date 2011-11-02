<?php

class CbFeatureDetect {
   protected $features = array();

   public function run() {
      if ($_SESSION['feature_detect_running']) {
         foreach ($_GET as $feature => $value) {
            $this->features[$feature] = $value;
         }
      } else {
         $_SESSION['feature_detect_running'] = true;
         require dirname(__FILE__).'/templates/feature-detect.html';
         die();
      }
   }

   public function get($feature) {
      return isset($this->features[$feature]) ? $this->features[$feature] : false;
   }

   public function getAll() {
      return $features;
   }
}