<?php

require_once dirname(__DIR__) . '/config/install_gate.php';

if (!fshop_is_installed()) {
	fshop_redirect_to_installer();
}

define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

header('Content-Type: application/json; charset=utf-8');

$token = trim((string) Tools::getValue('token', ''));

if ($token === '') {
	$token = trim((string) ($_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
}

$shopToken = (string) Settings::get('SHOP_TOKEN');

if ($shopToken === '' || !hash_equals($shopToken, $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
	exit;
}

$action = strtolower(trim((string) Tools::getValue('action', 'currency')));

if ($action === 'currency' || $action === 'doviz') {
	$updated = Product::refreshCurrencyPrices();

	echo json_encode([
		'success' => true,
		'message' => $updated . ' ürün fiyatı güncellendi',
		'updated' => $updated,
	]);
	exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Bilinmeyen cron işlemi']);
