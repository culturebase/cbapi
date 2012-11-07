<?php

Cb::import('CbRequestHandlerInterface');

/**
 * Default request handler. Does not actually do anything.
 */
class CbRequestHandler implements CbRequestHandlerInterface {

   /**
    * Default Handler throws an error.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   function handle(array $params) {
      throw new CbApiException(503, 'Request handler must be overriden');
   }
}