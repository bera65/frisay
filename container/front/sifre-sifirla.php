<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	if (Customer::isLoggedIn()) {
		header('Location: ' . $domain . 'hesabim');
		exit;
	}

	$css = 'auth.css';
	$authError = '';
	$authSuccess = '';
	// Sıfırlama token'ı URL'de; POST'taki hidden "token" alanı CSRF içindir.
	$resetToken = trim((string) ($_GET['token'] ?? ''));

	if ($resetToken === '' || !preg_match('/^[a-f0-9]{64}$/i', $resetToken)) {
		$authError = 'Geçersiz veya süresi dolmuş bağlantı';
	}

	if ($authError === '' && Tools::isSubmit('resetPassword')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$authError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
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

	$pageTitle = 'Şifre Sıfırla';
	$pageDesc = 'Yeni şifrenizi belirleyin';

	$smarty->assign([
		'authError' => $authError,
		'authSuccess' => $authSuccess,
		'resetToken' => $resetToken,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Giriş Yap', 'url' => $domain . 'login'],
			['name' => 'Şifre Sıfırla', 'url' => ''],
		],
	]);
