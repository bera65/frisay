<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';

	if (Tools::isSubmit('deleteCoupon')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Coupon::delete((int) Tools::getValue('id_coupon'));
			$flash = $result['message'];
		}
	}

	$smarty->assign([
		'coupons' => Coupon::getAdminList(),
		'flash' => $flash,
	]);

	AdminPage::add('coupons', 'Kuponlar');
