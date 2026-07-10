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

if (!$pos->hasTerminalAccess()) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Oturum gerekli', 'auth_required' => true]);
	exit;
}

echo json_encode($pos->lockScreen());
