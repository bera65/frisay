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
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$ok = true;

			foreach (array_keys($editableKeys) as $key) {
				if ($key === 'SMTP_PASS' && trim((string) Tools::getValue('SMTP_PASS')) === '') {
					continue;
				}

				$value = trim((string) Tools::getValue($key));

				if ($key === 'ORDER_REF_PREFIX') {
					$value = Order::sanitizeReferencePrefix($value);
				} elseif ($key === 'ORDER_REF_SUFFIX_MODE') {
					$modes = array_keys(Order::getReferenceSuffixModes());
					if (!in_array(strtolower($value), $modes, true)) {
						$value = 'sequential';
					}
				} elseif ($key === 'ORDER_REF_PAD') {
					$value = (string) max(3, min(10, (int) $value ?: 5));
				}

				if (!Settings::set($key, $value)) {
					$ok = false;
				}
			}

			$flash = $ok ? adminT('Settings saved') : adminT('Some settings could not be saved');
			$flashType = $ok ? 'success' : 'danger';
			$values = Settings::getAllForAdmin();
			$mailConfigured = Mail::isConfigured();
			$usesSmtp = Mail::usesSmtp();
		}
	}

	if (Tools::isSubmit('testMail')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} elseif (!$mailConfigured) {
			$flash = $usesSmtp
				? adminT('Complete SMTP settings. Enter server, user and password.')
				: adminT('Enter contact or sender email for PHP mail().');
			$flashType = 'danger';
		} else {
			$testTo = trim((string) Tools::getValue('test_email'));
			if ($testTo === '') {
				$testTo = Settings::get('SMTP_FROM_EMAIL') ?: Settings::get('SMTP_USER') ?: Settings::get('CONTACT_EMAIL');
			}

			$driverLabel = $usesSmtp ? 'SMTP' : 'PHP mail()';

			if ($testTo === '' || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
				$flash = adminT('Enter a valid email address for the test');
				$flashType = 'danger';
			} elseif (Mail::send($testTo, 'Test Mail - ' . Settings::get('SITE_NAME'), '<p>Bu bir test e-postasıdır. <strong>' . $driverLabel . '</strong> ile gönderildi.</p>')) {
				$flash = adminT('Test email sent') . ' (' . $driverLabel . '): ' . $testTo;
				$flashType = 'success';
			} else {
				$error = Mail::getLastError();
				$flash = adminT('Test email could not be sent') . ' (' . $driverLabel . ')' . ($error !== '' ? ': ' . $error : '');
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('saveWebApi') || Tools::isSubmit('regenWebApiKey')) {
		$flash = adminT('Web API management moved: Admin → API menu');
		$flashType = 'info';
	}

	$smarty->assign([
		'settingsValues' => $values,
		'settingsKeys' => $editableKeys,
		'shopCurrencyCode' => Currency::getShopCurrency(),
		'shopCurrencyLabel' => Currency::label(),
		'orderRefPreview' => Order::previewReference(),
		'orderRefModes' => Order::getReferenceSuffixModes(),
		'orderRefActiveMode' => Order::getReferenceSettings()['suffix_mode'],
		'flash' => $flash,
		'flashType' => $flashType,
		'mailConfigured' => $mailConfigured,
		'usesSmtp' => $usesSmtp,
		'readOnlySettings' => [
			'DOMAIN' => Settings::get('DOMAIN'),
			'FOLDER' => Settings::get('FOLDER'),
		],
	]);

	AdminPage::add('settings', 'Site settings');
