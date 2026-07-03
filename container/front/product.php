<?php
	if (!defined('IN_SCRIPT')) {
		header("HTTP/1.0 404 Not Found");
		header('Location: ../404');
		exit;
	}

	$js = 'product.js';
	$css = 'product.css';
	$idProduct = (int) Tools::getValue('id');
	$product = Product::getById($idProduct);

	if (!$product) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', translate('Page Not Found'));
		return;
	}

	$images = Product::getImages($idProduct);

	if (!$images) {
		$images = [[
			'id_image' => 0,
			'url' => $product['image_url'],
			'cover' => 1,
		]];
	}

	$pageTitle = trim((string) ($product['meta_title'] ?? ''));
	if ($pageTitle === '') {
		$pageTitle = $product['product_name'];
	}

	$pageDesc = trim((string) ($product['meta_description'] ?? ''));
	if ($pageDesc === '') {
		$metaSource = trim((string) ($product['short_description'] ?? ''));
		if ($metaSource === '') {
			$metaSource = strip_tags((string) $product['description']);
		}
		$pageDesc = Tools::strlen($metaSource) > 160
			? mb_substr($metaSource, 0, 157, 'UTF-8') . '...'
			: $metaSource;
	}

	$globalCargoDay = max(0, (int) Settings::get('CARGO_DAY'));
	$productCargoDay = max(0, (int) ($product['cargo_day'] ?? 0));
	$variationData = ProductVariation::getForStorefront($idProduct, (float) $product['price']);
	$optionData = ProductOption::getForStorefront($idProduct);

	$relatedProducts = [];
	$idBrand = (int) ($product['id_brand'] ?? 0);
	if ($idBrand > 0) {
		$relatedRaw = Product::getActiveList(null, 8, 0, 'newest', $idBrand);
		foreach ($relatedRaw as $relatedItem) {
			if ((int) ($relatedItem['id_product'] ?? 0) === $idProduct) {
				continue;
			}
			$relatedProducts[] = $relatedItem;
			if (count($relatedProducts) >= 5) {
				break;
			}
		}
	}

	$smarty->assign([
		'product' 			=> $product,
		'productUrl'		=> Product::getLink($product),
		'productName' 		=> $product['product_name'],
		'brandName' 		=> $product['brand_name'],
		'brandUrl' 			=> Brand::getUrl(['brand_link' => $product['brand_link']]),
		'price' 			=> (float) $product['price'],
		'oldPrice' 			=> (float) $product['old_price'],
		'shortDescription' 	=> trim((string) ($product['short_description'] ?? '')),
		'description' 		=> $product['description'],
		'imageUrl' 			=> $images[0]['url'],
		'images' 			=> $images,
		'inStock' 			=> $product['in_stock'],
		'stock' 			=> (int) $product['stock'],
		'stockCode' 		=> $product['stock_code'],
		'productVideoEmbed' => Product::getYoutubeEmbedUrl((string) ($product['product_video'] ?? '')),
		'productLabel' 		=> trim((string) ($product['label'] ?? '')),
		'cargoDay'			=> $productCargoDay > 0 ? $productCargoDay : $globalCargoDay,
		'freeCargo' 		=> (float)Settings::get('FREE_SHIPPING_MIN'),
		'cargoPrice' 		=> (float)Settings::get('SHIPPING_FEE'),
		'havale'			=> (float)Settings::get('HAVALE'),
		'isFavorite' 		=> Favorite::isFavorite($idProduct),
		'relatedProducts'	=> $relatedProducts,
		'hasVariations'     => !empty($variationData['has_variations']),
		'variationGroups'   => $variationData['groups'],
		'variationItemsJson' => json_encode($variationData['items'], JSON_UNESCAPED_UNICODE),
		'hasOptions'        => !empty($optionData['has_options']),
		'optionGroups'      => $optionData['groups'],
		'requiredOptionGroups' => count(array_filter($optionData['groups'], static function (array $group): bool {
			return !empty($group['required']);
		})),
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => $product['category_name'], 'url' => $domain . $product['category_link']],
			['name' => $product['product_name'], 'url' => ''],
		],
		'schemaJsonLd' => SchemaOrg::getProductScripts(
			$product,
			$images,
			[
				['name' => translate('Home Page'), 'url' => rtrim($domain, '/') . '/'],
				['name' => $product['category_name'], 'url' => $domain . $product['category_link']],
				['name' => $product['product_name'], 'url' => Product::getLink($product)],
			],
			$pageTitle,
			$pageDesc,
			(float) (Settings::get('FREE_SHIPPING_MIN') ?: 0),
			(float) (Settings::get('SHIPPING_FEE') ?: 0)
		),
	]);

	Module::refreshHook($smarty, 'product', ['id_product' => $idProduct]);
	Module::refreshHook($smarty, 'product_tab', ['id_product' => $idProduct]);
	Module::refreshHook($smarty, 'product_tab_content', ['id_product' => $idProduct]);
	Module::refreshHook($smarty, 'product_inf', ['id_product' => $idProduct]);
