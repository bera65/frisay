<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idUser = (int) Tools::getValue('id');
	$customer = Customer::getByIdAdmin($idUser);
	$flash = '';

	if (!$customer) {
		http_response_code(404);
		AdminPage::add('404', 'Müşteri Bulunamadı');
		return;
	}

	if (Tools::isSubmit('toggleActive')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Customer::setActive($idUser, !(int) $customer['active']);
			$flash = $result['message'];

			if ($result['success']) {
				$customer = Customer::getByIdAdmin($idUser);
			}
		}
	}

	$smarty->assign([
		'customer' => $customer,
		'flash' => $flash,
	]);

	AdminPage::add('customer', $customer['user_full_name']);
