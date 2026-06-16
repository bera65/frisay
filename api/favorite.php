<?php
define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';
require_once dirname(__DIR__) . '/core/Favorite.php';

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

$action = Tools::getValue('action');
$idProduct = (int) Tools::getValue('id_product');

switch ($action) {
	case 'toggle':
		echo json_encode(Favorite::toggle($idProduct));
		break;
	case 'remove':
		echo json_encode(Favorite::remove($idProduct));
		break;
	default:
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
}
