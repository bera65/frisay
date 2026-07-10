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

$result = $pos->createSale(
	trim((string) Tools::getValue('payment_method')),
	trim((string) Tools::getValue('customer_name')),
	trim((string) Tools::getValue('customer_phone')),
	trim((string) Tools::getValue('note')),
	(float) Tools::getValue('cash_paid', 0)
);

echo json_encode($result);
