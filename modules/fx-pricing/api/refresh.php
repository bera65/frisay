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

$result = FxPricingService::refreshAll(true);

echo json_encode([
	'success' => true,
	'updated' => $result['products'],
	'rates' => $result['rates'],
	'message' => $result['products'] . ' ürün güncellendi',
], JSON_UNESCAPED_UNICODE);
