<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

$request = array_merge($_GET, $_POST);
$ctx = NkolaypayModule::resolveReturnContext($request);
$reference = (string) ($ctx['reference'] ?? '');
$returnToken = (string) ($ctx['return_token'] ?? '');
$responseMsg = trim((string) ($ctx['message'] ?? ''));
$returnCode = strtoupper(trim((string) ($ctx['return_code'] ?? '')));
$responseCode = strtoupper(trim((string) ($ctx['response_code'] ?? '')));
$amountRaw = (string) ($ctx['amount'] ?? '0');

$isSuccess = $reference !== ''
	&& in_array($returnCode, ['0', '00', 'SUCCESS'], true)
	&& in_array($responseCode, ['0', '00', 'SUCCESS'], true);

if (!$isSuccess) {
	$message = $responseMsg !== '' ? $responseMsg : 'Ödeme sonucu başarısız';
	$codeBits = array_filter([$returnCode, $responseCode], static function ($v) {
		return $v !== '';
	});

	if ($codeBits !== []) {
		$message .= ' (Kod: ' . implode(' / ', $codeBits) . ')';
	}

	$message = 'N Kolay Pay: ' . $message;

	if ($reference !== '') {
		NkolaypayModule::saveReturnError($reference, $message);
		NkolaypayModule::restoreCartFromPending($reference);
	}

	if ($returnToken !== '') {
		header('Location: ' . NkolaypayModule::getResultUrl($returnToken, true));
		exit;
	}

	$_SESSION['nkolaypay_payment_error'] = $message;
	header('Location: ' . rtrim($domain, '/') . '/nkolaypay-payment?fail=1');
	exit;
}

$paidAmount = NkolaypayModule::parseAmount($amountRaw);
NkolaypayModule::completeOrderAfterPayment($reference, $paidAmount);
Order::clearPendingPayment();

$idOrder = (int) DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);
$target = $idOrder > 0
	? rtrim($domain, '/') . '/checkout-success?id=' . $idOrder
	: rtrim($domain, '/') . '/checkout-success?ref=' . rawurlencode($reference);

header('Location: ' . $target);
exit;
