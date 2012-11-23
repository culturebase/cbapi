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

// We should do Cb::import here, but there are apps which rely on request
// handler being a standalone thing, usable without api.inc.php. Besides, this
// whole interface is deprecated, so who cares ...
require_once("interface.cb_rpc_handler.php");
//Cb::import('CbRpcHandlerInterface');

/**
 * @deprecated Use CbRpcHandlerInterface.
 */
interface CbRequestHandlerInterface extends CbRpcHandlerInterface {}
