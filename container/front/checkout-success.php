<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'pages.css';
	$idOrder = (int) Tools::getValue('id');
	$reference = trim((string) Tools::getValue('ref'));

	// PayTR gibi ödemelerde önce callback siparişi oluşturur; ref ile kısa süre bekleyebiliriz
	if ($idOrder <= 0 && $reference !== '') {
		for ($attempt = 0; $attempt < 8; $attempt++) {
			$idOrder = (int) DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);

			if ($idOrder > 0) {
				break;
			}

			usleep(500000);
		}
	}

	// Referansla eşleşen sipariş → ödeme dönüşü erişimi (oturum/cookie kaybına karşı)
	if ($reference !== '' && $idOrder > 0) {
		$refMatch = (string) DB::getValue(
			'SELECT reference FROM orders WHERE id_order = ? LIMIT 1',
			[$idOrder]
		);

		if ($refMatch !== '' && hash_equals($refMatch, $reference)) {
			Order::grantGuestOrderAccess($idOrder);
		}
	}

	$order = Order::getByIdForViewer($idOrder);

	if (!$order && $reference !== '') {
		$idOrder = (int) DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);

		if ($idOrder > 0) {
			Order::grantGuestOrderAccess($idOrder);
			$order = Order::getByIdForViewer($idOrder);
		}
	}

	if ($order) {
		Order::clearPendingPayment();
		Cart::clear();
	}

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
