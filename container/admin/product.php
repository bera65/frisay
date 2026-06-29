<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$id 		= (int) Tools::getValue('id');
	$product 	= $id > 0 ? Product::getByIdAdmin($id) : null;
	$flash 		= '';
	$flashType 	= 'success';
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
			$flashType = 'danger';
		} else {
			$result = Product::save($_POST, $id);
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

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
			$flashType = 'danger';
		} else {
			$result = Product::uploadImage($id, $_FILES['image'] ?? []);
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

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
			$flashType = !empty($result['success']) ? 'success' : 'danger';
			$product = Product::getByIdAdmin($id);
		}
	}

	if (Tools::isSubmit('deleteImage') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			$result = Product::deleteImage((int) Tools::getValue('id_image'));
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';
			$product = Product::getByIdAdmin($id);
		}
	}

	if (Tools::isSubmit('uploadVirtualFile') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$result = VirtualProduct::uploadFile($id, $_FILES['virtual_file'] ?? []);
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if ($result['success']) {
				header('Location: ' . Admin::url('product?id=' . $id . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::isSubmit('deleteVirtualFile') && $id > 0) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			$result = VirtualProduct::deleteFile($id);
			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';
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
		'product_type' 		=> 'physical',
		'virtual_kind' 		=> '',
		'virtual_text' 		=> '',
		'virtual_file' 		=> '',
		'virtual_file_name' => '',
		'images' 			=> [],
	];

	$licenseStats = ['available' => 0, 'used' => 0];
	$availableLicenses = [];

	if (!$isNew && $product && VirtualProduct::isVirtualProduct($product) && VirtualProduct::getKind($product) === 'license') {
		$licenseStats = [
			'available' => VirtualProduct::countAvailableLicenses($id),
			'used' => VirtualProduct::countUsedLicenses($id),
		];
		$availableLicenses = VirtualProduct::getAvailableLicenses($id);
	}

	$langRows = $isNew ? [] : Product::getLangRows($id);
	$langForms = [];

	foreach (Lang::getAvailable() as $langCode) {
		$row = $langRows[$langCode] ?? [];

		if ($row === [] && $product) {
			$row = [
				'product_name' => (string) ($product['product_name'] ?? ''),
				'product_link' => (string) ($product['product_link'] ?? ''),
				'short_description' => (string) ($product['short_description'] ?? ''),
				'description' => (string) ($product['description'] ?? ''),
				'meta_title' => (string) ($product['meta_title'] ?? ''),
				'meta_description' => (string) ($product['meta_description'] ?? ''),
			];
		}

		$langForms[$langCode] = [
			'code' => $langCode,
			'label' => Lang::label($langCode),
			'product_name' => (string) ($row['product_name'] ?? ''),
			'product_link' => (string) ($row['product_link'] ?? ''),
			'short_description' => (string) ($row['short_description'] ?? ''),
			'description' => (string) ($row['description'] ?? ''),
			'meta_title' => (string) ($row['meta_title'] ?? ''),
			'meta_description' => (string) ($row['meta_description'] ?? ''),
		];
	}

	$smarty->assign([
		'product' 			=> $form,
		'productLangForms' 	=> $langForms,
		'shopLanguages' 	=> Lang::getAvailable(),
		'idProduct' 		=> $id,
		'isNew' 			=> $isNew,
		'flash' 			=> $flash,
		'flashType' 		=> $flashType,
		'pLink' 			=> $pLink,
		'categoryOptions' 	=> Category::getMenuList(),
		'brandOptions' 		=> Brand::getOptions(),
		'adminUseEditor' 	=> true,
		'licenseStats' 		=> $licenseStats,
		'availableLicenses' => $availableLicenses,
		'adminHooks' => [
			'admin_product_button' => Module::renderDisplayHook('admin_product_button', [
				'id_product' => $id,
				'product' => $form,
				'is_new' => $isNew,
			]),
		],
		'shopCurrencyLabel' => Currency::label(),
	]);

	AdminPage::add('product', $isNew ? 'Yeni Ürün' : 'Ürün Düzenle');
