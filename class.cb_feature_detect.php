<?php

/**
 * MESS. We cannot reliably detect features. Let's admit that.
 */
class CbFeatureDetect {

   private $session_name;
   private $js;
   private $features;

   public function __construct($session_name = 'features', $js = 'js')
   {
      $this->session_name = $session_name;
      $this->js = $js;
      $this->features = array_key_exists($session_name, $_SESSION) ?
            $_SESSION[$session_name] : array();
   }

   protected function decodeFeatures()
   {
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
   }

   /**
    * Run the feature detection. Preconditions are:
    * 1. The current request has no POST parameters.
    * 2. We can append ?$js={yes|no} to the current URL and that will constitute
    *    a valid GET parameter if the feature detection hasn't run yet.
    * 3. The session variable $session_name is not currently in use.
    * 4. The cookie $session_name is not currently in use.
    * 5. No output has been send, yet.
    * 6. The GET parameter $js is used to tell the feature detection that it
    *    cannot rely on cookies and/or JS.
    *
    * The feature detection run will include a special bit of HTML + Javascript
    * and then die(). The Javascript will reload the same page with a POST
    * request containing the detected features. It expects to trigger the same
    * code path and end up in the run() method again where the features are
    * evaluated. After that they're written to $_SESSION[$session_name] and
    * returned from the method. The feature detection cookie is set to 'done'
    * then so that further calls to this method don't trigger a re-run of the
    * detection. Instead $_SESSION[$session_name] is returned.
    *
    * This means you can run the feature detection without a session, but then
    * it will only work once. After that you'll get an empty array. It won't
    * usually rerun the detection as the cookie is independent of the session.
    *
    * However, if no cookies are accepted the feature detection itself is
    * incapable of finding out if it has already run. It will however listen to
    * the GET parameter $js and avoid running if that is set. The javascript
    * code will add a POST parameter "cookies=false" if it can run and detects
    * that no $session_name cookie is set.
    *
    * Obviously we can only detect either absence of JS or absence of Cookies
    * with client side code. However, if $js is set to "yes", availability of
    * cookies can be determined by checking the presence of the $sesion_name
    * cookie. Also, if the Browser is not expected to be able to run JS, the
    * cookie is set and then the browser is redirected via HTTP 302. This means
    * in the next run of the feature detection we can see if the cookie has been
    * set.
    *
    * You should always conserve the $js GET parameter in further requests if
    * it's set to "no". If it's set to "yes" then that's just a way of
    * circumventing empty form post actions which lead to browsers doing "weird
    * things" (see https://www.w3.org/Bugs/Public/show_bug.cgi?id=14215#c1).
    * However, if you want to avoid the 302 redirects in absence of cookies you
    * may still want to conserve the $js parameter, even if it's set to "yes".
    */
   public static function run($session_name = 'features', $js = 'js')
   {
      $fd = new CbFeatureDetect($session_name, $js);
      return $fd->detect();
   }

   private function detectBaseFeatures() {
      return array(
         'cookies'    => isset($_COOKIE[$this->session_name]),
         'javascript' => !isset($_GET[$this->js]) || $_GET[$this->js] === 'yes'
      );
   }

   public function detect()
   {
      if ($_COOKIE[$this->session_name] === 'done') {
         // don't rerun, even if features haven't been saved
         die(var_dump($this->features));
         return array_merge($this->detectBaseFeatures(), $this->features);
      } else if ($_COOKIE[$this->session_name] === 'running' ||
            isset($_GET[$this->js]) || !empty($_POST)) {
         // some information about features is given in request. Evaluate.
         $this->features = $this->detectBaseFeatures();
         $this->decodeFeatures();
         setcookie($this->session_name, 'done', 0, '/');
         $_COOKIE[$this->session_name] = 'done';
         $_SESSION[$this->session_name] = $this->features;
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Location: '.$_SERVER['REQUEST_URI']); // obviously submitted by JS
            die();
         } else {
            return $this->features;
         }
      } else {
         // nothing is known, run feature detection if expected to be
         // successful.
         setcookie($this->session_name, 'running', 0, '/');
         require 'lib/framework/3rdparty/browscap/Browscap.php';
         $bc = new Browscap('/var/tmp/browscap/');
         $browser = $bc->getBrowser();
         if ($browser->JavaScript) {
            // JS is probably available, try to run FD.
            require 'templates/feature_detect.inc.php';
         } else {
            // We're pretty sure there is no JS, do an HTTP redirect to ?js=no
            header('Location: ?js=no');
         }
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
