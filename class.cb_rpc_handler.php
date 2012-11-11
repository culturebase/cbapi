<?php

Cb::import('CbRpcHandlerInterface');

/**
 * Default RPC handler. Does not actually do anything.
 */
class CbRpcHandler implements CbRpcHandlerInterface {
   public function __construct() {}

   /**
    * Default Handler throws an error.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   function handle(array $params) {
      throw new CbApiException(503, 'RPC handler must be overriden');
   }
}