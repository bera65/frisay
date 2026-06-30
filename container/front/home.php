<?php
	if (!defined('IN_SCRIPT')) {
		header('HTTP/1.0 404 Not Found');
		header('Location: ../404');
		exit;
	}

	$homeSeo = Seo::resolvePage('home');
	$pageTitle = $homeSeo['title'];
	$pageDesc = $homeSeo['description'];

	$featuredProducts = Product::getActiveList(null, 12);
	$categoryBlocks = [];

	foreach (Category::getMenuList() as $cat) {
		$products = Product::getActiveList((int) $cat['id_category'], 8);

		if (!$products) {
			continue;
		}

		$categoryBlocks[] = [
			'category' => $cat,
			'products' => $products,
			'url' => Category::getUrl($cat),
		];
	}

	$smarty->assign([
		'featuredProducts' => $featuredProducts,
		'categoryBlocks' => $categoryBlocks,
	]);