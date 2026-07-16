<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('id');
	$order = Order::getByIdAdmin($idOrder);

	if (!$order) {
		http_response_code(404);
		AdminPage::add('404', 'Order not found', true);
		return;
	}

	$smarty->assign([
		'order' => $order,
		'printAuto' => Tools::getValue('auto') === '1',
		'printSiteName' => Settings::get('SITE_NAME'),
		'printContactPhone' => Settings::get('CONTACT_PHONE') ?: '',
		'printContactEmail' => Settings::get('CONTACT_EMAIL') ?: '',
	]);

	AdminPage::add('order-print', adminT('Order #') . $order['reference'], true);
