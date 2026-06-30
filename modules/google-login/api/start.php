<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

global $domain;

if (!GoogleLoginModule::isConfigured()) {
	header('Location: ' . rtrim($domain, '/') . '/login?google_error=config');
	exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

if (empty($_SESSION['auth_redirect'])) {
	global $domain;
	$path = parse_url($_SERVER['REQUEST_URI'] ?? '/checkout', PHP_URL_PATH) ?: '/checkout';
	$referer = $_SERVER['HTTP_REFERER'] ?? '';

	if (strpos($referer, 'checkout') !== false) {
		$_SESSION['auth_redirect'] = rtrim($domain, '/') . '/checkout';
	}
}

header('Location: ' . GoogleLoginModule::buildAuthUrl($state));
exit;
