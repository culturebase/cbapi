<?php
/* This file is part of cbapi.
 * Copyright © 2010-2012 stiftung kulturserver.de ggmbh <github@culturebase.org>
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