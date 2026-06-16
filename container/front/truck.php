<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$pageTitle = 'Kargo Takip';
	$pageDesc = 'Sipariş durumu sorgulama';
	$css = 'catalog.css';
	$trackResult = null;
	$trackError = '';
	$reference = strtoupper(trim((string) Tools::getValue('reference')));

	if ($reference !== '') {
		$idUser = Customer::isLoggedIn() ? Customer::getId() : null;
		$trackResult = Order::trackByReference($reference, $idUser);

		if (!$trackResult) {
			$trackError = 'Bu sipariş numarası ile kayıt bulunamadı.';
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
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Kargo Takip', 'url' => ''],
		],
	]);
