<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/lib/FxPricingService.php';

header('Content-Type: application/json; charset=utf-8');

if (!FxPricingService::isEnabled()) {
	echo json_encode(['success' => false, 'message' => 'Modül kapalı']);
	exit;
}

if (!class_exists('ExchangeRate', false)) {
	require_once dirname(__DIR__, 3) . '/core/ExchangeRate.php';
}

$shopCurrency = Currency::getShopCurrency();
$rates = ExchangeRate::getRates(false);
$updatedAt = 0;
$cacheRaw = trim((string) Settings::get('FX_RATES_CACHE'));

if ($cacheRaw !== '') {
	$cache = json_decode($cacheRaw, true);

	if (is_array($cache)) {
		$updatedAt = (int) ($cache['updated_at'] ?? 0);
	}
}

echo json_encode([
	'success' => true,
	'shop_currency' => $shopCurrency,
	'rates' => $rates,
	'updated_at' => $updatedAt,
], JSON_UNESCAPED_UNICODE);
