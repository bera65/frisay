<?php

define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

$file = basename((string) Tools::getValue('file'));

if (!Customer::isLoggedIn() || !ReturnRequest::canAccessProtectedFile($file, Customer::getId(), false)) {
	http_response_code(403);
	exit;
}

ReturnRequest::serveProtectedFile($file);
