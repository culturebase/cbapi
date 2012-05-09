<?php

class CbFeatureDetect {

   protected $features = array();

   public function run()
   {
      if ($_SESSION['feature_detect_running']) {
         $this->features = $_SESSION['get_browser_features'];
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
         $_SESSION['get_browser_features'] = null;
      } else {
         $this->features = get_browser(null, true);
         if ($this->features['cookies'] && $this->features['javascript']) {
            $_SESSION['get_browser_features'] = $this->features;
            $_SESSION['feature_detect_running'] = true;
            require dirname(__FILE__) . '/templates/feature-detect.html';
            die();
         }
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
