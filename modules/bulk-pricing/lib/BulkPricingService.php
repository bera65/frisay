<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class BulkPricingService
{
	public const FIELD_PRICE = 'price';
	public const FIELD_COST = 'cost';
	public const FIELD_OLD_PRICE = 'old_price';

	/** @return array{id_category: int, id_brand: int, active: int, query: string} */
	public static function parseFilters(array $input): array
	{
		$active = -1;

		if (array_key_exists('active_filter', $input)) {
			$active = (int) $input['active_filter'];
		}

		return [
			'id_category' => max(0, (int) ($input['id_category'] ?? 0)),
			'id_brand' => max(0, (int) ($input['id_brand'] ?? 0)),
			'active' => $active,
			'query' => trim((string) ($input['q'] ?? '')),
		];
	}

	/** @return array{mode: string, direction: string, value: float, fields: string[]} */
	public static function parseAdjustment(array $input): array
	{
		$fields = [];

		foreach ([self::FIELD_PRICE, self::FIELD_COST, self::FIELD_OLD_PRICE] as $field) {
			if (!empty($input['field_' . $field])) {
				$fields[] = $field;
			}
		}

		$mode = (string) ($input['adjust_mode'] ?? 'percent');
		$mode = $mode === 'fixed' ? 'fixed' : 'percent';

		$direction = (string) ($input['adjust_direction'] ?? 'increase');
		$direction = $direction === 'decrease' ? 'decrease' : 'increase';

		$value = (float) str_replace(',', '.', (string) ($input['adjust_value'] ?? '0'));

		return [
			'mode' => $mode,
			'direction' => $direction,
			'value' => max(0, $value),
			'fields' => $fields,
		];
	}

	public static function validateAdjustment(array $adjustment): ?string
	{
		if ($adjustment['fields'] === []) {
			return 'En az bir fiyat alanı seçin (satış, alış veya eski fiyat).';
		}

		if ($adjustment['value'] <= 0) {
			return 'Geçerli bir oran veya tutar girin.';
		}

		if ($adjustment['mode'] === 'percent' && $adjustment['value'] > 100 && $adjustment['direction'] === 'decrease') {
			return 'Yüzde indirim en fazla %100 olabilir.';
		}

		return null;
	}

	public static function countMatching(array $filters): int
	{
		$params = [];
		$where = self::buildWhere($filters, $params);

		return (int) DB::getValue(
			'SELECT COUNT(*) FROM products p WHERE ' . $where,
			$params
		);
	}

	/** @return array<int, array<string, mixed>> */
	public static function fetchMatching(array $filters, int $limit = 0, int $offset = 0): array
	{
		$params = [];
		$where = self::buildWhere($filters, $params);
		$sql = 'SELECT p.id_product, p.product_name, p.stock_code, p.price, p.cost, p.old_price,
				p.doviz, p.doviz_price, p.doviz_cost, p.doviz_old_price,
				b.brand_name, c.category_name
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			WHERE ' . $where . '
			ORDER BY p.id_product DESC';

		if ($limit > 0) {
			$sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . max(0, (int) $offset);
		}

		return DB::execute($sql, $params) ?: [];
	}

	/** @return array<int, array<string, mixed>> */
	public static function buildPreviewRows(array $filters, array $adjustment, int $limit = 8): array
	{
		$rows = self::fetchMatching($filters, $limit);
		$preview = [];

		foreach ($rows as $row) {
			$changes = self::buildChanges($row, $adjustment);
			$preview[] = array_merge($row, [
				'changes' => $changes,
				'has_change' => $changes !== [],
			]);
		}

		return $preview;
	}

	/** @return array{updated: int, skipped: int} */
	public static function apply(array $filters, array $adjustment): array
	{
		$products = self::fetchMatching($filters);
		$updated = 0;
		$skipped = 0;
		$shopCurrency = strtolower(trim(Currency::getShopCurrency()));

		foreach ($products as $product) {
			$id = (int) ($product['id_product'] ?? 0);

			if ($id <= 0) {
				$skipped++;
				continue;
			}

			$payload = self::buildUpdatePayload($product, $adjustment, $shopCurrency);

			if ($payload === []) {
				$skipped++;
				continue;
			}

			if (DB::update('products', $payload, 'id_product = :where_id', ['where_id' => $id]) === false) {
				$skipped++;
				continue;
			}

			$updated++;

			if (class_exists('Module', false)) {
				Module::runHook('product.updated', [$id, Product::getByIdAdmin($id) ?: $product, false]);
			}
		}

		return ['updated' => $updated, 'skipped' => $skipped];
	}

	public static function formatAdjustmentLabel(array $adjustment): string
	{
		$fieldLabels = [
			self::FIELD_PRICE => 'Satış fiyatı',
			self::FIELD_COST => 'Alış fiyatı',
			self::FIELD_OLD_PRICE => 'Eski fiyat',
		];

		$fields = [];

		foreach ($adjustment['fields'] as $field) {
			$fields[] = $fieldLabels[$field] ?? $field;
		}

		$action = $adjustment['direction'] === 'decrease' ? 'indirim' : 'zam';

		if ($adjustment['mode'] === 'percent') {
			$detail = '%' . number_format($adjustment['value'], 2, ',', '.') . ' ' . $action;
		} else {
			$detail = number_format($adjustment['value'], 2, ',', '.') . ' TL ' . $action;
		}

		return implode(', ', $fields) . ' · ' . $detail;
	}

	/** @return array<string, array{before: float, after: float}> */
	private static function buildChanges(array $product, array $adjustment): array
	{
		$changes = [];
		$shopCurrency = strtolower(trim(Currency::getShopCurrency()));
		$payload = self::buildUpdatePayload($product, $adjustment, $shopCurrency);

		foreach ($adjustment['fields'] as $field) {
			if (!array_key_exists($field, $payload)) {
				continue;
			}

			$changes[$field] = [
				'before' => (float) ($product[$field] ?? 0),
				'after' => (float) $payload[$field],
			];
		}

		return $changes;
	}

	/** @return array<string, float> */
	private static function buildUpdatePayload(array $product, array $adjustment, string $shopCurrency): array
	{
		$payload = [];
		$productCurrency = strtolower(trim((string) ($product['doviz'] ?? $shopCurrency)));
		$isFx = $productCurrency !== '' && $productCurrency !== $shopCurrency;

		foreach ($adjustment['fields'] as $field) {
			$current = (float) ($product[$field] ?? 0);
			$newValue = self::adjustValue($current, $adjustment);

			if (abs($newValue - $current) < 0.00001) {
				continue;
			}

			$payload[$field] = $newValue;

			if (!$isFx) {
				continue;
			}

			$fxField = self::mapFxField($field);

			if ($fxField === null) {
				continue;
			}

			$fxCurrent = (float) ($product[$fxField] ?? 0);

			if ($fxCurrent > 0) {
				$payload[$fxField] = self::adjustValue($fxCurrent, $adjustment);
			}
		}

		return $payload;
	}

	private static function mapFxField(string $field): ?string
	{
		$map = [
			self::FIELD_PRICE => 'doviz_price',
			self::FIELD_COST => 'doviz_cost',
			self::FIELD_OLD_PRICE => 'doviz_old_price',
		];

		return $map[$field] ?? null;
	}

	private static function adjustValue(float $current, array $adjustment): float
	{
		$current = max(0, $current);

		if ($adjustment['mode'] === 'percent') {
			$factor = (float) $adjustment['value'] / 100;

			if ($adjustment['direction'] === 'decrease') {
				$current *= max(0, 1 - $factor);
			} else {
				$current *= 1 + $factor;
			}
		} else {
			$delta = (float) $adjustment['value'];

			if ($adjustment['direction'] === 'decrease') {
				$current -= $delta;
			} else {
				$current += $delta;
			}
		}

		return max(0, round($current, 2));
	}

	private static function buildWhere(array $filters, array &$params): string
	{
		$sql = '1=1';

		if ($filters['id_category'] > 0) {
			$sql .= ' AND p.id_category = ?';
			$params[] = $filters['id_category'];
		}

		if ($filters['id_brand'] > 0) {
			$sql .= ' AND p.id_brand = ?';
			$params[] = $filters['id_brand'];
		}

		if ($filters['active'] >= 0) {
			$sql .= ' AND p.active = ?';
			$params[] = $filters['active'];
		}

		if ($filters['query'] !== '') {
			$like = '%' . $filters['query'] . '%';
			$sql .= ' AND (p.product_name LIKE ? OR p.stock_code LIKE ? OR p.barcode LIKE ?)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		return $sql;
	}

	/** @return array<int, array{id_category: int, category_name: string}> */
	public static function getCategoryOptions(): array
	{
		$rows = DB::execute(
			'SELECT id_category, category_name FROM categories WHERE active = 1 ORDER BY category_name ASC'
		) ?: [];

		return array_map(static function (array $row): array {
			$row = Lang::applyCategory($row);

			return [
				'id_category' => (int) ($row['id_category'] ?? 0),
				'category_name' => (string) ($row['category_name'] ?? ''),
			];
		}, $rows);
	}
}
