<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idOrder = (int) Tools::getValue('id');
	$order = Order::getByIdAdmin($idOrder);
	$flash = '';

	if (!$order) {
		http_response_code(404);
		AdminPage::add('404', 'Sipariş Bulunamadı');
		return;
	}

	if (Tools::isSubmit('updateStatus')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Order::updateFromApi($idOrder, [
				'status' => (int) Tools::getValue('status'),
				'cargo_company' => (string) Tools::getValue('cargo_company'),
				'tracking_number' => (string) Tools::getValue('tracking_number'),
			]);
			$flash = $result['message'];

			if ($result['success']) {
				$order = Order::getByIdAdmin($idOrder);
			}
		}
	}

	$smarty->assign([
		'order' => $order,
		'flash' => $flash,
		'statusOptions' => Order::getStatusOptions(),
		'adminHooks' => [
			'admin_order_detail' => Module::renderDisplayHook('admin_order_detail', [
				'id_order' => $idOrder,
				'order' => $order,
			]),
		],
	]);

	AdminPage::add('order', 'Sipariş #' . $order['reference']);
