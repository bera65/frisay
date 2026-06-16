<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$link = (string) Tools::getValue('link');
	$brand = Brand::getByLink($link);

	if (!$brand) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', 'Sayfa Bulunamadı');
		return;
	}

	$css = 'catalog.css';
	$sort = (string) Tools::getValue('sort');
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$idBrand = (int) $brand['id_brand'];
	$productCount = Product::countActive(null, $idBrand);
	$baseUrl = Brand::getUrl($brand);
	$pagination = Pagination::build($productCount, $currentPage, $perPage, $baseUrl, ['sort' => $sort !== 'newest' ? $sort : '']);
	$products = Product::getActiveList(null, $perPage, $pagination['offset'], $sort, $idBrand);

	$brandSeo = Seo::resolveEntity(
		(string) ($brand['meta_title'] ?? ''),
		(string) ($brand['meta_description'] ?? ''),
		$brand['brand_name'],
		$brand['brand_name'] . ' ürünleri'
	);
	$pageTitle = $brandSeo['title'];
	$pageDesc = $brandSeo['description'];

	$smarty->assign([
		'brand' => $brand,
		'products' => $products,
		'productCount' => $productCount,
		'listTitle' => $brand['brand_name'],
		'pagination' => $pagination,
		'sort' => $sort !== '' ? $sort : 'newest',
		'sortOptions' => Pagination::getSortOptions(),
		'catalogBaseUrl' => $baseUrl,
		'emptyMessage' => 'Bu markaya ait ürün bulunamadı.',
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => $brand['brand_name'], 'url' => ''],
		],
	]);
