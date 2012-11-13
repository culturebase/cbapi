<?php

class CbContentAdapter {
   private $content = null;
   private $handler = null;
   private $method = null;

   public function __construct($handler, $method = null, $params = array(), $exec_now = false)
   {
      if ($method === null) {
         $this->content = $handler;
      } else {
         $this->handler = $handler;
         $this->method = $method;
         $this->params = $params;
         if ($exec_now) $this->get();
      }
   }

   public function get()
   {
      if ($this->handler !== null) {
         $reflect = new ReflectionMethod($this->handler, $this->method);
         $this->content = $reflect->invoke($this->handler, $this->params);
         $this->handler = null;
      }
      return $this->content;
   }
}