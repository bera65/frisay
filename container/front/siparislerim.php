<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$pageTitle = 'Siparişlerim';
	$pageDesc = 'Siparişlerim';
	$orders = Order::getUserOrders(Customer::getId());

	$smarty->assign([
		'orders' => $orders,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Siparişlerim', 'url' => ''],
		],
	]);
