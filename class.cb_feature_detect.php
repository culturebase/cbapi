<?php

class CbFeatureDetect {

   protected static function decodeFeatures()
   {
      foreach ($_POST as $name => $value) {
         if ($value === 'true') {
            $_SESSION['features'][$name] = true;
         } else if ($value === 'false') {
            $_SESSION['features'][$name] = false;
         } else if (is_numeric($value)) {
            $_SESSION['features'][$name] = $value + 0;
         } else {
            $_SESSION['features'][$name] = $value;
         }
      }
   }

   /**
    * Run the feature detection. Preconditions are:
    * 1. The current request has no POST parameters.
    * 2. We can append '?' + $nojs or '?' + $nocookies to the current URL and that will
    *    constitute a valid GET parameter if the feature detection hasn't run
    *    yet.
    * 3. The session variable $name is not currently in use.
    * 4. The cookie $name is not currently in use.
    * 5. No output has been send, yet.
    * 6. The GET parameters $nocookies and $nojs are used to tell the feature
    *    detection that it cannot rely on cookies and/or JS.
    *
    * The feature detection run will include a special bit of HTML + Javascript
    * and then die(). The Javascript will reload the same page with a POST
    * request containing the detected features. It expects to trigger the same
    * code path and end up in the run() method again where the features are
    * evaluated. After that they're written to $_SESSION[$name] and
    * returned from the method. The feature detection cookie is set to 'done'
    * then so that further calls to this method don't trigger a re-run of the
    * detection. Instead $_SESSION[$name] is returned.
    *
    * This means you can run the feature detection without a session, but then
    * it will only work once. After that you'll get a null. It won't usually
    * rerun the detection as the cookie is independent of the session.
    *
    * However, if no cookies are accepted the feature detection itself is
    * incapable of finding out if it has already run. It will however listen to
    * the GET parameter $nocookies and avoid running if that is set. The
    * javascript code will add '?' + $nocookies to the URL if it can run and
    * detects that no $name cookie is set.
    *
    * Obviously we can only detect either absence of JS or absence of Cookies
    * with client side code. This is why we cannot add both GET parameters at
    * once. However, if $nojs is set, availability of cookies can be determined
    * by checking the presence of the $name cookie. On the other hand
    * if $nocookies is set, it must have been set by Javascript, so JS is
    * available.
    *
    * You should always conserve the $nojs or $nocookies GET parameters in
    * further requests if you intend to re-run this method.
    */
   public static function run($name = 'features', $nojs = 'nojs', $nocookies = 'nocookies')
   {
      if ($_COOKIE[$name] === 'done') {
         // don't rerun, even if features haven't been saved
         return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
      } else if ($_COOKIE[$name] === 'running' || isset($_GET[$nojs]) ||
            !empty($_POST) || isset($_GET[$nocookies])) {
         $_SESSION[$name] = array(
            'cookies'    => isset($_COOKIE[$name]),
            'javascript' => !isset($_GET[$nojs])
         );
         self::decodeFeatures();
         setcookie($name, 'done');
         return $_SESSION[$name];
      } else {
         require 'lib/framework/3rdparty/browscap/Browscap.php';
         $bc = new Browscap('/var/tmp/browscap/');
         $browser = $bc->getBrowser();
         if ($browser->JavaScript) {
            setcookie($name, 'running');
            require 'feature_detect.inc.php';
            die();
         } else {
            setcookie($name, 'done');
            $_SESSION[$name] = array(
               'javascript' => false,
               'cookies' => ($browser->Cookies !== 0)
            );
            return $_SESSION[$name];
         }
      }
   }

   public static function get($feature)
   {
      return array_key_exists($feature, $_SESSION['features']) ? $_SESSION['features'][$feature] : false;
   }

   public static function getAll()
   {
      return $_SESSION['features'];
   }

}
