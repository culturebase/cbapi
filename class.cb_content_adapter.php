<?php
/* This file is part of cbapi.
 * Copyright © 2011-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
 *
 * cbapi is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * cbapi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with cbapi.  If not, see <http://www.gnu.org/licenses/>.
 */

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