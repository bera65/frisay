<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$id = (int) Tools::getValue('id');
	$brand = $id > 0 ? Brand::getByIdAdmin($id) : null;
	$flash = '';
	$isNew = $id <= 0;

	if (!$isNew && !$brand) {
		http_response_code(404);
		AdminPage::add('404', 'Marka Bulunamadı');
		return;
	}

	if (Tools::isSubmit('saveBrand')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Brand::save($_POST, $id);
			$flash = $result['message'];

			if ($result['success']) {
				header('Location: ' . Admin::url('brand?id=' . (int) $result['id'] . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::getValue('saved')) {
		$flash = 'Marka kaydedildi';
	}

	$form = $brand ?: [
		'brand_name' => '',
		'brand_link' => '',
		'meta_title' => '',
		'meta_description' => '',
		'active' => 1,
	];

	$smarty->assign([
		'brand' => $form,
		'idBrand' => $id,
		'isNew' => $isNew,
		'flash' => $flash,
	]);

	AdminPage::add('brand', $isNew ? 'Yeni Marka' : 'Marka Düzenle');
