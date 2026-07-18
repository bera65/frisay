<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

require_once dirname(__DIR__) . '/lib/ProductSetService.php';

header('Content-Type: application/json; charset=utf-8');

if (!class_exists('Admin')) {
	require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

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

$q = trim((string) Tools::getValue('q', ''));
$exclude = (int) Tools::getValue('exclude', 0);

echo json_encode([
	'success' => true,
	'items' => ProductSetService::searchProducts($q, $exclude, 20),
], JSON_UNESCAPED_UNICODE);
