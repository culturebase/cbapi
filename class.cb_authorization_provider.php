<?php

Cb::import('CbAuthorizationProviderInterface', 'CbResourceMapper', 'CbApiException');

/**
 * An authorization provider that maps methods and parameters to actions and
 * resources, then checks the ACL system for authorization.
 */
class CbAuthorizationProvider implements CbAuthorizationProviderInterface {

   protected $application;      ///< Application context for ACLS.
   protected $resource_mapping; ///< Mappers for getting resources from parameters.
   protected $action_mapping;   ///< Mapping of method => action.
   protected $resource_mapper;  ///< Resource mapper to be used if none is given.

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

      $account = null;
      if (CbSession::has('auth') && is_array(CbSession::get('auth'))) {
         $auth = new CbObj(CbSession::get('auth'));
         if ($auth->isAuthenticated) $account = $auth->account;
      }

      $acl = new CbAcl($this->application);
      if (!$acl->isAllowed($account, $resource, $action)) {
         if ($account === null) {
            throw new CbApiException(401, "Please log in", array('WWW-Authenticate: Basic realm="'.$this->application.'"'));
         } else {
            throw new CbApiException(403, "You're not allowed to do $action on $resource as $account");
         }
      }
   }
}