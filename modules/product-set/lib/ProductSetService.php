<?php

class ProductSetService
{
	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'product_set_items'");

		if (!empty($table)) {
			return;
		}

		DB::execute(
			"CREATE TABLE IF NOT EXISTS `product_set_items` (
				`id_set_item` int(11) NOT NULL AUTO_INCREMENT,
				`id_set_product` int(11) NOT NULL,
				`id_product` int(11) NOT NULL,
				`qty` int(11) NOT NULL DEFAULT 1,
				`position` int(11) NOT NULL DEFAULT 0,
				PRIMARY KEY (`id_set_item`),
				UNIQUE KEY `set_child` (`id_set_product`, `id_product`),
				KEY `id_set_product` (`id_set_product`),
				KEY `id_product` (`id_product`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);
	}

	/** @return array<int, array<string, mixed>> */
	public static function getItems(int $idSetProduct): array
	{
		self::ensureSchema();

		if ($idSetProduct <= 0) {
			return [];
		}

		$rows = DB::execute(
			'SELECT i.*, p.product_name, p.price, p.stock, p.active, p.product_type, p.product_link, c.category_link, img.id_image
			FROM product_set_items i
			INNER JOIN products p ON p.id_product = i.id_product
			LEFT JOIN categories c ON c.id_category = p.id_category
			LEFT JOIN images img ON img.id_product = p.id_product AND img.cover = 1
			WHERE i.id_set_product = ?
			ORDER BY i.position ASC, i.id_set_item ASC',
			[$idSetProduct]
		) ?: [];

		foreach ($rows as &$row) {
			$row['id_product'] = (int) $row['id_product'];
			$row['qty'] = max(1, (int) $row['qty']);
			$row['position'] = (int) $row['position'];
			$row['price'] = (float) $row['price'];
			$row['line_total'] = $row['price'] * $row['qty'];
			$row['price_formatted'] = Tools::displayPrice($row['price']);
			$row['line_total_formatted'] = Tools::displayPrice($row['line_total']);
			$row['image_url'] = Product::getImageUrl(isset($row['id_image']) ? (int) $row['id_image'] : null);
			$row['url'] = (!empty($row['category_link']) && !empty($row['product_link']))
				? Product::getLink($row)
				: '';
			$row['child_stock'] = Product::getStock($row, 0);
		}
		unset($row);

		return $rows;
	}

	public static function getAvailableStock(int $idSetProduct): int
	{
		$items = self::getItems($idSetProduct);

		if ($items === []) {
			return 0;
		}

		$min = null;

		foreach ($items as $item) {
			if ((int) ($item['active'] ?? 0) !== 1) {
				return 0;
			}

			if (($item['product_type'] ?? '') === 'pack') {
				return 0;
			}

			$perSet = max(1, (int) $item['qty']);
			$childStock = (int) ($item['child_stock'] ?? 0);
			$sets = (int) floor($childStock / $perSet);

			if ($min === null || $sets < $min) {
				$min = $sets;
			}
		}

		return max(0, (int) $min);
	}

	/**
	 * @param array<string, mixed>|null $packRow
	 * @return array{price: float, old_price: float, components_total: float, has_override: bool}
	 */
	public static function getPricing(int $idSetProduct, ?array $packRow = null): array
	{
		if ($packRow === null) {
			$packRow = Product::getByIdAdmin($idSetProduct) ?: [];
		}

		$items = self::getItems($idSetProduct);
		$componentsTotal = 0.0;

		foreach ($items as $item) {
			$componentsTotal += (float) $item['price'] * max(1, (int) $item['qty']);
		}

		$overrideRaw = $packRow['pack_price_override'] ?? null;
		$hasOverride = $overrideRaw !== null && $overrideRaw !== '';
		$override = $hasOverride ? max(0.0, (float) $overrideRaw) : null;

		$price = $hasOverride ? (float) $override : $componentsTotal;
		$oldPrice = ($hasOverride && $componentsTotal > $price) ? $componentsTotal : 0.0;

		return [
			'price' => $price,
			'old_price' => $oldPrice,
			'components_total' => $componentsTotal,
			'has_override' => $hasOverride,
		];
	}

