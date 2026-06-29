<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	if (Customer::isLoggedIn()) {
		header('Location: ' . $domain . 'my-account');
		exit;
	}

	$css = 'pages.css';
	$authError = '';
	$authSuccess = '';
	$resetToken = trim((string) ($_GET['token'] ?? ''));

	if ($resetToken === '' || !preg_match('/^[a-f0-9]{64}$/i', $resetToken)) {
		$authError = translate('Invalid or expired reset link');
	}

	if ($authError === '' && Tools::isSubmit('resetPassword')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$authError = translate('Invalid request, please refresh and try again');
		} else {
			$result = Customer::resetPassword(
				$resetToken,
				(string) Tools::getValue('password'),
				(string) Tools::getValue('password2')
			);

			if ($result['success']) {
				$authSuccess = $result['message'];
				$resetToken = '';
			} else {
				$authError = $result['message'];
			}
		}
	}

	$pageTitle = translate('Reset page title');
	$pageDesc = translate('Reset page description');

	$smarty->assign([
		'authError' => $authError,
		'authSuccess' => $authSuccess,
		'resetToken' => $resetToken,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('Sign In'), 'url' => $domain . 'login'],
			['name' => translate('Set New Password'), 'url' => ''],
		],
	]);
