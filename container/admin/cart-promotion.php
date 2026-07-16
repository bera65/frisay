<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idPromotion = (int) Tools::getValue('id');
	$promotion = $idPromotion > 0 ? CartPromotion::getById($idPromotion) : null;
	$flash = '';

	if ($idPromotion > 0 && !$promotion) {
		http_response_code(404);
		AdminPage::add('404', 'Promotion not found');
		return;
	}

	if (Tools::isSubmit('savePromotion')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
		} else {
			$result = CartPromotion::save([
				'name' => Tools::getValue('name'),
				'promo_type' => Tools::getValue('promo_type'),
				'item_position' => Tools::getValue('item_position'),
				'item_discount_type' => Tools::getValue('item_discount_type'),
				'item_discount_value' => Tools::getValue('item_discount_value'),
				'repeat_every' => Tools::getValue('repeat_every'),
				'buy_qty' => Tools::getValue('buy_qty'),
				'pay_qty' => Tools::getValue('pay_qty'),
				'min_cart' => Tools::getValue('min_cart'),
				'date_from' => Tools::getValue('date_from'),
				'date_to' => Tools::getValue('date_to'),
				'active' => Tools::getValue('active'),
			], $idPromotion);

			$flash = $result['message'];

			if ($result['success'] && $idPromotion === 0) {
				$rows = CartPromotion::getAdminList();
				if (!empty($rows[0]['id_promotion'])) {
					header('Location: ' . Admin::url('cart-promotion') . '?id=' . (int) $rows[0]['id_promotion']);
					exit;
				}
			}

			if ($result['success'] && $idPromotion > 0) {
				$promotion = CartPromotion::getById($idPromotion);
			}
		}
	}

	$defaults = [
		'name' => '',
		'promo_type' => 'nth_item',
		'item_position' => 2,
		'item_discount_type' => 'fixed',
		'item_discount_value' => 10,
		'repeat_every' => 0,
		'buy_qty' => 3,
		'pay_qty' => 2,
		'min_cart' => 0,
		'date_from' => '',
		'date_to' => '',
		'date_from_input' => '',
		'date_to_input' => '',
		'active' => 1,
	];

	if ($promotion) {
		$promotion['date_from_input'] = CartPromotion::formatDateTimeInput($promotion['date_from'] ?? null);
		$promotion['date_to_input'] = CartPromotion::formatDateTimeInput($promotion['date_to'] ?? null);
	}

	$smarty->assign([
		'promotion' => $promotion ?: $defaults,
		'isNew' => $idPromotion === 0,
		'flash' => $flash,
	]);

	AdminPage::add('cart-promotion', $idPromotion > 0 ? 'Kampanya: ' . $promotion['name'] : 'New Cart Promotion');
