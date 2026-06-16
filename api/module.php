<?php

define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

$moduleName = trim((string) Tools::getValue('m'));
$action = trim((string) Tools::getValue('action'));

if ($moduleName === '' || $action === '') {
	http_response_code(400);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['success' => false, 'message' => 'Modül veya işlem belirtilmedi']);
	exit;
}

Module::dispatchApi($moduleName, $action);
