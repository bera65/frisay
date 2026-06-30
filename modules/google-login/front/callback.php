<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

global $domain;

$code = trim((string) Tools::getValue('code'));
$state = trim((string) Tools::getValue('state'));
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');

unset($_SESSION['google_oauth_state']);

if ($code === '' || $state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
	header('Location: ' . $domain . 'login?google_error=1');
	exit;
}

$exchange = GoogleLoginModule::exchangeCode($code);

if (empty($exchange['success'])) {
	header('Location: ' . $domain . 'login?google_error=1');
	exit;
}

$result = Customer::authWithGoogle(
	(string) $exchange['google_id'],
	(string) $exchange['email'],
	(string) $exchange['name']
);

if (empty($result['success'])) {
	header('Location: ' . $domain . 'login?google_error=1');
	exit;
}

$redirect = !empty($_SESSION['auth_redirect']) ? (string) $_SESSION['auth_redirect'] : $domain . 'my-account';
unset($_SESSION['auth_redirect']);

header('Location: ' . $redirect);
exit;
