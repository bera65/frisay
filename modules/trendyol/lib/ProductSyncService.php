<?php

namespace Trendyol;

class ProductSyncService
{
	public static function ensureSchema(): void
	{
		$products = \DB::execute("SHOW TABLES LIKE 'trendyol_products'");

		if (empty($products)) {
			\DB::execute(
				"CREATE TABLE `trendyol_products` (
					`id_product` int(11) NOT NULL,
					`barcode` varchar(64) NOT NULL DEFAULT '',
					`content_id` varchar(64) NOT NULL DEFAULT '',
					`product_url` varchar(512) NOT NULL DEFAULT '',
					`brand_id` int(11) NOT NULL DEFAULT 0,
					`category_id` int(11) NOT NULL DEFAULT 0,
					`attributes_json` text NULL,
					`sale_price` decimal(20,2) NOT NULL DEFAULT 0.00,
					`list_price` decimal(20,2) NOT NULL DEFAULT 0.00,
					`quantity` int(11) NOT NULL DEFAULT 0,
					`approved` tinyint(1) NOT NULL DEFAULT 0,
					`batch_request_id` varchar(128) NOT NULL DEFAULT '',
					`last_status` varchar(32) NOT NULL DEFAULT '',
					`last_error` text NULL,
					`last_sync_at` datetime NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY (`id_product`),
					KEY `barcode` (`barcode`),
					KEY `content_id` (`content_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		$map = \DB::execute("SHOW TABLES LIKE 'trendyol_category_map'");

		if (empty($map)) {
			\DB::execute(
				"CREATE TABLE `trendyol_category_map` (
					`id_category` int(11) NOT NULL,
					`trendyol_category_id` int(11) NOT NULL DEFAULT 0,
					`trendyol_category_name` varchar(512) NOT NULL DEFAULT '',
					`attributes_json` text NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY (`id_category`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		} else {
			$col = \DB::execute("SHOW COLUMNS FROM `trendyol_category_map` LIKE 'trendyol_category_name'");

			if (empty($col)) {
				\DB::execute(
					"ALTER TABLE `trendyol_category_map`
					 ADD COLUMN `trendyol_category_name` varchar(512) NOT NULL DEFAULT '' AFTER `trendyol_category_id`"
				);
			}
		}

		$orders = \DB::execute("SHOW TABLES LIKE 'trendyol_orders'");

		if (empty($orders)) {
			\DB::execute(
				"CREATE TABLE `trendyol_orders` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`order_number` varchar(64) NOT NULL,
					`shipment_package_id` varchar(64) NOT NULL DEFAULT '',
					`status` varchar(64) NOT NULL DEFAULT '',
					`customer_name` varchar(255) NOT NULL DEFAULT '',
					`total_price` decimal(20,2) NOT NULL DEFAULT 0.00,
					`cargo_tracking_number` varchar(128) NOT NULL DEFAULT '',
					`cargo_provider` varchar(128) NOT NULL DEFAULT '',
					`lines_json` mediumtext NULL,
					`raw_json` mediumtext NULL,
					`stock_deducted` tinyint(1) NOT NULL DEFAULT 0,
					`order_date` datetime NULL,
					`last_sync_at` datetime NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `order_package` (`order_number`, `shipment_package_id`),
					KEY `status` (`status`),
					KEY `order_date` (`order_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		} else {
			$col = \DB::execute("SHOW COLUMNS FROM `trendyol_orders` LIKE 'stock_deducted'");

			if (empty($col)) {
				\DB::execute(
					"ALTER TABLE `trendyol_orders`
					 ADD COLUMN `stock_deducted` tinyint(1) NOT NULL DEFAULT 0 AFTER `raw_json`"
				);
			}
		}

		$questions = \DB::execute("SHOW TABLES LIKE 'trendyol_questions'");

		if (empty($questions)) {
			\DB::execute(
				"CREATE TABLE `trendyol_questions` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`question_id` bigint(20) NOT NULL,
					`product_name` varchar(255) NOT NULL DEFAULT '',
					`barcode` varchar(64) NOT NULL DEFAULT '',
					`question_text` text NULL,
					`answer_text` text NULL,
					`status` varchar(64) NOT NULL DEFAULT '',
					`answered` tinyint(1) NOT NULL DEFAULT 0,
					`customer_id` varchar(64) NOT NULL DEFAULT '',
					`raw_json` mediumtext NULL,
					`question_date` datetime NULL,
					`last_sync_at` datetime NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `question_id` (`question_id`),
					KEY `answered` (`answered`),
					KEY `status` (`status`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	public static function isConfigured(): bool
	{
		return self::api()->isConfigured();
	}

	public static function api(): TrendyolApi
	{
		return new TrendyolApi(
			(string) \Settings::get('TRENDYOL_MERCHANT_ID'),
			(string) \Settings::get('TRENDYOL_API_KEY'),
			(string) \Settings::get('TRENDYOL_API_SECRET')
		);
	}

	/** @return array<string, mixed>|null */
	public static function findMapping(int $idProduct): ?array
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return null;
		}

		$row = \DB::getRowSafe('trendyol_products', 'id_product = ?', [$idProduct]);

		return is_array($row) ? $row : null;
	}

	/**
	 * @param array<string, mixed> $meta brand_id, category_id, attributes (map)
	 * @return array{ok: bool, message: string, mapping?: array<string, mixed>, data?: mixed}
	 */
	public static function sync(int $idProduct, array $meta = []): array
	{
		self::ensureSchema();

		if (!self::isConfigured()) {
			return ['ok' => false, 'message' => 'Trendyol API kimlik bilgileri tanımlı değil'];
		}

		$product = \Product::getByIdAdmin($idProduct);

		if (!$product) {
			return ['ok' => false, 'message' => 'Ürün bulunamadı'];
		}

		$existing = self::findMapping($idProduct) ?: [];
		$meta = self::mergeMeta($product, $existing, $meta);
		$build = self::buildPayload($product, $meta);

		if (!$build['ok']) {
			return ['ok' => false, 'message' => $build['message']];
		}

		$payload = ['items' => [$build['payload']]];
		$api = self::api();
		$isUpdate = trim((string) ($existing['content_id'] ?? '')) !== ''
			|| trim((string) ($existing['last_status'] ?? '')) === 'synced';

		if ($isUpdate) {
			$result = $api->updateProduct($payload);
		} else {
			$result = $api->createProduct($payload);
		}

		$now = date('Y-m-d H:i:s');

		if (self::isApiError($result)) {
			self::saveMapping($idProduct, [
				'barcode' => (string) ($build['payload']['barcode'] ?? ''),
				'brand_id' => (int) ($meta['brand_id'] ?? 0),
				'category_id' => (int) ($meta['category_id'] ?? 0),
				'attributes_json' => json_encode($meta['attributes'] ?? [], JSON_UNESCAPED_UNICODE),
				'sale_price' => (float) ($build['payload']['salePrice'] ?? 0),
				'list_price' => (float) ($build['payload']['listPrice'] ?? 0),
				'quantity' => (int) ($build['payload']['quantity'] ?? 0),
				'last_status' => 'failed',
				'last_error' => (string) ($result['message'] ?? 'Trendyol API hatası'),
				'last_sync_at' => $now,
			]);

			return [
				'ok' => false,
				'message' => (string) ($result['message'] ?? 'Trendyol API hatası'),
				'data' => $result,
			];
		}

		$batchId = (string) ($result['batchRequestId'] ?? '');
		$mappingFields = [
			'barcode' => (string) ($build['payload']['barcode'] ?? ''),
			'brand_id' => (int) ($meta['brand_id'] ?? 0),
			'category_id' => (int) ($meta['category_id'] ?? 0),
			'attributes_json' => json_encode($meta['attributes'] ?? [], JSON_UNESCAPED_UNICODE),
			'sale_price' => (float) ($build['payload']['salePrice'] ?? 0),
			'list_price' => (float) ($build['payload']['listPrice'] ?? 0),
			'quantity' => (int) ($build['payload']['quantity'] ?? 0),
			'batch_request_id' => $batchId,
			'last_status' => 'synced',
			'last_error' => '',
			'last_sync_at' => $now,
		];

		self::saveMapping($idProduct, $mappingFields);
		self::refreshFromTrendyol($idProduct);

		return [
			'ok' => true,
			'message' => $isUpdate ? 'Ürün Trendyol\'da güncellendi' : 'Ürün Trendyol\'a aktarıldı',
			'mapping' => self::findMapping($idProduct),
			'data' => $result,
		];
	}

	/**
	 * @return array{ok: bool, message: string, mapping?: array<string, mixed>}
	 */
	public static function updatePriceStock(int $idProduct, ?float $saleOverride = null, ?float $listOverride = null): array
	{
		self::ensureSchema();

		if (!self::isConfigured()) {
			return ['ok' => false, 'message' => 'Trendyol API kimlik bilgileri tanımlı değil'];
		}

		$product = \Product::getByIdAdmin($idProduct);
		$mapping = self::findMapping($idProduct);

		if (!$product) {
			return ['ok' => false, 'message' => 'Ürün bulunamadı'];
		}

		$barcode = trim((string) ($mapping['barcode'] ?? ($product['barcode'] ?? '')));

		if ($barcode === '') {
			return ['ok' => false, 'message' => 'Barkod gerekli'];
		}

		$salePrice = null;
		$listPrice = null;

		if ($saleOverride !== null && $saleOverride > 0) {
			$salePrice = $saleOverride;
		} elseif ($mapping && (float) ($mapping['sale_price'] ?? 0) > 0) {
			$salePrice = (float) $mapping['sale_price'];
		}

		if ($listOverride !== null && $listOverride > 0) {
			$listPrice = $listOverride;
		} elseif ($mapping && (float) ($mapping['list_price'] ?? 0) > 0) {
			$listPrice = (float) $mapping['list_price'];
		}

		if ($salePrice === null || $salePrice <= 0) {
			$salePrice = (float) ($product['price'] ?? 0);
		}

		if ($listPrice === null || $listPrice <= 0) {
			$listPrice = (float) ($product['old_price'] ?? 0);
		}

		if ($listPrice <= $salePrice) {
			$listPrice = $salePrice;
		}

		if ($salePrice <= 0) {
			return ['ok' => false, 'message' => 'Trendyol satış fiyatı 0 olamaz'];
		}

		$stock = max(0, \Product::getStock($product));
		$sku = trim((string) ($product['stock_code'] ?? ''));

		$result = self::api()->updateStockPrice($barcode, $listPrice, $salePrice, $stock, $sku !== '' ? $sku : null);
		$now = date('Y-m-d H:i:s');

		if (self::isApiError($result)) {
			self::saveMapping($idProduct, [
				'barcode' => $barcode,
				'sale_price' => $salePrice,
				'list_price' => $listPrice,
				'quantity' => $stock,
				'last_status' => 'failed',
				'last_error' => (string) ($result['message'] ?? 'Fiyat güncelleme hatası'),
				'last_sync_at' => $now,
			]);

			return [
				'ok' => false,
				'message' => (string) ($result['message'] ?? 'Fiyat güncelleme hatası'),
			];
		}

		$batchId = (string) ($result['batchRequestId'] ?? ($mapping['batch_request_id'] ?? ''));

		self::saveMapping($idProduct, [
			'barcode' => $barcode,
			'sale_price' => $salePrice,
			'list_price' => $listPrice,
			'quantity' => $stock,
			'batch_request_id' => $batchId,
			'last_status' => 'synced',
			'last_error' => '',
			'last_sync_at' => $now,
		]);

		self::refreshFromTrendyol($idProduct);

		return [
			'ok' => true,
			'message' => 'Trendyol fiyat/stok güncellendi',
			'mapping' => self::findMapping($idProduct),
		];
	}

	/**
	 * @return array{ok: bool, message: string, mapping?: array<string, mixed>}
	 */
	public static function refreshFromTrendyol(int $idProduct): array
	{
		self::ensureSchema();

		$mapping = self::findMapping($idProduct);
		$product = \Product::getByIdAdmin($idProduct);
		$barcode = trim((string) ($mapping['barcode'] ?? ($product['barcode'] ?? '')));

		if ($barcode === '') {
			return ['ok' => false, 'message' => 'Barkod yok'];
		}

		$result = self::api()->getProduct($barcode);

		if (self::isApiError($result)) {
			return ['ok' => false, 'message' => (string) ($result['message'] ?? 'Ürün bilgisi alınamadı')];
		}

		$content = null;

		if (isset($result['content']) && is_array($result['content']) && isset($result['content'][0])) {
			$content = $result['content'][0];
		} elseif (isset($result[0]) && is_array($result[0])) {
			$content = $result[0];
		}

		if (!is_array($content)) {
			return ['ok' => false, 'message' => 'Trendyol ürün kaydı henüz görünmüyor'];
		}

		self::saveMapping($idProduct, [
			'barcode' => (string) ($content['barcode'] ?? $barcode),
			'content_id' => (string) ($content['contentId'] ?? ($content['id'] ?? ($mapping['content_id'] ?? ''))),
			'product_url' => (string) ($content['productUrl'] ?? ($content['url'] ?? ($mapping['product_url'] ?? ''))),
			'brand_id' => (int) ($content['brandId'] ?? ($mapping['brand_id'] ?? 0)),
			'category_id' => (int) ($content['pimCategoryId'] ?? ($content['categoryId'] ?? ($mapping['category_id'] ?? 0))),
			'sale_price' => (float) ($content['salePrice'] ?? ($mapping['sale_price'] ?? 0)),
			'list_price' => (float) ($content['listPrice'] ?? ($mapping['list_price'] ?? 0)),
			'quantity' => (int) ($content['quantity'] ?? ($mapping['quantity'] ?? 0)),
			'approved' => !empty($content['approved']) ? 1 : 0,
			'last_status' => 'synced',
			'last_error' => '',
			'last_sync_at' => date('Y-m-d H:i:s'),
		]);

		return [
			'ok' => true,
			'message' => 'Trendyol ürün bilgisi yenilendi',
			'mapping' => self::findMapping($idProduct),
		];
	}

	/**
	 * @param array<string, mixed> $product
	 * @param array<string, mixed> $existing
	 * @param array<string, mixed> $meta
	 * @return array{brand_id: int, category_id: int, attributes: array<string, mixed>, sale_price: ?float, list_price: ?float}
	 */
	private static function mergeMeta(array $product, array $existing, array $meta): array
	{
		$brandId = (int) ($meta['brand_id'] ?? 0);

		if ($brandId <= 0) {
			$brandId = (int) ($existing['brand_id'] ?? 0);
		}

		if ($brandId <= 0) {
			$brandId = (int) (\Settings::get('TRENDYOL_DEFAULT_BRAND_ID') ?: 0);
		}

		$categoryId = (int) ($meta['category_id'] ?? 0);

		if ($categoryId <= 0) {
			$categoryId = (int) ($existing['category_id'] ?? 0);
		}

		if ($categoryId <= 0) {
			$categoryId = self::resolveCategoryId((int) ($product['id_category'] ?? 0));
		}

		$attributes = $meta['attributes'] ?? null;

		if (!is_array($attributes) || $attributes === []) {
			$decoded = json_decode((string) ($existing['attributes_json'] ?? ''), true);
			$attributes = is_array($decoded) ? $decoded : [];
		}

		if ($attributes === [] && $categoryId > 0) {
			$map = \DB::getRowSafe('trendyol_category_map', 'id_category = ?', [(int) ($product['id_category'] ?? 0)]);
			$decoded = json_decode((string) ($map['attributes_json'] ?? ''), true);

			if (is_array($decoded)) {
				$attributes = $decoded;
			}
		}

		$salePrice = null;
		$listPrice = null;

		if (isset($meta['sale_price']) && $meta['sale_price'] !== '' && $meta['sale_price'] !== null) {
			$salePrice = (float) $meta['sale_price'];
		} elseif (isset($existing['sale_price']) && (float) $existing['sale_price'] > 0) {
			$salePrice = (float) $existing['sale_price'];
		}

		if (isset($meta['list_price']) && $meta['list_price'] !== '' && $meta['list_price'] !== null) {
			$listPrice = (float) $meta['list_price'];
		} elseif (isset($existing['list_price']) && (float) $existing['list_price'] > 0) {
			$listPrice = (float) $existing['list_price'];
		}

		return [
			'brand_id' => $brandId,
			'category_id' => $categoryId,
			'attributes' => $attributes,
			'sale_price' => $salePrice,
			'list_price' => $listPrice,
		];
	}

	/**
	 * @param array<string, mixed> $product
	 * @param array{brand_id: int, category_id: int, attributes: array<string, mixed>} $meta
	 * @return array{ok: bool, message: string, payload?: array<string, mixed>}
	 */
	public static function buildPayload(array $product, array $meta): array
	{
		$idProduct = (int) ($product['id_product'] ?? 0);
		$title = trim((string) ($product['product_name'] ?? ''));
		$barcode = trim((string) ($product['barcode'] ?? ''));

		if ($idProduct <= 0) {
			return ['ok' => false, 'message' => 'Geçersiz ürün'];
		}

		if ($title === '') {
			return ['ok' => false, 'message' => 'Ürün adı boş olamaz'];
		}

		if ($barcode === '') {
			$barcode = 'FS' . $idProduct;
		}

		$barcode = preg_replace('/\s+/', '', $barcode) ?: $barcode;

		if ((int) ($meta['brand_id'] ?? 0) <= 0) {
			return ['ok' => false, 'message' => 'Trendyol marka ID gerekli (modül ayarı veya ürün paneli)'];
		}

		if ((int) ($meta['category_id'] ?? 0) <= 0) {
			return ['ok' => false, 'message' => 'Trendyol kategori ID gerekli (kategori eşlemesi veya ürün paneli)'];
		}

		$images = self::buildImages($product);

		if ($images === []) {
			return ['ok' => false, 'message' => 'En az bir herkese açık ürün görseli gerekli (localhost URL kabul edilmez)'];
		}

		$salePrice = isset($meta['sale_price']) && $meta['sale_price'] !== null
			? (float) $meta['sale_price']
			: (float) ($product['price'] ?? 0);
		$listPrice = isset($meta['list_price']) && $meta['list_price'] !== null
			? (float) $meta['list_price']
			: (float) ($product['old_price'] ?? 0);

		if ($listPrice <= $salePrice) {
			$listPrice = $salePrice;
		}

		if ($salePrice <= 0) {
			return ['ok' => false, 'message' => 'Trendyol satış fiyatı 0 olamaz'];
		}

		$stockCode = trim((string) ($product['stock_code'] ?? ''));

		if ($stockCode === '') {
			$stockCode = 'SKU-' . $idProduct;
		}

		$vat = (int) round((float) ($product['vat'] ?? 20));
		$allowedVat = [0, 1, 10, 20];

		if (!in_array($vat, $allowedVat, true)) {
			$vat = 20;
		}

		$desi = max(1, (int) ($product['desi'] ?? 1));
		$cargoDay = (int) ($product['cargo_day'] ?? 0);
		$deliveryDuration = $cargoDay > 0 ? min(3, max(1, $cargoDay)) : max(1, (int) (\Settings::get('TRENDYOL_DELIVERY_DURATION') ?: 1));

		$attributes = self::api()->convertAttributes($meta['attributes'] ?? []);

		if ($attributes === []) {
			return ['ok' => false, 'message' => 'Trendyol kategori özellikleri (attributes) gerekli'];
		}

		$description = self::buildDescription($product);
		$payload = [
			'barcode' => mb_substr($barcode, 0, 40),
			'title' => mb_substr($title, 0, 100),
			'productMainId' => mb_substr('PM-' . $idProduct, 0, 40),
			'brandId' => (int) $meta['brand_id'],
			'categoryId' => (int) $meta['category_id'],
			'quantity' => max(0, \Product::getStock($product)),
			'stockCode' => mb_substr($stockCode, 0, 100),
			'dimensionalWeight' => $desi,
			'description' => $description,
			'currencyType' => 'TRY',
			'listPrice' => round($listPrice, 2),
			'salePrice' => round($salePrice, 2),
			'vatRate' => $vat,
			'images' => $images,
			'attributes' => $attributes,
			'deliveryOption' => [
				'deliveryDuration' => $deliveryDuration,
			],
		];

		$cargoCompanyId = (int) (\Settings::get('TRENDYOL_CARGO_COMPANY_ID') ?: 0);

		if ($cargoCompanyId > 0) {
			$payload['cargoCompanyId'] = $cargoCompanyId;
		}

		$shipmentAddressId = (int) (\Settings::get('TRENDYOL_SHIPMENT_ADDRESS_ID') ?: 0);

		if ($shipmentAddressId > 0) {
			$payload['shipmentAddressId'] = $shipmentAddressId;
		}

		$returningAddressId = (int) (\Settings::get('TRENDYOL_RETURNING_ADDRESS_ID') ?: 0);

		if ($returningAddressId > 0) {
			$payload['returningAddressId'] = $returningAddressId;
		}

		return ['ok' => true, 'message' => 'OK', 'payload' => $payload];
	}

	/** @return array<int, array{url: string}> */
	private static function buildImages(array $product): array
	{
		$idProduct = (int) ($product['id_product'] ?? 0);
		$images = \Product::getImages($idProduct);
		$out = [];

		foreach ($images as $image) {
			if (count($out) >= 8) {
				break;
			}

			$url = self::absolutePublicUrl((string) ($image['url'] ?? ''));

			if ($url === '' || !self::isPublicImageUrl($url)) {
				continue;
			}

			$out[] = ['url' => $url];
		}

		return $out;
	}

	private static function buildDescription(array $product): string
	{
		$long = trim((string) ($product['description'] ?? ''));
		$short = trim((string) ($product['short_description'] ?? ''));

		if ($long !== '') {
			$text = $long;
		} elseif ($short !== '') {
			$text = $short;
		} else {
			$text = (string) ($product['product_name'] ?? '');
		}

		$text = preg_replace('/\s+/u', ' ', strip_tags($text)) ?: $text;

		return mb_substr(trim($text), 0, 30000);
	}

	private static function resolveCategoryId(int $idCategory): int
	{
		if ($idCategory <= 0) {
			return (int) (\Settings::get('TRENDYOL_DEFAULT_CATEGORY_ID') ?: 0);
		}

		self::ensureSchema();
		$row = \DB::getRowSafe('trendyol_category_map', 'id_category = ?', [$idCategory]);
		$mapped = (int) ($row['trendyol_category_id'] ?? 0);

		if ($mapped > 0) {
			return $mapped;
		}

		return (int) (\Settings::get('TRENDYOL_DEFAULT_CATEGORY_ID') ?: 0);
	}

	public static function saveCategoryMap(
		int $idCategory,
		int $trendyolCategoryId,
		string $attributesJson = '',
		string $trendyolCategoryName = ''
	): void {
		self::ensureSchema();

		if ($idCategory <= 0) {
			return;
		}

		$now = date('Y-m-d H:i:s');
		$existing = \DB::getRowSafe('trendyol_category_map', 'id_category = ?', [$idCategory]);
		$row = [
			'trendyol_category_id' => max(0, $trendyolCategoryId),
			'trendyol_category_name' => mb_substr($trendyolCategoryName, 0, 512),
			'attributes_json' => $attributesJson,
			'date_upd' => $now,
		];

		if ($existing) {
			if ($trendyolCategoryName === '' && !empty($existing['trendyol_category_name'])) {
				$row['trendyol_category_name'] = (string) $existing['trendyol_category_name'];
			}

			\DB::update('trendyol_category_map', $row, 'id_category = :where_id', ['where_id' => $idCategory]);
		} else {
			$row['id_category'] = $idCategory;
			\DB::insert('trendyol_category_map', $row);
		}
	}

	/** @return array<int, array<string, mixed>> */
	public static function getCategoryMaps(): array
	{
		self::ensureSchema();

		return \DB::execute('SELECT * FROM trendyol_category_map ORDER BY id_category ASC') ?: [];
	}

	/** @return array<int, array<string, mixed>> */
	public static function getRecentSyncs(int $limit = 40): array
	{
		self::ensureSchema();
		$limit = max(1, min(100, $limit));

		return \DB::execute(
			'SELECT tp.*, p.product_name, p.active
			 FROM trendyol_products tp
			 INNER JOIN products p ON p.id_product = tp.id_product
			 ORDER BY tp.last_sync_at DESC, tp.id_product DESC
			 LIMIT ' . (int) $limit
		) ?: [];
	}

	/** @param array<string, mixed> $fields */
	private static function saveMapping(int $idProduct, array $fields): void
	{
		self::ensureSchema();

		$now = date('Y-m-d H:i:s');
		$existing = self::findMapping($idProduct);

		$row = [
			'barcode' => (string) ($fields['barcode'] ?? ($existing['barcode'] ?? '')),
			'content_id' => (string) ($fields['content_id'] ?? ($existing['content_id'] ?? '')),
			'product_url' => (string) ($fields['product_url'] ?? ($existing['product_url'] ?? '')),
			'brand_id' => (int) ($fields['brand_id'] ?? ($existing['brand_id'] ?? 0)),
			'category_id' => (int) ($fields['category_id'] ?? ($existing['category_id'] ?? 0)),
			'attributes_json' => (string) ($fields['attributes_json'] ?? ($existing['attributes_json'] ?? '')),
			'sale_price' => (float) ($fields['sale_price'] ?? ($existing['sale_price'] ?? 0)),
			'list_price' => (float) ($fields['list_price'] ?? ($existing['list_price'] ?? 0)),
			'quantity' => (int) ($fields['quantity'] ?? ($existing['quantity'] ?? 0)),
			'approved' => (int) ($fields['approved'] ?? ($existing['approved'] ?? 0)),
			'batch_request_id' => (string) ($fields['batch_request_id'] ?? ($existing['batch_request_id'] ?? '')),
			'last_status' => (string) ($fields['last_status'] ?? ($existing['last_status'] ?? '')),
			'last_error' => (string) ($fields['last_error'] ?? ''),
			'last_sync_at' => (string) ($fields['last_sync_at'] ?? $now),
			'date_upd' => $now,
		];

		if ($existing) {
			\DB::update('trendyol_products', $row, 'id_product = :where_id', ['where_id' => $idProduct]);
		} else {
			$row['id_product'] = $idProduct;
			$row['date_add'] = $now;
			\DB::insert('trendyol_products', $row);
		}
	}

	/** @param mixed $result */
	public static function isApiError($result): bool
	{
		if ($result === null) {
			return true;
		}

		if (!is_array($result)) {
			return true;
		}

		if (array_key_exists('success', $result) && $result['success'] === false) {
			return true;
		}

		return false;
	}

	private static function absolutePublicUrl(string $url): string
	{
		$url = trim($url);

		if ($url === '') {
			return '';
		}

		if (preg_match('~^https?://~i', $url)) {
			return $url;
		}

		global $domain;

		return rtrim((string) $domain, '/') . '/' . ltrim($url, '/');
	}

	private static function isPublicImageUrl(string $url): bool
	{
		if (!preg_match('~^https?://~i', $url)) {
			return false;
		}

		$host = parse_url($url, PHP_URL_HOST);

		if (!is_string($host) || $host === '') {
			return false;
		}

		$localHosts = ['localhost', '127.0.0.1', '::1'];

		return !in_array(strtolower($host), $localHosts, true);
	}
}
