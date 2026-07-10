<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

$query = trim((string) Tools::getValue('q'));
$idCategory = max(0, (int) Tools::getValue('id_category'));
$page = max(1, (int) Tools::getValue('page'));
$limit = max(1, min(48, (int) Tools::getValue('limit', 24)));
$offset = ($page - 1) * $limit;

if ($query !== '' && Tools::strlen($query) >= 2) {
	$products = Product::search($query, $limit, $offset);
	$total = Product::countSearch($query);
} else {
	$products = Product::getActiveList($idCategory > 0 ? $idCategory : null, $limit, $offset, 'name_asc');
	$total = Product::countActive($idCategory > 0 ? $idCategory : null);
}

echo json_encode([
	'success' => true,
	'products' => $pos->formatProductsForPos($products),
	'pagination' => [
		'page' => $page,
		'limit' => $limit,
		'total' => $total,
		'pages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
	],
]);
