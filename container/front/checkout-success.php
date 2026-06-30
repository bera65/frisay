<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'pages.css';
	$idOrder = (int) Tools::getValue('id');
	$reference = trim((string) Tools::getValue('ref'));

	if ($idOrder <= 0 && $reference !== '') {
		for ($attempt = 0; $attempt < 6; $attempt++) {
			$idOrder = (int) DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);

			if ($idOrder > 0) {
				break;
			}

			usleep(400000);
		}
	}

	$order = Order::getByIdForViewer($idOrder);

	if (!$order && $reference !== '') {
		$idOrder = (int) DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);

		if ($idOrder > 0) {
			if (Customer::getId() <= 0) {
				Order::grantGuestOrderAccess($idOrder);
			}

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
