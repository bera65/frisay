<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$pageTitle = translate('Orders page title');
	$pageDesc = translate('Orders page description');
	$orders = Order::getUserOrders(Customer::getId());

	$smarty->assign([
		'orders' => $orders,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Orders'), 'url' => ''],
		],
	]);
