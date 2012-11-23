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

Cb::import('CbResourceHandlerInterface');

/**
 * Default request handler. Does not actually do anything.
 */
class CbResourceHandler implements CbResourceHandlerInterface {
   public function __construct() {}

   private function crash() {
      throw new CbApiException(503, 'Resource handler must be overriden');
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