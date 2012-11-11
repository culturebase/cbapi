<?php

/**
 * Map a set of parameters to an ACL resource.
 */
class CbResourceMapper {
   public function __construct() {}

   /**
    * By default return the parameter 'resource' or an empty string.
    * @param array $params Parameters to be searched for resource.
    * @return string Resource to be used.
    */
   function get(array $params) {
      return isset($params['resource']) ? $params['resource'] : '';
   }
}