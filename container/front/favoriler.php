<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'catalog.css';
	$pageTitle = 'Favorilerim';
	$pageDesc = 'Favorilerim';
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$productCount = Favorite::getCount();
	$baseUrl = $domain . 'favoriler';
	$pagination = Pagination::build($productCount, $currentPage, $perPage, $baseUrl);
	$products = Favorite::getList(null, $perPage, $pagination['offset']);

	$smarty->assign([
		'products' => $products,
		'productCount' => $productCount,
		'pagination' => $pagination,
		'listTitle' => 'Favori Ürünlerim',
		'showFavoriteRemove' => true,
		'emptyMessage' => 'Henüz favori ürününüz yok.',
		'catalogBaseUrl' => $baseUrl,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Favorilerim', 'url' => ''],
		],
	]);
