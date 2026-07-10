<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

$returnToken = trim((string) Tools::getValue('rt'));
$failed = (bool) Tools::getValue('fail');
$row = $returnToken !== '' ? NkolaypayModule::loadPendingByReturnToken($returnToken) : null;
$reference = $row ? (string) $row['reference'] : '';
$paymentError = $row ? trim((string) ($row['last_error'] ?? '')) : '';

if ($reference !== '') {
	NkolaypayModule::restoreCartFromPending($reference);
}

if ($paymentError === '') {
	$paymentError = (string) ($_SESSION['nkolaypay_payment_error'] ?? '');
	unset($_SESSION['nkolaypay_payment_error']);
}

if ($paymentError === '') {
	$paymentError = $failed
		? 'Ödeme tamamlanamadı. Lütfen kart bilgilerinizi kontrol edip tekrar deneyin.'
		: 'Ödeme sonucu alınamadı.';
}

$pendingData = isset($_SESSION['pending_order_data']) && is_array($_SESSION['pending_order_data'])
	? $_SESSION['pending_order_data']
	: [];
$cartSummary = Cart::getSummary();

if ($reference !== '' && $pendingData !== [] && empty($cartSummary['empty'])) {
	$pendingData['_nkolaypay_reference'] = $reference;
}

$skipPageRender = true;

$smarty->assign([
	'pageName' => 'nkolaypay-result',
	'pageTitle' => 'Ödeme Sonucu',
	'pageDesc' => 'N Kolay Pay ödeme sonucu',
	'css' => 'checkout.css',
	'js' => false,
	'paymentError' => $paymentError,
	'reference' => $reference,
	'cartEmpty' => !empty($cartSummary['empty']),
	'breadcrumb' => [
		['name' => 'Anasayfa', 'url' => $domain],
		['name' => 'Ödeme', 'url' => $domain . 'checkout'],
		['name' => 'Ödeme Sonucu', 'url' => ''],
	],
]);

$smarty->display(_THEME_BASE_DIR_ . 'header.tpl');
$smarty->display('file:' . dirname(__DIR__) . '/assets/templates/front/result.tpl');
$smarty->display(_THEME_BASE_DIR_ . 'footer.tpl');
