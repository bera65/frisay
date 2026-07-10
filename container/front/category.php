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

	$baseUrl = Category::getUrl($category);



	$catalogFilter = CatalogFilter::forCategory($idCategory);

	$categoryIds = $catalogFilter->getCategoryIds();

	$filterQuery = $catalogFilter->toQueryArray();

	$paginationQuery = $filterQuery;



	if ($sort !== '' && $sort !== 'newest') {

		$paginationQuery['sort'] = $sort;

	}



	$productCount = Product::countFiltered(

		$categoryIds,

		$catalogFilter->brandId > 0 ? $catalogFilter->brandId : null,

		$catalogFilter->priceMin,

		$catalogFilter->priceMax

	);



	$pagination = Pagination::build($productCount, $currentPage, $perPage, $baseUrl, $paginationQuery);

	$products = Product::getFilteredList(

		$categoryIds,

		$perPage,

		$pagination['offset'],

		$sort !== '' ? $sort : 'newest',

		$catalogFilter->brandId > 0 ? $catalogFilter->brandId : null,

		$catalogFilter->priceMin,

		$catalogFilter->priceMax

	);



	$subcategories = Category::getChildren($idCategory);

	foreach ($subcategories as &$sub) {
		$sub['filter_url'] = $catalogFilter->buildUrl(Category::getUrl($sub), ['subcat' => null]);
	}
	unset($sub);

	$filterBrands = Category::getBrandsInCategories($categoryIds);

	$priceRange = Product::getPriceRangeForCategories($categoryIds);



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

		'catalogFilterQuery' => $filterQuery,

		'catalogFilter' => [

			'subCategoryId' => $catalogFilter->subCategoryId,

			'brandId' => $catalogFilter->brandId,

			'priceMin' => $catalogFilter->priceMin,

			'priceMax' => $catalogFilter->priceMax,

			'hasActive' => $catalogFilter->hasActiveFilters(),

			'clearUrl' => $baseUrl,

		],

		'filterSubcategories' => $subcategories,

		'filterSubcategoryAllUrl' => $catalogFilter->buildUrl($baseUrl, ['subcat' => null]),

		'filterBrands' => $filterBrands,

		'filterPriceRange' => $priceRange,

		'emptyMessage' => translate('No products in this category yet.'),

		'breadcrumb' => [

			['name' => translate('Home Page'), 'url' => $domain],

			['name' => $category['category_name'], 'url' => ''],

		],

	]);


