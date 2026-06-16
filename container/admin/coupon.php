<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idCoupon = (int) Tools::getValue('id');
	$coupon = $idCoupon > 0 ? Coupon::getById($idCoupon) : null;
	$flash = '';

	if ($idCoupon > 0 && !$coupon) {
		http_response_code(404);
		AdminPage::add('404', 'Kupon Bulunamadı');
		return;
	}

	if (Tools::isSubmit('saveCoupon')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Coupon::save([
				'code' => Tools::getValue('code'),
				'discount_type' => Tools::getValue('discount_type'),
				'discount_value' => Tools::getValue('discount_value'),
				'min_cart' => Tools::getValue('min_cart'),
				'max_uses' => Tools::getValue('max_uses'),
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
		'date_from' => '',
		'date_to' => '',
		'date_from_input' => '',
		'date_to_input' => '',
		'active' => 1,
	];

	if ($coupon) {
		$coupon['date_from_input'] = Coupon::formatDateTimeInput($coupon['date_from'] ?? null);
		$coupon['date_to_input'] = Coupon::formatDateTimeInput($coupon['date_to'] ?? null);
	}

	$smarty->assign([
		'coupon' => $coupon ?: $defaults,
		'isNew' => $idCoupon === 0,
		'flash' => $flash,
	]);

	AdminPage::add('coupon', $idCoupon > 0 ? 'Kupon: ' . $coupon['code'] : 'Yeni Kupon');
