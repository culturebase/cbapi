<?php

/**
 * Handler for specific requests. This will usually be used by CbContentProvider.
 * Implement the handle method to introduce specific behaviour. Once this method
 * is called any required authorization has already been checked. You can throw
 * CbApiExceptions to indicate any errors. Otherwise return anything encodable
 * by the CbContentFormatter being used as result from the handle method.
 */
interface CbRequestHandlerInterface {

   /**
    * Handle a request.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function handle(array $params);

   /*
    * Optionally implement the meta() method to control caching.
    */
}