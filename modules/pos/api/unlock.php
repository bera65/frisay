<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$pos = new PosModule();
$pos->requireApiToken();

$result = $pos->unlockScreen(trim((string) Tools::getValue('pin')));

if (!$result['success'] && $pos->isScreenLocked()) {
	http_response_code(403);
}

echo json_encode($result);
