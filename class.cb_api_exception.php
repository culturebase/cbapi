<?php

/**
 * Class to provide exceptions mappable to HTTP response codes. Usually this
 * will be thrown from a utility class and caught by CbContentProvider. For
 * now we can only transfer a single string message. Subclass this and provide
 * a more sophisticated getMessage to get something else.
 */
class CbApiException extends Exception {
   protected $headers; ///< Additional HTTP headers.

   /**
    * Create an API exception.
    * @param string $message Message to be shown to the user.
    * @param int $response_code HTTP response code to be set.
    * @param string|array $headers additional headers to be set (e.g. WWW-Authenticate on 401).
    */
   public function __construct(string $message = "Not Implemented", int $response_code = 502, $headers = array()) {
      /* Informational and Success headers are of no use here.
       * Obviously this would indicate a problem in our application logic.
       */
      if ($response_code > 99 && $response_code < 300) $response_code = 500;
      parent::__construct($message, $response_code);
      $this->headers = is_array($headers) ? $headers : array($headers);
   }

   /**
    * Return the additional headers.
    * @return array The headers.
    */
   public function getHeaders() {
      return $this->headers;
   }

   /**
    * Output additional HTTP headers and response code header.
    */
   public function outputHeaders() {
      foreach($this->getHeaders() as $header) {
         header($header);
      }
      http_response_code($this->getCode());
   }
}