<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!Order::hasPendingPayment() || $cart['empty']) {
	header('Location: ' . $domain . 'checkout');
	exit;
}

$pendingData = $_SESSION['pending_order_data'];

if (!is_array($pendingData) || (string) ($pendingData['payment_method'] ?? '') !== 'esnekpos') {
	header('Location: ' . $domain . 'checkout');
	exit;
}

/** @var EsnekposModule|null $esnekpos */
$esnekpos = Module::getPaymentModule('esnekpos');

if (!$esnekpos || !EsnekposModule::isConfigured()) {
	header('Location: ' . $domain . 'checkout');
	exit;
}

$reference = trim((string) ($pendingData['_esnekpos_reference'] ?? ''));

if ($reference === '') {
	$reference = Order::reserveReference();
	$pendingData['_esnekpos_reference'] = $reference;
	$_SESSION['pending_order_data'] = $pendingData;
}

EsnekposModule::persistPendingCheckout($reference, $pendingData, $cart);

$previewOrder = EsnekposModule::buildPreviewOrder($pendingData, $cart);
$paymentError = '';

if (Tools::getValue('fail')) {
	$paymentError = (string) ($_SESSION['esnekpos_payment_error'] ?? 'Ödeme tamamlanamadı. Lütfen tekrar deneyin.');
	unset($_SESSION['esnekpos_payment_error']);
}

$cardForm = [
	'holder' => '',
	'number' => '',
	'exp_month' => '',
	'exp_year' => '',
	'installment' => '1',
];

if (Tools::isSubmit('payEsnekpos')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($token, $postToken)) {
		$paymentError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
	} else {
		$cardForm = [
			'holder' => trim((string) Tools::getValue('card_holder')),
			'number' => preg_replace('/[^0-9]/', '', (string) Tools::getValue('card_number')),
			'exp_month' => (string) Tools::getValue('exp_month'),
			'exp_year' => (string) Tools::getValue('exp_year'),
			'installment' => (string) Tools::getValue('installment'),
		];
		$cvv = preg_replace('/[^0-9]/', '', (string) Tools::getValue('cvv'));

		if ($cardForm['holder'] === '') {
			$paymentError = 'Kart üzerindeki ismi girin';
		} elseif (!EsnekposModule::isValidCardNumber($cardForm['number'])) {
			$paymentError = 'Geçerli bir kart numarası girin';
		} elseif (!EsnekposModule::isValidExpiry((int) $cardForm['exp_month'], (int) $cardForm['exp_year'])) {
			$paymentError = 'Son kullanma tarihi geçersiz';
		} elseif (!preg_match('/^[0-9]{3}$/', $cvv)) {
			$paymentError = 'Geçerli bir CVV girin';
		} else {
			$paymentResult = $esnekpos->initiate3DPayment($previewOrder, [
				'holder' => $cardForm['holder'],
				'number' => $cardForm['number'],
				'exp_month' => (int) $cardForm['exp_month'],
				'exp_year' => (int) $cardForm['exp_year'],
				'cvv' => $cvv,
				'installment' => max(1, (int) $cardForm['installment']),
			]);

			if ($paymentResult['success'] && !empty($paymentResult['url_3ds'])) {
				header('Location: ' . $paymentResult['url_3ds']);
				exit;
			}

			$paymentError = $paymentResult['message'] ?? 'EsnekPOS ödeme başlatılamadı';
		}
	}
}

$skipPageRender = true;

$smarty->assign([
	'pageName' => 'esnekpos-payment',
	'pageTitle' => 'Kart ile Ödeme',
	'pageDesc' => 'EsnekPOS ile güvenli 3D ödeme',
	'css' => 'checkout.css',
	'js' => false,
	'paymentError' => $paymentError,
	'cardForm' => $cardForm,
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
