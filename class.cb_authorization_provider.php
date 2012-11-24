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

Cb::import('CbAuthorizationProviderInterface', 'CbResourceMapper', 'CbApiException');

/**
 * An authorization provider that maps methods and parameters to actions and
 * resources, then checks the ACL system for authorization.
 *
 * It expects an authenticator class with a static method getAccount() that will
 * return the currently logged in user's account or null if the user isn't
 * logged in.
 *
 * Additionally it needs an acl_checker class which can be instantiated with the
 * application context as constructor parameter and will then provide a method
 * isAllowed($account, $resource, $action).
 *
 * You can provide authenticator and acl_checker class names as in the config
 * array passed to the constructor, with keys "authenticator" and "acl_checker".
 */
class CbAuthorizationProvider implements CbAuthorizationProviderInterface {

   protected $application;      ///< Application context for ACLS.
   protected $resource_mapping; ///< Mappers for getting resources from parameters.
   protected $action_mapping;   ///< Mapping of method => action.
   protected $resource_mapper;  ///< Resource mapper to be used if none is given.
   protected $authenticator;    ///< Authenticator to be used.
   protected $acl_checker;      ///< ACL checker to be used.

   /**
    * Create an authorization provider.
    * @param array $config Application Configuration.
    * @param @deprecated array $action_mapping Mapping of methods to actions.
    * @param @deprecated array $resource_mapping Resource mappers for mapping parameters to resources.
    * @param @deprecated CbResourceMapper $resource_mapper Default resource mapper.
    */
   function __construct($config, array $action_mapping = array(),
         array $resource_mapping = array(),
         CbResourceMapper $resource_mapper = null)
   {
      if (!is_array($config)) {
         $config = array(
            'application' => $config,
            'resource_mapping' => $resource_mapping,
            'action_mapping' => $action_mapping,
            'resource_mapper' => $resource_mapper
         );
      }
      $this->config = $config;
      $this->application = $config['application'];
      $this->resource_mapping = $config['resource_mapping'];
      $this->action_mapping = $config['action_mapping'];
      $this->resource_mapper = $config['resource_mapper'] ?
            $config['resource_mapper'] : 'CbResourceMapper';
      $this->authenticator = $config['authenticator'] ?
            $config['authenticator'] : 'CbAuth';
      $this->acl_checker = $config['acl_checker'] ?
            $config['acl_checker'] : 'CbAcl';
   }

   /**
    * Check if the requested action is allowed and throw an API exception with
    * response code 401 or 403 otherwise.
    * @param string $method Method to be executed.
    * @param array $params Parameters for the method.
    */
   function assert($method, array $params)
   {
      if (isset($this->action_mapping)) {
         $action = $this->action_mapping[$method];
      }
      if (!$action) $action = $method;

      if (isset($this->resource_mapping)) {
         $resource_mapper = $this->resource_mapping[$action];
      }
      if (!$resource_mapper) $resource_mapper = $this->resource_mapper;
      if (is_string($resource_mapper)) {
         $class = new ReflectionClass($resource_mapper);
         $resource_mapper = $class->newInstance($this->config);
      }
      $resource = $resource_mapper->get($params);

      $account = call_user_func(array($this->authenticator, "getAccount"));

      $aclclass = new ReflectionClass($this->acl_checker);
      $acl = $aclclass->newInstance($this->application);
      if (!$acl->isAllowed($account, $resource, $action)) {
         if ($account === null) {
            throw new CbApiException(401, "Please log in", array('WWW-Authenticate: Basic realm="'.$this->application.'"'));
         } else {
            throw new CbApiException(403, "You're not allowed to do $action on $resource as $account");
         }
      }
   }
}