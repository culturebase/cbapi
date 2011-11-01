<?php

/**
 * An authorization provider that maps methods and parameters to actions and
 * resources, then checks the ACL system for authorization.
 */
class CbAuthorizationProvider {

   protected $application;             ///< Application context for ACLS.
   protected $resource_mapping;        ///< Mappers for getting resources from parameters.
   protected $action_mapping;          ///< Mapping of method => action.
   protected $default_resource_mapper; ///< Resource mapper to be used if none is given.

   /**
    * Create an authorization provider.
    * @param string $application Application context for ACLs.
    * @param array $action_mapping Mapping of methods to actions.
    * @param array $resource_mapping Resource ,appers for mapping parameters to resources.
    * @param CbResourceMapper $default_mapper Default resource mapper.
    */
   function __construct(string $application, array $action_mapping = array(), array $resource_mapping = array(), CbResourceMapper $default_mapper = null) {
      $this->application = $application;
      $this->resource_mapping = $resource_mapping;
      $this->action_mapping = $action_mapping;
      $this->default_resource_mapper = $default_mapper ? $default_mapper : new CbResourceMapper();
   }

   /**
    * Check if the requested action is allowed and throw an API exception with
    * response code 401 or 403 otherwise.
    * @param string $method Method to be executed.
    * @param array $params Parameters for the method.
    */
   function assert(string $method, array $params) {
      $action = $action_mapping[$method];
      if (!$action) $action = $method;
      $resource_mapper = $resource_mapping[$action];
      if (!$resource_mapper) $resource_mapper = $this->default_resource_mapper;
      $resource = $resource_mapper->get($params);

      $account = "guest!";
      if (CbSession::has('auth') && is_array(CbSession::get('auth'))) {
         $auth = new CbObj(CbSession::get('auth'));
         if ($auth->isAuthenticated) $account = $auth->account;
      }

      $acl = new CbAcl($app);
      if ($acl->isAllowed(new CbAclRole($account), $resource, $action)) {
         if ($account == "guest!") {
            throw new CbApiException("Unauthorized", 401, 'WWW-Authenticate: Basic realm="'.$this->application.'"');
         } else {
            throw new CbApiException("Forbidden", 403);
         }
      }
   }
}