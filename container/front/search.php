<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$css = 'catalog.css';
	$query = trim((string) (Tools::getValue('q') ?: Tools::getValue('query')));
	$sort = (string) Tools::getValue('sort');
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$products = [];
	$productCount = 0;
	$pagination = Pagination::build(0, 1, $perPage, $domain . 'search', ['q' => $query, 'sort' => $sort !== 'newest' ? $sort : '']);

	if ($query !== '') {
		$productCount = Product::countSearch($query);
		$pagination = Pagination::build($productCount, $currentPage, $perPage, $domain . 'search', ['q' => $query, 'sort' => $sort !== 'newest' ? $sort : '']);
		$products = Product::search($query, $perPage, $pagination['offset'], $sort);
	}

	$searchSeo = Seo::resolvePage('search', $query !== '' ? 'Arama: ' . $query : 'Arama', 'Ürün ara');
	$pageTitle = $query !== '' ? 'Arama: ' . $query : $searchSeo['title'];
	$pageDesc = $searchSeo['description'];

	$smarty->assign([
		'searchQuery' => $query,
		'products' => $products,
		'productCount' => $productCount,
		'listTitle' => $query !== '' ? '"' . $query . '" için sonuçlar' : 'Arama',
		'pagination' => $pagination,
		'sort' => $sort !== '' ? $sort : 'newest',
		'sortOptions' => Pagination::getSortOptions(),
		'catalogBaseUrl' => $domain . 'search',
		'catalogQuery' => ['q' => $query],
		'emptyMessage' => 'Aramanızla eşleşen ürün bulunamadı.',
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Arama', 'url' => ''],
		],
	]);
