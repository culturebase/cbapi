<?php
/* This file is part of cbapi.
 * Copyright © 2011-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
 *
 * cbapi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * cbapi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with cbapi.  If not, see <http://www.gnu.org/licenses/>.
 */

Cb::import('InterfaceCbAuthorizationProvider', 'CbAuthorizationProvider', 'CbContentFormatter', 'CbApiException');

/**
 * Providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You can specify an authorization provider which checks if the specified
 * method is allowed. If no authorization provider is given all actions are
 * allowed.
 *
 * The provider expects an authenticator class with a static method getAccount()
 * that will return the currently logged in user's account or null if the user
 * isn't logged in. Additionally there must be a method login($name, $passwd)
 * which will be called to fulfill authentication requests from respective
 * headers.
 *
 * You can provide the authenticator class name as in the config array passed to
 * the constructor, with the key "authenticator".
 */
abstract class CbAbstractProvider {
   protected $authenticator;   ///< Authenticator to be used for validating auth headers.
   protected $auth_provider;   ///< Authorization provider to check ACLs.
   protected $handlers;        ///< Handlers for specific methods.
   protected $default_handler; ///< Handler to be called if no specific handler is given.
   protected $formatter;       ///< Formatter for the output.
   protected $cache_provider;  ///< Timout for the HTTP cache.
   protected $config;          ///< Application configuration.

   protected function instantiate($thing)
   {
      if (is_string($thing)) {
         $class = new ReflectionClass($thing);
         $thing = $class->newInstance($this->config);
      }
      return $thing;
   }

   protected function getHandler($key)
   {
      if (isset($this->handlers[$key])) {
         return ($this->handlers[$key] = $this->instantiate($this->handlers[$key]));
      } else {
         return ($this->default_handler = $this->instantiate($this->default_handler));
      }
   }

   /**
    * Create an abstract provider.
    * @param array $handlers Handlers for various methods.
    * @param array $config Application Configuration.
    */
   protected function __construct(array $handlers = array(), $config = array())
   {
      $this->config = $config;
      $this->cache_provider = $config['cache_provider'] ?
            $this->instantiate($config['cache_provider']) :
            new CbCacheProvider($config);
      $this->auth_provider = $this->instantiate($config['auth_provider']);
      $this->handlers = $handlers;
      $this->default_handler = $config['default_handler'];
      $this->formatter = $config['formatter'] ?
            $this->instantiate($config['formatter']) :
            new CbContentFormatter($config);
      $this->authenticator = $config['authenticator'] ?
            $config['authenticator'] : 'CbAuth';
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   public function handle(array $request = null)
   {
      if (!$request) $request = array_merge($_COOKIE, $_POST, $_GET);
      $method = isset($request['method']) ? $request['method'] : strtolower($_SERVER['REQUEST_METHOD']);
      $meta = $this->getMetadata($method, $request);
      if (isset($meta['vary'])) {
         $vary = array_map("ucfirst", array_map("strtolower",
               is_array($meta['vary']) ? $meta['vary'] : array($meta['vary'])));
         if (!in_array("Accept", $vary)) $vary[] = 'Accept';
         header('Vary: '. implode(',', $vary), false);
      } else {
         header('Vary: Accept', false);
      }
      if (method_exists($this->formatter, 'negotiate')) {
         $formatter = $this->formatter->negotiate(
               isset($meta['formats']) ? $meta['formats'] : null,
               isset($request['format']) ? $request['format'] : null
         );
      } else {
         $formatter = $this->formatter;
      }
      header('Content-type: ' . $formatter->contentType());

      try {
         if (!$this->cache_provider->run($meta)) return;

         /* allow inline HTTP login; as we return 401s we should do this, no
          * matter if an auth_provider is given.
          */
         if (isset($_SERVER['PHP_AUTH_USER'])) {
            $account = call_user_func(array($this->authenticator, "getAccount"));
            if ($account === null || $account !== $_SERVER['PHP_AUTH_USER']) {
               call_user_func(array($this->authenticator, "login"),
                     $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            }
         }

         if ($this->auth_provider) $this->auth_provider->assert($method, $request);
         $result = $this->execHandler($method, $request);
      } catch (CbApiException $e) {
         $e->outputHeaders();
         $result = new CbContentAdapter($e->getUserData());
      }
      try {
         $formatter->format(isset($meta['name']) ? $meta['name'] : '', $result);
      } catch (CbApiException $e) {
         $e->outputHeaders();
         echo "fatal error during output formatting: ".$e->getUserData();
      }
   }

   abstract protected function execHandler($method, $request);

   abstract protected function getMetadata($method, $request);
}