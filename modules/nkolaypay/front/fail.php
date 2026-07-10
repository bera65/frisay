<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

$request = array_merge($_GET, $_POST);
$ctx = NkolaypayModule::resolveReturnContext($request);
$reference = (string) ($ctx['reference'] ?? '');
$returnToken = (string) ($ctx['return_token'] ?? '');
$message = trim((string) ($ctx['message'] ?? ''));
$returnCode = trim((string) ($ctx['return_code'] ?? ''));
$responseCode = trim((string) ($ctx['response_code'] ?? ''));

if ($message === '') {
	$message = 'Ödeme bankadan onay alamadı';
}

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
