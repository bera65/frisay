<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$token = trim((string) Tools::getValue('token', ''));

if ($token === '') {
	$token = trim((string) ($_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
}

if ($token === '') {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Token gerekli']);
	exit;
}

$result = AlertPriceModule::processCron($token);

if (!$result['success']) {
	http_response_code(403);
}

echo json_encode($result);
