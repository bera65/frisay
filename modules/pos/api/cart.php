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

// URL'de ?action=cart olduğu için gövde parametresi cart_op kullanılır
$cartOp = trim((string) ($_POST['cart_op'] ?? ''));

switch ($cartOp) {
	case 'add':
		$result = $pos->addToCart(
			(int) Tools::getValue('id_product'),
			max(1, (int) Tools::getValue('qty', 1)),
			(int) Tools::getValue('id_variation', 0)
		);
		break;

	case 'update':
		$result = $pos->updateCartQty(
			trim((string) Tools::getValue('key')),
			(int) Tools::getValue('qty')
		);
		break;

	case 'remove':
		$result = $pos->removeFromCart(trim((string) Tools::getValue('key')));
		break;

	case 'clear':
		$result = $pos->clearCart();
		break;

	case 'get':
		$result = [
			'success' => true,
			'cart' => $pos->getCartSummary(),
		];
		break;

	default:
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
		exit;
}

echo json_encode($result);
