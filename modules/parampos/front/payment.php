<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!Order::hasPendingPayment() || $cart['empty']) {
	header('Location: ' . $domain . 'checkout');
	exit;
}

$pendingData = $_SESSION['pending_order_data'];

if (!is_array($pendingData) || (string) ($pendingData['payment_method'] ?? '') !== 'parampos') {
	header('Location: ' . $domain . 'checkout');
	exit;
}

/** @var ParamposModule|null $parampos */
$parampos = Module::getPaymentModule('parampos');

if (!$parampos || !ParamposModule::isConfigured()) {
	header('Location: ' . $domain . 'checkout');
	exit;
}

$reference = trim((string) ($pendingData['_parampos_reference'] ?? ''));

if ($reference === '') {
	$reference = Order::reserveReference();
	$pendingData['_parampos_reference'] = $reference;
	$_SESSION['pending_order_data'] = $pendingData;
}

ParamposModule::persistPendingCheckout($reference, $pendingData, $cart);

$previewOrder = ParamposModule::buildPreviewOrder($pendingData, $cart);
$paymentError = '';
$paymentUrl = '';

if (Tools::getValue('fail')) {
	$paymentError = (string) ($_SESSION['parampos_payment_error'] ?? 'Ödeme tamamlanamadı. Lütfen tekrar deneyin.');
	unset($_SESSION['parampos_payment_error']);
}

if ($paymentError === '') {
	$result = $parampos->createHostedPayment($previewOrder);

	if (!empty($result['success']) && !empty($result['payment_url'])) {
		$paymentUrl = (string) $result['payment_url'];
		header('Location: ' . $paymentUrl);
		exit;
	}

	$paymentError = $result['message'] ?? 'ParamPOS ortak ödeme sayfası oluşturulamadı';
}

$skipPageRender = true;

$smarty->assign([
	'pageName' => 'parampos-payment',
	'pageTitle' => 'ParamPOS ile Ödeme',
	'pageDesc' => 'ParamPOS ortak ödeme sayfası',
	'css' => 'checkout.css',
	'js' => false,
	'paymentError' => $paymentError,
	'paymentUrl' => $paymentUrl,
	'order' => $previewOrder,
	'breadcrumb' => [
		['name' => 'Anasayfa', 'url' => $domain],
		['name' => 'Ödeme', 'url' => $domain . 'checkout'],
		['name' => 'ParamPOS', 'url' => ''],
	],
]);

$smarty->display(_THEME_BASE_DIR_ . 'header.tpl');
$smarty->display('file:' . dirname(__DIR__) . '/assets/templates/front/payment_page.tpl');
$smarty->display(_THEME_BASE_DIR_ . 'footer.tpl');
