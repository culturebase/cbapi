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

Cb::import('CbAbstractProvider');

/**
 * Template providers return dumb templates to be filled from JS. No
 * authentication is necessary and the templates are expected as an array of
 * name => absolute_file_name.
 */
class CbTemplateProvider extends CbAbstractProvider {
   protected $default_template; ///< Resource to be used if none is specified.

   /**
    * Create a template provider.
    * @param array $templates Mapping of name => path for templates.
    * @param string $default_template Template to be used if none is given.
    */
   public function __construct(array $templates = array(), $default_template = null)
   {
      parent::__construct(array());
      $this->default_template = $default_template;
      $this->templates = $templates;
   }

   private function resolveTemplate($request)
   {
      return isset($request['template']) ? $this->templates[$request['template']] : $this->default_template;
   }

   protected function execHandler($method, $request)
   {
      if ($method !== 'get') {
         throw new CbApiException(403, 'You cannot modify templates.');
      }
      return new CbContentAdapter($this->resolveTemplate($request));
   }

   protected function getMetadata($method, $request)
   {
      if ($method !== 'get') {
         throw new CbApiException(403, 'You cannot modify templates.');
      }
      return array(
         'last_modified' => filemtime($this->resolveTemplate($request)),
         'privacy' => 'public',
         'name' => $request['template'],
         'formats' => array(
            'text/html' => 'CbHtmlFileFormatter'
         )
      );
   }
}