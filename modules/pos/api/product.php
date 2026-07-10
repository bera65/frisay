<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

$idProduct = (int) Tools::getValue('id_product');
$product = $pos->getProductDetail($idProduct);

if (!$product) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
	exit;
}

echo json_encode([
	'success' => true,
	'product' => $product,
]);
