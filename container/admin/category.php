<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$id = (int) Tools::getValue('id');
	$category = $id > 0 ? Category::getByIdAdmin($id) : null;
	$flash = '';
	$isNew = $id <= 0;

	if (!$isNew && !$category) {
		http_response_code(404);
		AdminPage::add('404', 'Kategori Bulunamadı');
		return;
	}

	if (Tools::isSubmit('saveCategory')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Category::save($_POST, $id);
			$flash = $result['message'];

			if ($result['success']) {
				header('Location: ' . Admin::url('category?id=' . (int) $result['id'] . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::getValue('saved')) {
		$flash = 'Kategori kaydedildi';
	}

	$form = $category ?: [
		'category_name' => '',
		'category_link' => '',
		'meta_title' => '',
		'meta_description' => '',
		'id_parent' => 0,
		'active' => 1,
	];

	$smarty->assign([
		'category' => $form,
		'idCategory' => $id,
		'isNew' => $isNew,
		'flash' => $flash,
		'parentOptions' => Category::getParentOptions($id),
	]);

	AdminPage::add('category', $isNew ? 'Yeni Kategori' : 'Kategori Düzenle');
