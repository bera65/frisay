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
	$formData = ['email' => ''];

	if (Tools::isSubmit('forgotPassword')) {
		$postToken = (string) Tools::getValue('token');
		$formData['email'] = (string) Tools::getValue('email');

		if (!hash_equals($token, $postToken)) {
			$authError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
		} else {
			$result = Customer::requestPasswordReset($formData['email']);

			if ($result['success']) {
				$authSuccess = $result['message'];
				$formData['email'] = '';
			} else {
				$authError = $result['message'];
			}
		}
	}

	$pageTitle = 'Şifremi Unuttum';
	$pageDesc = 'Şifre sıfırlama bağlantısı isteyin';

	$smarty->assign([
		'authError' => $authError,
		'authSuccess' => $authSuccess,
		'formData' => $formData,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Giriş Yap', 'url' => $domain . 'login'],
			['name' => 'Şifremi Unuttum', 'url' => ''],
		],
	]);
