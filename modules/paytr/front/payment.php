<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!Customer::isLoggedIn()) {
	$_SESSION['auth_redirect'] = $domain . 'checkout';
	header('Location: ' . $domain . 'login');
	exit;
}

$idOrder = (int) Tools::getValue('id');
$order = Order::getByIdForUser($idOrder, Customer::getId());

if (!$order || $order['payment_method'] !== 'paytr') {
	header('Location: ' . $domain . 'checkout');
	exit;
}

if ((int) $order['status'] === Order::STATUS_PROCESSING) {
	header('Location: ' . $domain . 'checkout-success?id=' . $idOrder);
	exit;
}

if ((int) $order['status'] !== Order::STATUS_PENDING) {
	header('Location: ' . $domain . 'orders');
	exit;
}

/** @var PaytrModule|null $paytr */
$paytr = Module::getPaymentModule('paytr');

if (!$paytr || !PaytrModule::isConfigured()) {
	header('Location: ' . $domain . 'checkout');
	exit;
}

$paymentError = Tools::getValue('fail') ? 'Ödeme tamamlanamadı. Lütfen tekrar deneyin.' : '';
$paytrToken = null;

$tokenResult = $paytr->getIframeToken($order);

if ($tokenResult['success']) {
	$paytrToken = $tokenResult['token'];
} else {
	$paymentError = $tokenResult['message'] ?? 'PayTR ödeme ekranı yüklenemedi';
}

$skipPageRender = true;

$smarty->assign([
	'pageName' => 'paytr-payment',
	'pageTitle' => 'Kart ile Ödeme',
	'pageDesc' => 'PayTR ile güvenli ödeme',
	'css' => 'checkout.css',
	'js' => false,
	'paymentError' => $paymentError,
	'paytrToken' => $paytrToken,
	'order' => $order,
	'breadcrumb' => [
		['name' => 'Anasayfa', 'url' => $domain],
		['name' => 'Ödeme', 'url' => $domain . 'checkout'],
		['name' => 'Kart ile Ödeme', 'url' => ''],
	],
]);

$smarty->display(_THEME_BASE_DIR_ . 'header.tpl');
$smarty->display('file:' . dirname(__DIR__) . '/assets/templates/front/payment_page.tpl');
$smarty->display(_THEME_BASE_DIR_ . 'footer.tpl');
