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
		'message' => 'Yorum yazmak için giriş yapmalısınız',
		'login_required' => true,
	]);
	exit;
}

$idProduct = (int) Tools::getValue('id_product');
$rating = (int) Tools::getValue('rating', 5);
$title = (string) Tools::getValue('title');
$comment = (string) Tools::getValue('comment');
$idUser = Customer::getId();

$result = ReviewsModule::submit($idProduct, $rating, $title, $comment, $idUser, '', [
	'website' => (string) Tools::getValue('website'),
]);

echo json_encode($result);
