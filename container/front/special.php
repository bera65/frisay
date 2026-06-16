<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'catalog.css';
	$sort = (string) Tools::getValue('sort');
	if ($sort === '') {
		$sort = 'discount';
	}
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$productCount = Product::countDiscounted();
	$baseUrl = $domain . 'special';
	$pagination = Pagination::build($productCount, $currentPage, $perPage, $baseUrl, ['sort' => $sort !== 'discount' ? $sort : '']);
	$products = Product::getDiscountedList($perPage, $pagination['offset'], $sort);

	$specialSeo = Seo::resolvePage('special', 'Kampanyalar', 'İndirimli ürünler ve kampanyalar');
	$pageTitle = $specialSeo['title'];
	$pageDesc = $specialSeo['description'];

	$smarty->assign([
		'products' => $products,
		'productCount' => $productCount,
		'pagination' => $pagination,
		'sort' => $sort,
		'sortOptions' => Pagination::getSortOptions(),
		'catalogBaseUrl' => $baseUrl,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Kampanyalar', 'url' => ''],
		],
	]);
