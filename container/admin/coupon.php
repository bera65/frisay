<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idCoupon = (int) Tools::getValue('id');
	$coupon = $idCoupon > 0 ? Coupon::getById($idCoupon) : null;
	$flash = '';

	if ($idCoupon > 0 && !$coupon) {
		http_response_code(404);
		AdminPage::add('404', 'Coupon not found');
		return;
	}

	if (Tools::isSubmit('saveCoupon')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
		} else {
			$result = Coupon::save([
				'code' => Tools::getValue('code'),
				'discount_type' => Tools::getValue('discount_type'),
				'discount_value' => Tools::getValue('discount_value'),
				'min_cart' => Tools::getValue('min_cart'),
				'max_uses' => Tools::getValue('max_uses'),
				'id_user' => Tools::getValue('id_user'),
				'date_from' => Tools::getValue('date_from'),
				'date_to' => Tools::getValue('date_to'),
				'active' => Tools::getValue('active'),
			], $idCoupon);

			$flash = $result['message'];

			if ($result['success'] && $idCoupon === 0) {
				$newCoupon = Coupon::getByCode((string) Tools::getValue('code'));
				if ($newCoupon) {
					header('Location: ' . Admin::url('coupon') . '?id=' . (int) $newCoupon['id_coupon']);
					exit;
				}
			}

			if ($result['success']) {
				$coupon = Coupon::getById($idCoupon);
			}
		}
	}

	$defaults = [
		'code' => '',
		'discount_type' => 'percent',
		'discount_value' => 10,
		'min_cart' => 0,
		'max_uses' => 0,
		'id_user' => 0,
		'customer_label' => '',
		'date_from' => '',
		'date_to' => '',
		'date_from_input' => '',
		'date_to_input' => '',
		'active' => 1,
	];

	if ($coupon) {
		$coupon['date_from_input'] = Coupon::formatDateTimeInput($coupon['date_from'] ?? null);
		$coupon['date_to_input'] = Coupon::formatDateTimeInput($coupon['date_to'] ?? null);
		$coupon['id_user'] = (int) ($coupon['id_user'] ?? 0);
		$coupon['customer_label'] = '';

		if ($coupon['id_user'] > 0) {
			$user = Customer::getByIdAdmin($coupon['id_user']);
			if ($user) {
				$coupon['customer_label'] = trim((string) ($user['user_full_name'] ?? ''))
					. (!empty($user['email']) ? ' (' . $user['email'] . ')' : '');
			}
		}
	}

	$displayCoupon = $coupon ?: $defaults;
	$customerOptions = Customer::getAdminList('', 80, 0);
	$selectedUserId = (int) ($displayCoupon['id_user'] ?? 0);

	if ($selectedUserId > 0) {
		$found = false;
		foreach ($customerOptions as $opt) {
			if ((int) $opt['id_user'] === $selectedUserId) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			$selected = Customer::getByIdAdmin($selectedUserId);
			if ($selected) {
				array_unshift($customerOptions, $selected);
			}
		}
	}

	$smarty->assign([
		'coupon' => $displayCoupon,
		'isNew' => $idCoupon === 0,
		'flash' => $flash,
		'customerOptions' => $customerOptions,
	]);

	AdminPage::add('coupon', $idCoupon > 0 ? 'Kupon: ' . $coupon['code'] : 'New coupon');
