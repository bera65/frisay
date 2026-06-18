<?php

require_once dirname(__DIR__) . '/config/install_gate.php';

if (!fshop_is_installed()) {
	fshop_redirect_to_installer();
}

define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';
require_once dirname(__DIR__) . '/core/WebApi.php';

WebApi::dispatch();
