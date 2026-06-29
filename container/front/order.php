<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('id');
	$order = Order::getByIdForUser($idOrder, Customer::getId());

	if (!$order) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', translate('Page Not Found'));
		return;
	}

	$pageTitle = translate('Order') . ' #' . $order['reference'];
	$pageDesc = translate('Order Detail');

	$smarty->assign([
		'order' => $order,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Orders'), 'url' => $domain . 'orders'],
			['name' => $order['reference'], 'url' => ''],
		],
	]);
