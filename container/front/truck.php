<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$pageTitle = translate('Track page title');
	$pageDesc = translate('Track page description');
	$css = 'pages.css';
	$trackResult = null;
	$trackError = '';
	$reference = strtoupper(trim((string) Tools::getValue('reference')));

	if ($reference !== '') {
		$idUser = Customer::isLoggedIn() ? Customer::getId() : null;
		$trackResult = Order::trackByReference($reference, $idUser);

		if (!$trackResult) {
			$trackError = translate('Order not found for reference');
		}
	}

	$recentOrders = Customer::isLoggedIn()
		? array_slice(Order::getUserOrders(Customer::getId()), 0, 5)
		: [];

	$smarty->assign([
		'reference' => $reference,
		'trackResult' => $trackResult,
		'trackError' => $trackError,
		'recentOrders' => $recentOrders,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('Track Order'), 'url' => ''],
		],
	]);
