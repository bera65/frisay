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

require_once dirname(__DIR__) . '/lib/TrendyolApi.php';
require_once dirname(__DIR__) . '/lib/ProductSyncService.php';
require_once dirname(__DIR__) . '/lib/OrderService.php';
require_once dirname(__DIR__) . '/lib/QuestionService.php';

$type = strtolower(trim((string) Tools::getValue('type', 'orders')));

if ($type === 'questions' || $type === 'qna') {
	$result = Trendyol\QuestionService::syncQuestions(0, 50);
} else {
	$start = trim((string) Tools::getValue('start_date', ''));
	$end = trim((string) Tools::getValue('end_date', ''));
	$result = Trendyol\OrderService::syncOrders(
		$start !== '' ? $start : null,
		$end !== '' ? $end : null
	);
}

echo json_encode([
	'success' => $result['ok'],
	'message' => $result['message'],
	'count' => $result['count'] ?? 0,
	'stock_updates' => $result['stock_updates'] ?? 0,
], JSON_UNESCAPED_UNICODE);
exit;
