<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

$reference = trim((string) Tools::getValue('reference'));

if ($reference === '') {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Sipariş numarası gerekli']);
	exit;
}

$receipt = $pos->getReceiptByReference($reference);

if (!$receipt) {
	http_response_code(404);
	echo json_encode(['success' => false, 'message' => 'Fiş bulunamadı']);
	exit;
}

echo json_encode([
	'success' => true,
	'receipt' => $receipt,
]);
