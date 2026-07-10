<?php

namespace Shopier;

class ProductSyncService
{
	public static function ensureSchema(): void
	{
		$table = \DB::execute("SHOW TABLES LIKE 'shopier_products'");

		if (!empty($table)) {
			return;
		}

		\DB::execute(
			"CREATE TABLE `shopier_products` (
				`id_product` int(11) NOT NULL,
				`shopier_id` varchar(64) NOT NULL DEFAULT '',
				`shopier_url` varchar(512) NOT NULL DEFAULT '',
				`last_status` varchar(32) NOT NULL DEFAULT '',
				`last_error` text NULL,
				`last_sync_at` datetime NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				PRIMARY KEY (`id_product`),
				KEY `shopier_id` (`shopier_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);

		$map = \DB::execute("SHOW TABLES LIKE 'shopier_category_map'");

		if (empty($map)) {
			\DB::execute(
				"CREATE TABLE `shopier_category_map` (
					`id_category` int(11) NOT NULL,
					`shopier_category_id` varchar(64) NOT NULL DEFAULT '',
					`date_upd` datetime NOT NULL,
					PRIMARY KEY (`id_category`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	public static function isConfigured(): bool
	{
		return trim((string) \Settings::get('SHOPIER_API_TOKEN')) !== '';
	}

	public static function api(): ShopierApi
	{
		return new ShopierApi((string) \Settings::get('SHOPIER_API_TOKEN'));
	}

	/** @return array<string, mixed>|null */
	public static function findMapping(int $idProduct): ?array
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return null;
		}

		$row = \DB::getRowSafe('shopier_products', 'id_product = ?', [$idProduct]);

		return is_array($row) ? $row : null;
	}

	/**
	 * @return array{ok: bool, message: string, shopier_id?: string, shopier_url?: string, data?: mixed}
	 */
	public static function sync(int $idProduct): array
	{
		self::ensureSchema();

		if (!self::isConfigured()) {
			return ['ok' => false, 'message' => 'Shopier API anahtarı tanımlı değil'];
		}

		$product = \Product::getByIdAdmin($idProduct);

		if (!$product) {
			return ['ok' => false, 'message' => 'Ürün bulunamadı'];
		}

		$build = self::buildPayload($product);

		if (!$build['ok']) {
			return ['ok' => false, 'message' => $build['message']];
		}

		$payload = $build['payload'];
		$mapping = self::findMapping($idProduct);
		$api = self::api();
		$now = date('Y-m-d H:i:s');

		if ($mapping && trim((string) ($mapping['shopier_id'] ?? '')) !== '') {
			$shopierId = (string) $mapping['shopier_id'];
			$result = $api->updateProduct($shopierId, $payload);
		} else {
			$result = $api->createProduct($payload);
		}

		if (!$result['ok']) {
			self::saveMapping($idProduct, [
				'shopier_id' => (string) ($mapping['shopier_id'] ?? ''),
				'shopier_url' => (string) ($mapping['shopier_url'] ?? ''),
				'last_status' => 'failed',
				'last_error' => $result['message'],
				'last_sync_at' => $now,
			]);

			return ['ok' => false, 'message' => $result['message'], 'data' => $result['data'] ?? null];
		}

		$data = is_array($result['data'] ?? null) ? $result['data'] : [];
		$shopierId = (string) ($data['id'] ?? ($mapping['shopier_id'] ?? ''));
		$shopierUrl = (string) ($data['url'] ?? ($mapping['shopier_url'] ?? ''));

		self::saveMapping($idProduct, [
			'shopier_id' => $shopierId,
			'shopier_url' => $shopierUrl,
			'last_status' => 'synced',
			'last_error' => '',
			'last_sync_at' => $now,
		]);

		return [
			'ok' => true,
			'message' => $mapping && trim((string) ($mapping['shopier_id'] ?? '')) !== ''
				? 'Ürün Shopier\'de güncellendi'
				: 'Ürün Shopier\'e gönderildi',
			'shopier_id' => $shopierId,
			'shopier_url' => $shopierUrl,
			'data' => $data,
		];
	}

	/**
	 * @return array{ok: bool, message: string}
	 */
	public static function deleteFromShopier(int $idProduct, bool $clearMapping = true): array
	{
		self::ensureSchema();

		if (!self::isConfigured()) {
			return ['ok' => false, 'message' => 'Shopier API anahtarı tanımlı değil'];
		}

		$mapping = self::findMapping($idProduct);
		$shopierId = trim((string) ($mapping['shopier_id'] ?? ''));

		if ($shopierId === '') {
			return ['ok' => false, 'message' => 'Bu ürün Shopier ile eşleştirilmemiş'];
		}

		$result = self::api()->deleteProduct($shopierId);

		if (!$result['ok']) {
			return ['ok' => false, 'message' => $result['message']];
		}

		if ($clearMapping) {
			\DB::execute('DELETE FROM shopier_products WHERE id_product = ?', [$idProduct]);
		} else {
			self::saveMapping($idProduct, [
				'shopier_id' => '',
				'shopier_url' => '',
				'last_status' => 'deleted',
				'last_error' => '',
				'last_sync_at' => date('Y-m-d H:i:s'),
			]);
		}

		return ['ok' => true, 'message' => 'Ürün Shopier\'den silindi'];
	}

	/**
	 * @param array<string, mixed> $product
	 * @return array{ok: bool, message: string, payload?: array<string, mixed>}
	 */
	public static function buildPayload(array $product): array
	{
		$idProduct = (int) ($product['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return ['ok' => false, 'message' => 'Geçersiz ürün'];
		}

		$title = trim((string) ($product['product_name'] ?? ''));

		if ($title === '') {
			return ['ok' => false, 'message' => 'Ürün adı boş olamaz'];
		}

		$media = self::buildMedia($product);

		if ($media === []) {
			return ['ok' => false, 'message' => 'En az bir ürün görseli gerekli (Shopier için herkese açık URL)'];
		}

		$priceData = self::buildPriceData($product);
		$categoryId = self::resolveShopierCategoryId((int) ($product['id_category'] ?? 0));

		if ($categoryId === '') {
			$defaultCategory = trim((string) \Settings::get('SHOPIER_DEFAULT_CATEGORY_ID'));

			if ($defaultCategory === '') {
				return ['ok' => false, 'message' => 'Shopier kategori eşlemesi yok. Modül ayarlarından varsayılan kategori tanımlayın.'];
			}

			$categoryId = $defaultCategory;
		}

		$description = self::buildDescription($product);
		$stockQuantity = max(0, \Product::getStock($product));
		$cargoDay = (int) ($product['cargo_day'] ?? 0);
		$dispatchDuration = $cargoDay > 0 ? min(3, max(1, $cargoDay)) : max(1, (int) (\Settings::get('SHOPIER_DISPATCH_DURATION') ?: 1));

		$payload = [
			'title' => mb_substr($title, 0, 255),
			'description' => $description,
			'type' => self::resolveProductType($product),
			'media' => $media,
			'priceData' => $priceData,
			'stockQuantity' => $stockQuantity,
			'shippingPayer' => self::resolveShippingPayer(),
			'categories' => [
				['categoryId' => $categoryId],
			],
			'dispatchDuration' => min(3, max(1, $dispatchDuration)),
		];

		$placement = (int) (\Settings::get('SHOPIER_PLACEMENT_SCORE') ?: 0);

		if ($placement > 0) {
			$payload['placementScore'] = $placement;
		}

		return ['ok' => true, 'message' => 'OK', 'payload' => $payload];
	}

	/** @return array<int, array<string, mixed>> */
	private static function buildMedia(array $product): array
	{
		$idProduct = (int) ($product['id_product'] ?? 0);
		$images = \Product::getImages($idProduct);
		$media = [];
		$placement = 1;

		foreach ($images as $image) {
			if ($placement > 5) {
				break;
			}

			$url = self::absolutePublicUrl((string) ($image['url'] ?? ''));

			if ($url === '' || !self::isPublicImageUrl($url)) {
				continue;
			}

			$media[] = [
				'type' => 'image',
				'url' => $url,
				'placement' => $placement,
			];
			$placement++;
		}

		return $media;
	}

	/** @return array<string, mixed> */
	private static function buildPriceData(array $product): array
	{
		$currency = self::resolveCurrency($product);
		$price = (float) ($product['price'] ?? 0);
		$oldPrice = (float) ($product['old_price'] ?? 0);
		$hasDiscount = $oldPrice > $price && $price > 0;

		$data = [
			'currency' => $currency,
			'price' => self::formatMoney($hasDiscount ? $oldPrice : $price),
			'discount' => $hasDiscount,
		];

		if ($hasDiscount) {
			$data['discountedPrice'] = self::formatMoney($price);
		}

		$shippingPrice = trim((string) \Settings::get('SHOPIER_SHIPPING_PRICE'));

		if ($shippingPrice !== '') {
			$data['shippingPrice'] = $shippingPrice;
		}

		return $data;
	}

	private static function buildDescription(array $product): string
	{
		$parts = [];
		$short = trim(strip_tags((string) ($product['short_description'] ?? '')));
		$long = trim(strip_tags((string) ($product['description'] ?? '')));

		if ($short !== '') {
			$parts[] = $short;
		}

		if ($long !== '' && $long !== $short) {
			$parts[] = $long;
		}

		$text = trim(implode("\n\n", $parts));

		if ($text === '') {
			$text = (string) ($product['product_name'] ?? '');
		}

		return mb_substr($text, 0, 5000);
	}

	private static function resolveProductType(array $product): string
	{
		$default = \Settings::get('SHOPIER_PRODUCT_TYPE') ?: 'physical';

		if ($default === 'digital' || $default === 'physical') {
			if (\VirtualProduct::isVirtualProduct($product)) {
				return 'digital';
			}

			return $default === 'digital' ? 'digital' : 'physical';
		}

		return \VirtualProduct::isVirtualProduct($product) ? 'digital' : 'physical';
	}

	private static function resolveShippingPayer(): string
	{
		$value = \Settings::get('SHOPIER_SHIPPING_PAYER') ?: 'buyerPays';

		return $value === 'sellerPays' ? 'sellerPays' : 'buyerPays';
	}

	private static function resolveCurrency(array $product): string
	{
		$doviz = strtolower(trim((string) ($product['doviz'] ?? 'try')));

		if ($doviz === 'usd') {
			return 'USD';
		}

		if ($doviz === 'eur') {
			return 'EUR';
		}

		return 'TRY';
	}

	private static function resolveShopierCategoryId(int $idCategory): string
	{
		if ($idCategory <= 0) {
			return '';
		}

		self::ensureSchema();

		$row = \DB::getRowSafe('shopier_category_map', 'id_category = ?', [$idCategory]);

		return trim((string) ($row['shopier_category_id'] ?? ''));
	}

	public static function saveCategoryMap(int $idCategory, string $shopierCategoryId): void
	{
		self::ensureSchema();

		if ($idCategory <= 0) {
			return;
		}

		$shopierCategoryId = trim($shopierCategoryId);
		$now = date('Y-m-d H:i:s');

		if ($shopierCategoryId === '') {
			\DB::execute('DELETE FROM shopier_category_map WHERE id_category = ?', [$idCategory]);

			return;
		}

		$existing = \DB::getRowSafe('shopier_category_map', 'id_category = ?', [$idCategory]);

		if ($existing) {
			\DB::update('shopier_category_map', [
				'shopier_category_id' => $shopierCategoryId,
				'date_upd' => $now,
			], 'id_category = :where_id', ['where_id' => $idCategory]);
		} else {
			\DB::insert('shopier_category_map', [
				'id_category' => $idCategory,
				'shopier_category_id' => $shopierCategoryId,
				'date_upd' => $now,
			]);
		}
	}

	/** @return array<int, array<string, mixed>> */
	public static function getCategoryMaps(): array
	{
		self::ensureSchema();

		return \DB::execute('SELECT * FROM shopier_category_map ORDER BY id_category ASC') ?: [];
	}

	/** @return array<int, array<string, mixed>> */
	public static function getRecentSyncs(int $limit = 30): array
	{
		self::ensureSchema();

		$limit = max(1, min(100, $limit));

		return \DB::execute(
			'SELECT sp.*, p.product_name, p.active
			 FROM shopier_products sp
			 INNER JOIN products p ON p.id_product = sp.id_product
			 ORDER BY sp.last_sync_at DESC, sp.id_product DESC
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
			'shopier_id' => (string) ($fields['shopier_id'] ?? ''),
			'shopier_url' => (string) ($fields['shopier_url'] ?? ''),
			'last_status' => (string) ($fields['last_status'] ?? ''),
			'last_error' => (string) ($fields['last_error'] ?? ''),
			'last_sync_at' => (string) ($fields['last_sync_at'] ?? $now),
			'date_upd' => $now,
		];

		if ($existing) {
			\DB::update('shopier_products', $row, 'id_product = :where_id', ['where_id' => $idProduct]);
		} else {
			$row['id_product'] = $idProduct;
			$row['date_add'] = $now;
			\DB::insert('shopier_products', $row);
		}
	}

	private static function formatMoney(float $amount): string
	{
		return number_format(max(0, $amount), 2, '.', '');
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
