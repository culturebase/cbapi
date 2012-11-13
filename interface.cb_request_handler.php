<?php

// We should do Cb::import here, but there are apps which rely on request
// handler being a standalone thing, usable without api.inc.php. Besides, this
// whole interface is deprecated, so who cares ...
require_once("interface.cb_rpc_handler.php");
//Cb::import('CbRpcHandlerInterface');

/**
 * @deprecated Use CbRpcHandlerInterface.
 */
interface CbRequestHandlerInterface extends CbRpcHandlerInterface {}
