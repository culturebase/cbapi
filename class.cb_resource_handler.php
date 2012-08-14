<?php

Cb::import('CbResourceHandlerInterface');

/**
 * Default request handler. Does not actually do anything.
 */
class CbResourceHandler implements CbResourceHandlerInterface {

   private function crash() {
      throw new CbApiException(502, 'Resource handler must be overriden');
   }

   /**
    * Default Handler throws an error.
    * @param array $params Parameters for the request.
    * @return Nothing.
    */
   public function get(array $params) {$this->crash();}

   public function delete(array $params) {$this->crash();}

   public function post(array $params) {$this->crash();}

   public function put(array $params) {$this->crash();}
}