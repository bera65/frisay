<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$link = defined('CATEGORY_SLUG') ? CATEGORY_SLUG : (string) Tools::getValue('link');
	$category = Category::getByLink($link);

	if (!$category) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', translate('Page Not Found'));
		return;
	}

	$css = 'catalog.css';
	$sort = (string) Tools::getValue('sort');
	$currentPage = max(1, (int) Tools::getValue('page'));
	$perPage = Pagination::PER_PAGE;
	$idCategory = (int) $category['id_category'];
	$productCount = Product::countActive($idCategory);
	$baseUrl = Category::getUrl($category);
	$pagination = Pagination::build($productCount, $currentPage, $perPage, $baseUrl, ['sort' => $sort !== 'newest' ? $sort : '']);
	$products = Product::getActiveList($idCategory, $perPage, $pagination['offset'], $sort);

	$categorySeo = Seo::resolveEntity(
		(string) ($category['meta_title'] ?? ''),
		(string) ($category['meta_description'] ?? ''),
		$category['category_name'],
		$category['category_name'] . ' ürünleri'
	);
	$pageTitle = $categorySeo['title'];
	$pageDesc = $categorySeo['description'];

	$smarty->assign([
		'category' => $category,
		'products' => $products,
		'productCount' => $productCount,
		'listTitle' => $category['category_name'],
		'pagination' => $pagination,
		'sort' => $sort !== '' ? $sort : 'newest',
		'sortOptions' => Pagination::getSortOptions(),
		'catalogBaseUrl' => $baseUrl,
		'emptyMessage' => translate('No products in this category yet.'),
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => $category['category_name'], 'url' => ''],
		],
	]);
