<?php

/**
 * Handler for specific resources. This will usually be used by
 * CbResourceProvider. Once this method is called any required authorization has
 * already been checked. You can throw CbApiExceptions to indicate any errors.
 * Otherwise return anything encodable by the CbContentFormatter being used as
 * result from the methods.
 */
interface CbResourceHandlerInterface {

   /**
    * GET the resource.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function get(array $params);

   /**
    * PUT the resource - change its content.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function put(array $params);

   /**
    * POST a new item into the resource - add a "child", whatever that may be.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function post(array $params);

   /**
    * DELETE the resource.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function delete(array $params);
}