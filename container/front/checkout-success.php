<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'pages.css';
	$idOrder = (int) Tools::getValue('id');
	$order = Order::getByIdForUser($idOrder, Customer::getId());

	if (!$order) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', translate('Page Not Found'));
		return;
	}

	$pageTitle = translate('Checkout success title');
	$pageDesc = translate('Checkout success description');

	$smarty->assign([
		'order' => $order,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('Order confirmation'), 'url' => ''],
		],
	]);

	Module::refreshHook($smarty, 'order_confirmation', ['order' => $order]);
