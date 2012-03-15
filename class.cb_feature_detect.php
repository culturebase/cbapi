<?php

class CbFeatureDetect {

   protected $features = array();

   public function run()
   {
      if ($_SESSION['feature_detect_running']) {
         foreach ($_POST as $name => $value) {
            if ($value === 'true') {
               $this->features[$name] = true;
            } else if ($value === 'false') {
               $this->features[$name] = false;
            } else if (is_numeric($value)) {
               $this->features[$name] = $value + 0;
            } else {
               $this->features[$name] = $value;
            }
         }
         $_SESSION['feature_detect_running'] = false;
      } else {
         $_SESSION['feature_detect_running'] = true;
         require dirname(__FILE__) . '/templates/feature-detect.html';
         die();
      }
   }

   public function get($feature)
   {
      return array_key_exists($feature, $this->features) ? $this->features[$feature] : false;
   }

   public function getAll()
   {
      return $this->features;
   }

}
