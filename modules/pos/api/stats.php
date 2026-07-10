<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

echo json_encode([
	'success' => true,
	'stats' => $pos->getTodayStats(),
]);
