<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$token = trim((string) Tools::getValue('token', ''));

if ($token === '') {
	$token = trim((string) ($_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
}

$shopToken = (string) Settings::get('SHOP_TOKEN');

if ($shopToken === '' || !hash_equals($shopToken, $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz token'], JSON_UNESCAPED_UNICODE);
	exit;
}

require_once dirname(__DIR__) . '/lib/SmartCampaignService.php';

$batch = SmartCampaignService::processPendingBatch(50);

echo json_encode([
	'success' => true,
	'message' => $batch['processed'] . ' kayıt işlendi',
	'sent' => $batch['sent'],
	'failed' => $batch['failed'],
	'skipped' => $batch['skipped'],
	'processed' => $batch['processed'],
], JSON_UNESCAPED_UNICODE);
exit;
