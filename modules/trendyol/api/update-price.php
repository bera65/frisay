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

$idProduct = (int) Tools::getValue('id_product', 0);

if ($idProduct <= 0) {
	echo json_encode(['success' => false, 'message' => 'Ürün ID gerekli'], JSON_UNESCAPED_UNICODE);
	exit;
}

$saleOverride = null;
$listOverride = null;
$saleRaw = Tools::getValue('sale_price', null);
$listRaw = Tools::getValue('list_price', null);

if ($saleRaw !== null && $saleRaw !== '') {
	$saleOverride = (float) str_replace(',', '.', (string) $saleRaw);
}

if ($listRaw !== null && $listRaw !== '') {
	$listOverride = (float) str_replace(',', '.', (string) $listRaw);
}

$result = Trendyol\ProductSyncService::updatePriceStock($idProduct, $saleOverride, $listOverride);

echo json_encode([
	'success' => $result['ok'],
	'message' => $result['message'],
	'mapping' => $result['mapping'] ?? Trendyol\ProductSyncService::findMapping($idProduct),
], JSON_UNESCAPED_UNICODE);
exit;
