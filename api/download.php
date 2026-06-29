<?php
define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

if (!Customer::isLoggedIn()) {
	http_response_code(403);
	exit('Giriş yapmalısınız');
}

$token = (string) Tools::getValue('token');
VirtualProduct::serveDownload($token, Customer::getId());
