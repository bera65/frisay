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
		'full_name' => '',
		'phone' => '',
		'email' => '',
	];

	if (!empty($_SESSION['auth_redirect'])) {
		$target = (string) $_SESSION['auth_redirect'];

		if (strpos($target, 'checkout') !== false) {
			$authNotice = 'Siparişinizi tamamlamak için üye olun veya giriş yapın. Sepetinizdeki ürünler korunur.';
		}
	}

	if (Tools::isSubmit('registerUser')) {
		$postToken = (string) Tools::getValue('token');

		$formData = [
			'full_name' => (string) Tools::getValue('full_name'),
			'phone' => (string) Tools::getValue('phone'),
			'email' => (string) Tools::getValue('email'),
		];

		if (!hash_equals($token, $postToken)) {
			$authError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
		} else {
			$password = (string) Tools::getValue('password');
			$password2 = (string) Tools::getValue('password2');

			if ($password !== $password2) {
				$authError = 'Şifreler eşleşmiyor';
			} else {
				$result = Customer::register(
					$formData['full_name'],
					$formData['phone'],
					$password,
					$formData['email']
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
	}

	$pageTitle = 'Üye Ol';
	$pageDesc = 'Yeni üyelik oluşturun';

	$smarty->assign([
		'authNotice' => $authNotice,
		'authError' => $authError,
		'formData' => $formData,
		'authMode' => 'register',
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Üye Ol', 'url' => ''],
		],
	]);
