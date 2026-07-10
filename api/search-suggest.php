<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$query = trim((string) Tools::getValue('q', Tools::getValue('query', '')));

if (mb_strlen($query) < 2) {
	echo json_encode(['success' => true, 'items' => []], JSON_UNESCAPED_UNICODE);
	exit;
}

$products = Product::search($query, 8, 0, 'newest');
$items = [];

foreach ($products as $p) {
	$items[] = [
		'id' => (int) ($p['id_product'] ?? 0),
		'name' => (string) ($p['product_name'] ?? ''),
		'url' => (string) ($p['url'] ?? ''),
		'image' => (string) ($p['image_url'] ?? ''),
		'price' => (string) ($p['price_formatted'] ?? ''),
		'category' => (string) ($p['category_name'] ?? ''),
	];
}

echo json_encode([
	'success' => true,
	'query' => $query,
	'items' => $items,
], JSON_UNESCAPED_UNICODE);
exit;
