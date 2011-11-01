<?php

/**
 * Handler for specific requests. This will usually be used by CbContentProvider.
 * Override the handle method to introduce specific behaviour. Once this method
 * is called any required authorization has already been checked. You can throw
 * CbApiExceptions to indicate any errors. Otherwise return anything encodable
 * by the CbContentFormatter being used as result from the handle method.
 */
class CbRequestHandler {

   /**
    * Default Handler throws an error.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   function handle(array $params) {
      throw new CbApiException('Request handler must be overriden', 502);
   }

}