<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$pageTitle = 'İletişim';
	$pageDesc = 'Bizimle iletişime geçin';
	$contactSeo = Seo::resolvePage('contact', $pageTitle, $pageDesc);
	$pageTitle = $contactSeo['title'];
	$pageDesc = $contactSeo['description'];
	$contactSuccess = '';
	$contactError = '';
	$formData = [
		'full_name' => Customer::isLoggedIn() ? ($customer['user_full_name'] ?? '') : '',
		'email' => '',
		'phone' => Customer::isLoggedIn() ? ($customer['phone'] ?? '') : '',
		'subject' => '',
		'message' => '',
	];

	if (Tools::isSubmit('sendContact')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$contactError = 'Geçersiz istek, sayfayı yenileyip tekrar deneyin';
		} else {
			$formData = [
				'full_name' => (string) Tools::getValue('full_name'),
				'email' => (string) Tools::getValue('email'),
				'phone' => (string) Tools::getValue('phone'),
				'subject' => (string) Tools::getValue('subject'),
				'message' => (string) Tools::getValue('message'),
				'website' => (string) Tools::getValue('website'),
			];

			$result = Contact::submit($formData);

			if ($result['success']) {
				$contactSuccess = $result['message'];
				$formData = [
					'full_name' => Customer::isLoggedIn() ? ($customer['user_full_name'] ?? '') : '',
					'email' => '',
					'phone' => Customer::isLoggedIn() ? ($customer['phone'] ?? '') : '',
					'subject' => '',
					'message' => '',
				];
			} else {
				$contactError = $result['message'];
			}
		}
	}

	$smarty->assign([
		'contactSuccess' => $contactSuccess,
		'contactError' => $contactError,
		'formData' => $formData,
		'contactEmail' => Settings::get('CONTACT_EMAIL'),
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'İletişim', 'url' => ''],
		],
	]);
