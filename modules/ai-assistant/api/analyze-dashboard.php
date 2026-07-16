<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!class_exists('Admin')) {
	require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

require_once dirname(__DIR__) . '/lib/AiClient.php';

header('Content-Type: application/json; charset=utf-8');

if (!Admin::isLoggedIn()) {
	echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim'], JSON_UNESCAPED_UNICODE);
	exit;
}

$token = (string) Tools::getValue('token');
$sessionToken = (string) ($_SESSION['admin_csrf_token'] ?? '');

if ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek'], JSON_UNESCAPED_UNICODE);
	exit;
}

if (!AiAssistantClient::isConfigured()) {
	echo json_encode([
		'success' => false,
		'message' => 'API anahtarı eksik. Modül ayarlarından ekleyin.',
	], JSON_UNESCAPED_UNICODE);
	exit;
}

$stats = AiAssistantClient::collectDashboardStats();
$result = AiAssistantClient::analyzeDashboard($stats);

if (!empty($result['success'])) {
	$result['stats_summary'] = [
		'orders_today' => $stats['kpi']['orders_today'] ?? 0,
		'revenue_month' => $stats['kpi']['revenue_month'] ?? 0,
		'top_products' => array_slice($stats['top_products_30d'] ?? [], 0, 5),
	];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
