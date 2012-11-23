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

/**
 * Handler for specific resources. This will usually be used by
 * CbResourceProvider. Once this method is called any required authorization has
 * already been checked. You can throw CbApiExceptions to indicate any errors.
 * Otherwise return anything encodable by the CbContentFormatter being used as
 * result from the methods.
 */
interface CbResourceHandlerInterface {

   /**
    * GET the resource.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function get(array $params);

   /**
    * PUT the resource - change its content.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function put(array $params);

   /**
    * POST a new item into the resource - add a "child", whatever that may be.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function post(array $params);

   /**
    * DELETE the resource.
    * @param array $params Parameters for the request.
    * @return Anything that can be encoded in JSON.
    */
   public function delete(array $params);

   /*
    * Optionally implement the meta() method to control caching.
    */
}