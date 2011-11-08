<?php

/**
 * An authorization provider that maps methods and parameters to actions and
 * resources, then checks the ACL system for authorization.
 */
interface CbAuthorizationProviderInterface {

   /**
    * Check if the requested action is allowed and throw an API exception with
    * suitable error otherwise.
    * @param string $method Method to be executed.
    * @param array $params Parameters for the method.
    */
   public function assert(string $method, array $params);
}