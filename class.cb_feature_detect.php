<?php

class CbFeatureDetect {
   protected $features = array();

   public function run() {
      if ($_SESSION['feature_detect_running']) {
         foreach ($_POST as $feature => $value) {
            $this->features[$feature] = $value;
         }
         $_SESSION['feature_detect_running'] = false;
      } else {
         $_SESSION['feature_detect_running'] = true;
         require dirname(__FILE__).'/templates/feature-detect.html';
         die();
      }
   }

   public function get($feature) {
      return array_key_exists($feature, $this->features) ? $this->features[$feature] : false;
   }

   public function getAll() {
      return $this->features;
   }
}