<?php
	if (!defined('IN_SCRIPT')) {
		header('HTTP/1.0 404 Not Found');
		header('Location: ../404');
		exit;
	}

	$cartSeo = Seo::resolvePage('cart', 'Sepetim', 'Alışveriş sepetiniz');
	$pageTitle = $cartSeo['title'];
	$pageDesc = $cartSeo['description'];

	$css = 'cart.css';
	$cart = Cart::getSummary();

	$smarty->assign([
		'cart' => $cart,
	]);
