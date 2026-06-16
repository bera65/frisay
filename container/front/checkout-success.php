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

	$pageTitle = 'Sipariş Alındı';
	$pageDesc = 'Sipariş Alındı';

	$smarty->assign([
		'order' => $order,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Sipariş Onayı', 'url' => ''],
		],
	]);

	// Ödeme modülleri onay sayfasına bilgi basabilir (ör. havale IBAN bilgileri)
	Module::refreshHook($smarty, 'order_confirmation', ['order' => $order]);
