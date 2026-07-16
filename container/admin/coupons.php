<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';

	if (Tools::isSubmit('deleteCoupon')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
		} else {
			$result = Coupon::delete((int) Tools::getValue('id_coupon'));
			$flash = $result['message'];
		}
	}

	if (Tools::isSubmit('deletePromotion')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
		} else {
			$result = CartPromotion::delete((int) Tools::getValue('id_promotion'));
			$flash = $result['message'];
		}
	}

	$smarty->assign([
		'coupons' => Coupon::getAdminList(),
		'promotions' => CartPromotion::getAdminList(),
		'flash' => $flash,
	]);

	AdminPage::add('coupons', 'Coupons & Promotions');
