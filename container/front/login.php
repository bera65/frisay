<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	if (Customer::isLoggedIn()) {
		$redirect = !empty($_SESSION['auth_redirect']) ? $_SESSION['auth_redirect'] : $domain . 'hesabim';
		unset($_SESSION['auth_redirect']);
		header('Location: ' . $redirect);
		exit;
	}

	$css = 'auth.css';
	$authNotice = '';
	$authError = '';
	$formData = [
		'phone' => '',
	];

	if (!empty($_SESSION['auth_redirect'])) {
		$target = (string) $_SESSION['auth_redirect'];

		if (strpos($target, 'checkout') !== false) {
			$authNotice = 'Siparişinizi tamamlamak için giriş yapın veya yeni üye olun. Sepetinizdeki ürünler korunur.';
		}
	}

	if (Tools::isSubmit('loginUser')) {
		$postToken = (string) Tools::getValue('token');

		$formData = [
			'phone' => (string) Tools::getValue('phone'),
		];

		if (!hash_equals($token, $postToken)) {
			$authError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
		} else {
			$remember = Tools::getValue('remember') !== '0';
			$result = Customer::login(
				$formData['phone'],
				(string) Tools::getValue('password'),
				$remember
			);

			if ($result['success']) {
				$redirect = !empty($_SESSION['auth_redirect']) ? $_SESSION['auth_redirect'] : $domain . 'hesabim';
				unset($_SESSION['auth_redirect']);
				header('Location: ' . $redirect);
				exit;
			}

			$authError = $result['message'];
		}
	}

	$pageTitle = 'Giriş Yap';
	$pageDesc = 'Hesabınıza giriş yapın';

	$smarty->assign([
		'authNotice' => $authNotice,
		'authError' => $authError,
		'formData' => $formData,
		'authMode' => 'login',
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Giriş Yap', 'url' => ''],
		],
	]);
