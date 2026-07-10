<?php

class DiscountTimerService
{
	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'discount_timer'");

		if (empty($table)) {
			DB::execute(
				"CREATE TABLE `discount_timer` (
					`id_product` int(11) NOT NULL,
					`starts_at` datetime NULL,
					`ends_at` datetime NULL,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY (`id_product`),
					KEY `starts_at` (`starts_at`),
					KEY `ends_at` (`ends_at`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);

			return;
		}

		$startsCol = DB::execute("SHOW COLUMNS FROM `discount_timer` LIKE 'starts_at'");

		if (empty($startsCol)) {
			DB::execute(
				"ALTER TABLE `discount_timer`
				 ADD COLUMN `starts_at` datetime NULL AFTER `id_product`"
			);
		}
	}

	/** @return array<string, mixed>|null */
	public static function getByProduct(int $idProduct): ?array
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return null;
		}

		$row = DB::getRowSafe('discount_timer', 'id_product = ?', [$idProduct]);

		return is_array($row) ? $row : null;
	}

	public static function saveSchedule(int $idProduct, ?string $startsAt, ?string $endsAt): void
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return;
		}

		$parsedStart = self::parseDateTime($startsAt);
		$parsedEnd = self::parseDateTime($endsAt);
		$existing = self::getByProduct($idProduct);
		$now = date('Y-m-d H:i:s');

		if ($parsedEnd === null) {
			if ($existing) {
				DB::execute('DELETE FROM discount_timer WHERE id_product = ?', [$idProduct]);
			}

			return;
		}

		if ($parsedStart !== null && strtotime($parsedStart) >= strtotime($parsedEnd)) {
			return;
		}

		$row = [
			'starts_at' => $parsedStart,
			'ends_at' => $parsedEnd,
			'date_upd' => $now,
		];

		if ($existing) {
			DB::update('discount_timer', $row, 'id_product = :where_id', ['where_id' => $idProduct]);
		} else {
			$row['id_product'] = $idProduct;
			$row['date_add'] = $now;
			DB::insert('discount_timer', $row);
		}
	}

	/**
	 * @param array<string, mixed> $timerRow
	 * @return 'none'|'pending'|'active'|'expired'
	 */
	public static function getWindowStatus(array $timerRow): string
	{
		$endsAt = trim((string) ($timerRow['ends_at'] ?? ''));

		if ($endsAt === '') {
			return 'none';
		}

		$now = time();
		$endsTs = strtotime($endsAt);

		if ($endsTs === false || $now >= $endsTs) {
			return 'expired';
		}

		$startsAt = trim((string) ($timerRow['starts_at'] ?? ''));

		if ($startsAt !== '') {
			$startsTs = strtotime($startsAt);

			if ($startsTs !== false && $now < $startsTs) {
				return 'pending';
			}
		}

		return 'active';
	}

	public static function expireDiscount(int $idProduct): bool
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return false;
		}

		$product = Product::getByIdAdmin($idProduct);

		if (!$product) {
			DB::execute('DELETE FROM discount_timer WHERE id_product = ?', [$idProduct]);

			return false;
		}

		$oldPrice = (float) ($product['old_price'] ?? 0);
		$price = (float) ($product['price'] ?? 0);
		$doviz = strtolower(trim((string) ($product['doviz'] ?? 'try')));
		$dovizOld = (float) ($product['doviz_old_price'] ?? 0);
		$dovizPrice = (float) ($product['doviz_price'] ?? 0);

		if ($oldPrice > $price && $oldPrice > 0) {
			$update = [
				'price' => $oldPrice,
				'old_price' => 0,
			];

			if ($doviz !== 'try' && $dovizOld > 0) {
				$update['doviz_price'] = $dovizOld;
				$update['doviz_old_price'] = 0;
			} elseif ($doviz !== 'try') {
				$update['doviz_price'] = $dovizPrice > 0 ? $dovizPrice : $oldPrice;
				$update['doviz_old_price'] = 0;
			} else {
				$update['doviz_price'] = $oldPrice;
				$update['doviz_old_price'] = 0;
			}

			DB::update('products', $update, 'id_product = :where_id', ['where_id' => $idProduct]);
		}

		DB::execute('DELETE FROM discount_timer WHERE id_product = ?', [$idProduct]);

		return true;
	}

	public static function processExpiredBatch(int $limit = 50): int
	{
		self::ensureSchema();

		$limit = max(1, min(200, $limit));

		$rows = DB::execute(
			'SELECT dt.id_product
			 FROM discount_timer dt
			 INNER JOIN products p ON p.id_product = dt.id_product
			 WHERE dt.ends_at IS NOT NULL
			   AND dt.ends_at <= NOW()
			   AND p.old_price > p.price
			 LIMIT ' . (int) $limit
		) ?: [];

		$count = 0;

		foreach ($rows as $row) {
			if (self::expireDiscount((int) ($row['id_product'] ?? 0))) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * @param array<string, mixed> $product
	 * @return array<string, mixed>
	 */
	public static function applyEffectivePricing(array $product): array
	{
		$idProduct = (int) ($product['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return $product;
		}

		$timer = self::getByProduct($idProduct);

		if (!$timer || trim((string) ($timer['ends_at'] ?? '')) === '') {
			return $product;
		}

		$status = self::getWindowStatus($timer);

		if ($status === 'expired') {
			self::expireDiscount($idProduct);
			$fresh = Product::getById($idProduct);

			return is_array($fresh) ? $fresh : self::refreshPriceFields($product);
		}

		$price = (float) ($product['price'] ?? 0);
		$oldPrice = (float) ($product['old_price'] ?? 0);

		if ($status === 'pending') {
			$regularPrice = $oldPrice > 0 ? $oldPrice : $price;
			$product['price'] = $regularPrice;
			$product['old_price'] = 0;

			return self::refreshPriceFields($product);
		}

		if ($status === 'active' && $oldPrice > $price && $price > 0) {
			return self::refreshPriceFields($product);
		}

		return self::refreshPriceFields($product);
	}

	/**
	 * @param array<string, mixed> $product
	 * @return array{active: bool, ends_at: string, ends_ts: int, starts_ts?: int, discount_pct: int}|null
	 */
	public static function getActiveForProduct(int $idProduct, array $product): ?array
	{
		if ($idProduct <= 0) {
			return null;
		}

		$product = self::applyEffectivePricing($product);

		$price = (float) ($product['price'] ?? 0);
		$oldPrice = (float) ($product['old_price'] ?? 0);

		if ($oldPrice <= $price || $price <= 0) {
			return null;
		}

		$timer = self::getByProduct($idProduct);

		if (!$timer || self::getWindowStatus($timer) !== 'active') {
			return null;
		}

		$endsTs = strtotime((string) $timer['ends_at']);

		if ($endsTs === false || $endsTs <= time()) {
			return null;
		}

		$result = [
			'active' => true,
			'ends_at' => (string) $timer['ends_at'],
			'ends_ts' => $endsTs,
			'discount_pct' => (int) Tools::getDiscount($oldPrice, $price),
		];

		if (!empty($timer['starts_at'])) {
			$startsTs = strtotime((string) $timer['starts_at']);

			if ($startsTs !== false) {
				$result['starts_ts'] = $startsTs;
			}
		}

		return $result;
	}

	/**
	 * @param array<int, array<string, mixed>> $products
	 * @return array<int, array<string, mixed>>
	 */
	public static function applyToProductList(array $products): array
	{
		foreach ($products as $index => $product) {
			if (is_array($product)) {
				$products[$index] = self::applyEffectivePricing($product);
			}
		}

		return $products;
	}

	/** @param Smarty\Smarty|null $smarty */
	public static function patchSmartyProductVars($smarty): void
	{
		if (!$smarty || !is_object($smarty)) {
			return;
		}

		self::processExpiredBatch(30);

		$keys = [
			'products',
			'featuredProducts',
			'dealProducts',
			'newProducts',
			'relatedProducts',
			'product',
		];

		foreach ($keys as $key) {
			$value = $smarty->getTemplateVars($key);

			if (!is_array($value) || $value === []) {
				continue;
			}

			if ($key === 'product' && isset($value['id_product'])) {
				$smarty->assign($key, self::applyEffectivePricing($value));

				if ($smarty->getTemplateVars('price') !== null) {
					$patched = self::applyEffectivePricing($value);
					$smarty->assign('price', (float) ($patched['price'] ?? 0));
					$smarty->assign('oldPrice', (float) ($patched['old_price'] ?? 0));
				}

				continue;
			}

			if (isset($value[0]) && is_array($value[0])) {
				$smarty->assign($key, self::applyToProductList($value));
			}
		}

		$categoryBlocks = $smarty->getTemplateVars('categoryBlocks');

		if (is_array($categoryBlocks) && $categoryBlocks !== []) {
			foreach ($categoryBlocks as $index => $block) {
				if (!is_array($block) || empty($block['products']) || !is_array($block['products'])) {
					continue;
				}

				$categoryBlocks[$index]['products'] = self::applyToProductList($block['products']);
			}

			$smarty->assign('categoryBlocks', $categoryBlocks);
		}
	}

	/** @param array<string, mixed> $product */
	private static function refreshPriceFields(array $product): array
	{
		$product['price'] = (float) ($product['price'] ?? 0);
		$product['old_price'] = (float) ($product['old_price'] ?? 0);
		$product['has_discount'] = $product['old_price'] > $product['price'] && $product['price'] > 0;
		$product['price_formatted'] = Tools::displayPrice($product['price']);
		$product['old_price_formatted'] = $product['has_discount']
			? Tools::displayPrice($product['old_price'])
			: '';

		return $product;
	}

	public static function parseDateTime(?string $value): ?string
	{
		$value = trim((string) $value);

		if ($value === '') {
			return null;
		}

		$value = str_replace('T', ' ', $value);

		if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $value)) {
			$value .= ':00';
		}

		$ts = strtotime($value);

		if ($ts === false) {
			return null;
		}

		return date('Y-m-d H:i:s', $ts);
	}

	public static function toInputValue(?string $datetime): string
	{
		if ($datetime === null || trim($datetime) === '') {
			return '';
		}

		$ts = strtotime($datetime);

		if ($ts === false) {
			return '';
		}

		return date('Y-m-d\TH:i', $ts);
	}

	public static function getTitle(): string
	{
		$title = trim((string) Settings::get('DISCOUNT_TIMER_TITLE'));

		return $title !== '' ? $title : 'Flaş İndirim';
	}

	public static function getSubtitle(): string
	{
		$subtitle = trim((string) Settings::get('DISCOUNT_TIMER_SUBTITLE'));

		return $subtitle !== '' ? $subtitle : 'Hemen al, fırsatı kaçırma';
	}

	public static function getPosition(): string
	{
		$pos = Settings::get('DISCOUNT_TIMER_POSITION');

		return $pos === 'inf' ? 'inf' : 'top';
	}
}
