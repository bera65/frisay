<?php
define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$idProduct = (int) Tools::getValue('id');

if ($idProduct <= 0) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Geçersiz ürün']);
	exit;
}

$data = Product::getQuickView($idProduct);

if (!$data) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'Ürün bulunamadı']);
	exit;
}

echo json_encode([
	'success' => true,
	'product' => $data,
], JSON_UNESCAPED_UNICODE);
