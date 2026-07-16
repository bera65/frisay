<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';
	$flashType = 'success';

	if (Tools::isSubmit('currencyAction')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$action = trim((string) Tools::getValue('action'));

			switch ($action) {
				case 'add':
					$result = Currency::addCurrency(
						(string) Tools::getValue('code'),
						(string) Tools::getValue('label'),
						(string) Tools::getValue('symbol')
					);
					break;

				case 'remove':
					$result = Currency::removeCurrency((string) Tools::getValue('code'));
					break;

				case 'active':
					$result = Currency::setShopCurrency((string) Tools::getValue('code'));
					break;

				case 'update':
					$result = Currency::updateCurrency(
						(string) Tools::getValue('code'),
						(string) Tools::getValue('label'),
						(string) Tools::getValue('symbol')
					);
					break;

				default:
					$result = ['success' => false, 'message' => adminT('Invalid action')];
					break;
			}

			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';
		}
	}

	$smarty->assign([
		'shopCurrencies' => Currency::getAdminList(),
		'shopCurrency' => Currency::getShopCurrency(),
		'flash' => $flash,
		'flashType' => $flashType,
	]);

	AdminPage::add('currencies', 'Currencies');
