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



$result = $pos->listProductsForPos($idCategory, $query, $page, $limit);

$total = (int) ($result['total'] ?? 0);



echo json_encode([

	'success' => true,

	'products' => $result['products'] ?? [],

	'pagination' => [

		'page' => $page,

		'limit' => $limit,

		'total' => $total,

		'pages' => $limit > 0 ? max(1, (int) ceil($total / $limit)) : 1,

	],

	'stock_rules' => [

		'hide_out_of_stock' => $pos->hideOutOfStock(),

		'allow_out_of_stock_sale' => $pos->allowOutOfStockSale(),

	],

]);

