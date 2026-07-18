<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	echo json_encode([
		'success' => true,
		'customer' => $pos->getSessionCustomer(),
	]);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

$op = trim((string) ($_POST['customer_op'] ?? ''));

if ($op === 'reset') {
	echo json_encode($pos->resetSessionCustomer());
	exit;
}

if ($op === 'set') {
	echo json_encode($pos->setSessionCustomer([
		'id_user' => (int) Tools::getValue('id_user'),
		'name' => (string) Tools::getValue('name'),
		'phone' => (string) Tools::getValue('phone'),
	]));
	exit;
}

if ($op === 'create') {
	echo json_encode($pos->createCustomer(
		(string) Tools::getValue('name'),
		(string) Tools::getValue('phone'),
		(string) Tools::getValue('email')
	));
	exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
