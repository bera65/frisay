<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!class_exists('Admin')) {
	require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

header('Content-Type: application/json; charset=utf-8');

if (!Admin::isLoggedIn()) {
	echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim'], JSON_UNESCAPED_UNICODE);
	exit;
}

require_once dirname(__DIR__) . '/lib/TrendyolApi.php';
require_once dirname(__DIR__) . '/lib/ProductSyncService.php';
require_once dirname(__DIR__) . '/lib/OrderService.php';

$start = trim((string) Tools::getValue('start_date', ''));
$end = trim((string) Tools::getValue('end_date', ''));

$result = Trendyol\OrderService::syncOrders(
	$start !== '' ? $start : null,
	$end !== '' ? $end : null
);

echo json_encode([
	'success' => $result['ok'],
	'message' => $result['message'],
	'count' => $result['count'] ?? 0,
	'orders' => $result['orders'] ?? [],
], JSON_UNESCAPED_UNICODE);
exit;
