<?php



if (!defined('IN_SCRIPT')) {

	exit;

}



if (!Order::hasPendingPayment() || $cart['empty']) {

	header('Location: ' . $domain . 'checkout');

	exit;

}



$pendingData = $_SESSION['pending_order_data'];



if (!is_array($pendingData) || (string) ($pendingData['payment_method'] ?? '') !== 'paytr') {

	header('Location: ' . $domain . 'checkout');

	exit;

}



/** @var PaytrModule|null $paytr */

$paytr = Module::getPaymentModule('paytr');



if (!$paytr || !PaytrModule::isConfigured()) {

	header('Location: ' . $domain . 'checkout');

	exit;

}



$reference = trim((string) ($pendingData['_paytr_reference'] ?? ''));



if ($reference === '') {

	$reference = Order::reserveReference();

	$pendingData['_paytr_reference'] = $reference;

	$_SESSION['pending_order_data'] = $pendingData;

}



PaytrModule::persistPendingCheckout($reference, $pendingData, $cart);



$previewOrder = PaytrModule::buildPreviewOrder($pendingData, $cart);

$paymentError = Tools::getValue('fail') ? 'Ödeme tamamlanamadı. Lütfen tekrar deneyin.' : '';

$paytrToken = null;



$tokenResult = $paytr->getIframeToken($previewOrder);



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

	'order' => $previewOrder,

	'breadcrumb' => [

		['name' => 'Anasayfa', 'url' => $domain],

		['name' => 'Ödeme', 'url' => $domain . 'checkout'],

		['name' => 'Kart ile Ödeme', 'url' => ''],

	],

]);



$smarty->display(_THEME_BASE_DIR_ . 'header.tpl');

$smarty->display('file:' . dirname(__DIR__) . '/assets/templates/front/payment_page.tpl');

$smarty->display(_THEME_BASE_DIR_ . 'footer.tpl');

