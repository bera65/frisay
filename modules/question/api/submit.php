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

$token = Tools::getValue('token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
	exit;
}

if (!Customer::isLoggedIn()) {
	echo json_encode([
		'success' => false,
		'message' => 'Soru sormak için giriş yapmalısınız',
		'login_required' => true,
	]);
	exit;
}

$idProduct = (int) Tools::getValue('id_product');
$question = (string) Tools::getValue('question');
$idUser = Customer::getId();

$result = QuestionModule::submit($idProduct, $question, $idUser, [
	'website' => (string) Tools::getValue('website'),
]);

echo json_encode($result);
