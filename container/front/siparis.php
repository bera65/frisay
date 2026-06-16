<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('id');
	$order = Order::getByIdForUser($idOrder, Customer::getId());

	if (!$order) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', 'Sayfa Bulunamadı');
		return;
	}

	$pageTitle = 'Sipariş #' . $order['reference'];
	$pageDesc = 'Sipariş Detayı';

	$smarty->assign([
		'order' => $order,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Siparişlerim', 'url' => $domain . 'siparislerim'],
			['name' => $order['reference'], 'url' => ''],
		],
	]);
