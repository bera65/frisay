<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

$code = trim((string) Tools::getValue('barcode'));

if ($code === '') {
	echo json_encode(['success' => false, 'message' => 'Barkod boş']);
	exit;
}

$result = $pos->addByBarcode($code, max(1, (int) Tools::getValue('qty', 1)));
$result['barcode'] = $code;

echo json_encode($result);
