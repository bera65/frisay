<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';
	$flashType = 'info';
	$editableKeys = Settings::getEditableKeys();
	$values = Settings::getAllForAdmin();
	$mailConfigured = Mail::isConfigured();
	$usesSmtp = Mail::usesSmtp();

	if (Tools::isSubmit('saveSettings')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$ok = true;

			foreach (array_keys($editableKeys) as $key) {
				if ($key === 'SMTP_PASS' && trim((string) Tools::getValue('SMTP_PASS')) === '') {
					continue;
				}

				$value = trim((string) Tools::getValue($key));
				if (!Settings::set($key, $value)) {
					$ok = false;
				}
			}

			$flash = $ok ? 'Ayarlar kaydedildi' : 'Bazı ayarlar kaydedilemedi';
			$flashType = $ok ? 'success' : 'danger';
			$values = Settings::getAllForAdmin();
			$mailConfigured = Mail::isConfigured();
			$usesSmtp = Mail::usesSmtp();
		}
	}

	if (Tools::isSubmit('testMail')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} elseif (!$mailConfigured) {
			$flash = $usesSmtp
				? 'SMTP ayarları eksik. Sunucu, kullanıcı ve şifre girin.'
				: 'PHP mail() için İletişim e-postası veya gönderen e-posta girin.';
			$flashType = 'danger';
		} else {
			$testTo = trim((string) Tools::getValue('test_email'));
			if ($testTo === '') {
				$testTo = Settings::get('SMTP_FROM_EMAIL') ?: Settings::get('SMTP_USER') ?: Settings::get('CONTACT_EMAIL');
			}

			$driverLabel = $usesSmtp ? 'SMTP' : 'PHP mail()';

			if ($testTo === '' || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
				$flash = 'Test için geçerli bir e-posta adresi girin';
				$flashType = 'danger';
			} elseif (Mail::send($testTo, 'Test Mail - ' . Settings::get('SITE_NAME'), '<p>Bu bir test e-postasıdır. <strong>' . $driverLabel . '</strong> ile gönderildi.</p>')) {
				$flash = 'Test maili gönderildi (' . $driverLabel . '): ' . $testTo;
				$flashType = 'success';
			} else {
				$error = Mail::getLastError();
				$flash = 'Test maili gönderilemedi (' . $driverLabel . ')' . ($error !== '' ? ': ' . $error : '');
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('saveWebApi')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			Settings::set('WEBAPI_ENABLED', Tools::getValue('WEBAPI_ENABLED') ? '1' : '0');
			$flash = 'Web API ayarı kaydedildi';
			$flashType = 'success';
		}
	}

	if (Tools::isSubmit('regenWebApiKey')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			Settings::set('WEBAPI_KEY', bin2hex(random_bytes(32)));
			Settings::set('WEBAPI_ENABLED', '1');
			$flash = 'Web API anahtarı oluşturuldu / yenilendi';
			$flashType = 'success';
		}
	}

	$webApiKey = (string) Settings::get('WEBAPI_KEY');
	$webApiEnabled = Settings::get('WEBAPI_ENABLED') === '1';
	$webApiUrl = rtrim($domain, '/') . '/api/v1/';

	$smarty->assign([
		'settingsValues' => $values,
		'settingsKeys' => $editableKeys,
		'shopCurrencyCode' => Currency::getShopCurrency(),
		'shopCurrencyLabel' => Currency::label(),
		'flash' => $flash,
		'flashType' => $flashType,
		'mailConfigured' => $mailConfigured,
		'usesSmtp' => $usesSmtp,
		'readOnlySettings' => [
			'DOMAIN' => Settings::get('DOMAIN'),
			'FOLDER' => Settings::get('FOLDER'),
		],
		'webApiKey' => $webApiKey,
		'webApiEnabled' => $webApiEnabled,
		'webApiUrl' => $webApiUrl,
	]);

	AdminPage::add('settings', 'Site Ayarları');
