<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$id 		= (int) Tools::getValue('id');
	$product 	= $id > 0 ? Product::getByIdAdmin($id) : null;
	$flash 		= '';
	$pLink 		= '';
	$isNew 		= $id <= 0;

	if (!$isNew && !$product) {
		http_response_code(404);
		AdminPage::add('404', 'Ürün Bulunamadı');
		return;
	}

	if (Tools::isSubmit('saveProduct')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Product::save($_POST, $id);
			$flash = $result['message'];

			if ($result['success']) {
				header('Location: ' . Admin::url('product?id=' . (int) $result['id'] . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::isSubmit('uploadImage') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Product::uploadImage($id, $_FILES['image'] ?? []);
			$flash = $result['message'];

			if ($result['success']) {
				header('Location: ' . Admin::url('product?id=' . $id . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::isSubmit('setCover') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			$result = Product::setCover((int) Tools::getValue('id_image'));
			$flash = $result['message'];
			$product = Product::getByIdAdmin($id);
		}
	}

	if (Tools::isSubmit('deleteImage') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			$result = Product::deleteImage((int) Tools::getValue('id_image'));
			$flash = $result['message'];
			$product = Product::getByIdAdmin($id);
		}
	}

	if (!$isNew && $product) {
		$pLink = Product::getLink(['id_product' => $id, 'category_link' => $product['category_link'] ?? '', 'product_link' => $product['product_link'] ?? '']);
	}

	if (Tools::getValue('saved')) {
		$flash 		= 'Kayıt güncellendi';
		$product 	= Product::getByIdAdmin($id);
	}

	$form = $product ?: [
		'product_name' 		=> '',
		'product_link' 		=> '',
		'short_description' => '',
		'meta_title' => '',
		'meta_description' => '',
		'description' 		=> '',
		'id_category' 		=> 0,
		'id_brand' 			=> 0,
		'price' 			=> '0.00',
		'doviz' 			=> 'try',
		'doviz_price' 		=> '0.00',
		'doviz_old_price'	=> '0.00',
		'old_price' 		=> '0.00',
		'vat' 				=> '20.00',
		'stock' 			=> 0,
		'cargo_day' 		=> 0,
		'label' 			=> '',
		'product_video' 	=> '',
		'active' 			=> 1,
		'stock_code' 		=> '',
		'barcode' 			=> '',
		'desi' 				=> 1,
		'images' 			=> [],
	];

	$smarty->assign([
		'product' 			=> $form,
		'idProduct' 		=> $id,
		'isNew' 			=> $isNew,
		'flash' 			=> $flash,
		'pLink' 			=> $pLink,
		'categoryOptions' 	=> Category::getMenuList(),
		'brandOptions' 		=> Brand::getOptions(),
		'adminUseEditor' 	=> true,
	]);

	AdminPage::add('product', $isNew ? 'Yeni Ürün' : 'Ürün Düzenle');
