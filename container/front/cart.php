<?php
	if (!defined('IN_SCRIPT')) {
		header('HTTP/1.0 404 Not Found');
		header('Location: ../404');
		exit;
	}

	$cartSeo = Seo::resolvePage('cart', translate('Cart page title'), translate('Cart page description'));
	$pageTitle = $cartSeo['title'];
	$pageDesc = $cartSeo['description'];

	$css = 'pages.css';
	$cart = Cart::getSummary();

	$categoryIds = [];
	$cartProductIds = [];

	foreach ($cart['items'] as $item) {
		$cartProductIds[] = (int) $item['id_product'];

		if (!empty($item['id_category'])) {
			$categoryIds[] = (int) $item['id_category'];
		}
	}

	$categoryIds = array_values(array_unique($categoryIds));
	$recommendedProducts = [];

	if ($categoryIds !== []) {
		$recommendedProducts = Product::getListInCategories($categoryIds, $cartProductIds, 12);
	}

	$favoriteProducts = Favorite::getList(null, 12, 0);

	$smarty->assign([
		'cart' => $cart,
		'recommendedProducts' => $recommendedProducts,
		'favoriteProducts' => $favoriteProducts,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Cart'), 'url' => ''],
		],
	]);