	/**
	 * @param array<int, array{id_product?: int|string, qty?: int|string, position?: int|string}> $items
	 */
	public static function saveItems(int $idSetProduct, array $items): array
	{
		self::ensureSchema();

		if ($idSetProduct <= 0) {
			return ['success' => false, 'message' => 'Geçersiz set ürünü'];
		}

		$pack = Product::getByIdAdmin($idSetProduct);
		if (!$pack) {
			return ['success' => false, 'message' => 'Ürün bulunamadı'];
		}

		$clean = [];
		$seen = [];

		foreach ($items as $row) {
			$idChild = (int) ($row['id_product'] ?? 0);
			$qty = max(1, (int) ($row['qty'] ?? 1));
			$position = (int) ($row['position'] ?? 0);

			if ($idChild <= 0 || $idChild === $idSetProduct) {
				continue;
			}

			if (isset($seen[$idChild])) {
				continue;
			}

			$child = Product::getByIdAdmin($idChild);
			if (!$child || (int) ($child['active'] ?? 0) !== 1) {
				return ['success' => false, 'message' => 'Bileşen ürün bulunamadı veya pasif: #' . $idChild];
			}

			if (($child['product_type'] ?? '') === 'pack') {
				return ['success' => false, 'message' => 'Set içine başka set eklenemez'];
			}

			if (($child['product_type'] ?? '') === 'virtual') {
				return ['success' => false, 'message' => 'Sanal ürün sete eklenemez: ' . ($child['product_name'] ?? '')];
			}

			if (ProductVariation::hasVariations($idChild)) {
				return ['success' => false, 'message' => 'Varyasyonlu ürün sete eklenemez: ' . ($child['product_name'] ?? '')];
			}

			$seen[$idChild] = true;
			$clean[] = [
				'id_product' => $idChild,
				'qty' => $qty,
				'position' => $position,
			];
		}

		usort($clean, static function (array $a, array $b): int {
			return $a['position'] <=> $b['position'];
		});

		DB::execute('DELETE FROM product_set_items WHERE id_set_product = ?', [$idSetProduct]);

		$pos = 0;
		foreach ($clean as $row) {
			DB::insert('product_set_items', [
				'id_set_product' => $idSetProduct,
				'id_product' => $row['id_product'],
				'qty' => $row['qty'],
				'position' => $pos++,
			]);
		}

		self::syncPackPrice($idSetProduct);

		return ['success' => true, 'message' => 'Set bileşenleri kaydedildi', 'count' => count($clean)];
	}

	public static function syncPackPrice(int $idSetProduct): void
	{
		$pack = Product::getByIdAdmin($idSetProduct);
		if (!$pack || ($pack['product_type'] ?? '') !== 'pack') {
			return;
		}

		$pricing = self::getPricing($idSetProduct, $pack);

		DB::update('products', [
			'price' => $pricing['price'],
			'doviz_price' => $pricing['price'],
			'old_price' => $pricing['old_price'],
			'doviz_old_price' => $pricing['old_price'],
			'stock' => 0,
		], 'id_product = :id', ['id' => $idSetProduct]);
	}

	/** @return array<int, array{id_product: int, product_name: string}> */
	public static function searchProducts(string $query, int $excludeId = 0, int $limit = 20): array
	{
		$query = trim($query);
		$limit = max(1, min(50, $limit));

		$sql = 'SELECT id_product, product_name, price, stock
			FROM products
			WHERE active = 1 AND product_type = \'physical\'';
		$params = [];

		if ($excludeId > 0) {
			$sql .= ' AND id_product != ?';
			$params[] = $excludeId;
		}

		if ($query !== '') {
			$sql .= ' AND (product_name LIKE ? OR stock_code LIKE ? OR CAST(id_product AS CHAR) = ?)';
			$like = '%' . $query . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $query;
		}

		$sql .= ' ORDER BY product_name ASC LIMIT ' . (int) $limit;

		$rows = DB::execute($sql, $params) ?: [];
		$out = [];

		foreach ($rows as $row) {
			$id = (int) $row['id_product'];
			if (ProductVariation::hasVariations($id)) {
				continue;
			}
			$out[] = [
				'id_product' => $id,
				'product_name' => (string) $row['product_name'],
				'price' => (float) $row['price'],
				'price_formatted' => Tools::displayPrice((float) $row['price']),
				'stock' => (int) $row['stock'],
			];
		}

		return $out;
	}
}
