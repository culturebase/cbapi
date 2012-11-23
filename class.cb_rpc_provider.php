<?php
/* This file is part of cbapi.
 * Copyright © 2010-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
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

Cb::import('CbAbstractProvider', 'CbRpcHandler', 'CbContentAdapter');

/**
 * Content providers handle AJAX requests (or any other requests) by clients,
 * execute the necessary actions and output the results in the required format.
 * You can either subclass this and override the handle method or register
 * handlers for specific methods. You can also specify an authorization provider
 * which checks if the specified method is allowed. If no authorization provider
 * is given all actions are allowed.
 */
class CbRpcProvider extends CbAbstractProvider {
   protected $default_method;  ///< Method to be called if none is specified.

   /**
    * Create a content provider.
    * @param array $handlers Handlers for various methods.
    * @param array $config Application Configuration.
    * @param @deprecated CbAuthorizationProvider $auth_provider Authorization Provider.
    * @param @deprecated string $default_method Default method to be called if none is specified.
    * @param @deprecated CbContentFormatter $formatter Content formatter for the output.
    * @param @deprecated number $deprecated Ignored.
    */
   function __construct(array $handlers = array(), $config = null,
         $auth_provider = null, $default_method = null, $formatter = null,
         $deprecated = null) {
      if (is_array($config)) {
         $config = array_merge(array(
            'default_handler' => $config['default_handler'] ?
                  null : 'CbRpcHandler',
            'auth_provider' => null,
            'formatter' => null
         ), $config);
      } else {
         $config = array(
            'default_handler' => $config,
            'auth_provider' => $auth_provider,
            'formatter' => $formatter
         );
      }
      parent::__construct($handlers, $config);
      $this->default_method = isset($config['default_method']) ?
            $config['default_method'] : $default_method;
   }

   protected function execHandler($method, $request) {
      return new CbContentAdapter($this->getHandler($method), "handle", $request,
            in_array(strtolower($_SERVER['REQUEST_METHOD']), array('put', 'post', 'delete')));
   }

   /**
    * Handle the specified request (or $_REQUEST if null), check authorization,
    * catch any errors, set the required HTTP response codes and headers, and
    * finally output the error message or the result.
    * @param array $request Request to be handled. If null, use $_REQUEST instead.
    */
   function handle(array $request = null) {
      if (!$request) $request = array_merge($_COOKIE, $_POST, $_GET);
      if (!isset($request['method']) && isset($this->default_method)) {
         $request['method'] = $this->default_method;
      }
      parent::handle($request);
   }

   protected function getMetadata($method, $request)
   {
      $handler = $this->getHandler($method);
      $meta = array('name' => $method);
      if (method_exists($handler, 'meta')) {
         return array_merge($meta, $handler->meta($request));
      } else {
         return $meta;
      }
   }
}