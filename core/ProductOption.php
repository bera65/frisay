<?php

/**
 * Ürün seçenekleri — stok/fiyat etkilemez; sipariş satırına not olarak yazılır.
 * Örn: Boyut (1, 1.5), İçecek (Ayran, Kola), Acı (Acılı, Sade)
 */
class ProductOption
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		if (empty(DB::execute("SHOW TABLES LIKE 'product_option_groups'"))) {
			DB::execute(
				"CREATE TABLE `product_option_groups` (
					`id_group` int(11) NOT NULL AUTO_INCREMENT,
					`id_product` int(11) NOT NULL,
					`group_name` varchar(64) NOT NULL,
					`required` tinyint(1) NOT NULL DEFAULT 1,
					`sort_order` int(11) NOT NULL DEFAULT 0,
					PRIMARY KEY (`id_group`),
					KEY `id_product` (`id_product`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		if (empty(DB::execute("SHOW TABLES LIKE 'product_option_values'"))) {
			DB::execute(
				"CREATE TABLE `product_option_values` (
					`id_value` int(11) NOT NULL AUTO_INCREMENT,
					`id_group` int(11) NOT NULL,
					`label` varchar(128) NOT NULL,
					`sort_order` int(11) NOT NULL DEFAULT 0,
					PRIMARY KEY (`id_value`),
					KEY `id_group` (`id_group`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	public static function hasOptions(int $idProduct): bool
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return false;
		}

		return (int) DB::getValue(
			'SELECT COUNT(*) FROM product_option_groups WHERE id_product = ? LIMIT 1',
			[$idProduct]
		) > 0;
	}

	/** @return array<int, array<string, mixed>> */
	public static function getByProduct(int $idProduct): array
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return [];
		}

		$groups = DB::execute(
			'SELECT * FROM product_option_groups WHERE id_product = ? ORDER BY sort_order ASC, id_group ASC',
			[$idProduct]
		) ?: [];

		$list = [];

		foreach ($groups as $group) {
			$idGroup = (int) ($group['id_group'] ?? 0);
			$values = DB::execute(
				'SELECT label FROM product_option_values WHERE id_group = ? ORDER BY sort_order ASC, id_value ASC',
				[$idGroup]
			) ?: [];

			$list[] = [
				'id_group' => $idGroup,
				'name' => (string) ($group['group_name'] ?? ''),
				'required' => (int) ($group['required'] ?? 1) === 1,
				'values' => array_map(static fn(array $row): string => (string) ($row['label'] ?? ''), $values),
			];
		}

		return $list;
	}

	/** Mağaza arayüzü */
	public static function getForStorefront(int $idProduct): array
	{
		$groups = self::getByProduct($idProduct);

		return [
			'has_options' => $groups !== [],
			'groups' => array_map(static function (array $group): array {
				return [
					'name' => $group['name'],
					'required' => $group['required'],
					'values' => $group['values'],
				];
			}, $groups),
		];
	}

	/** Admin form satırı */
	public static function formatFormRow(array $group): array
	{
		return [
			'id_group' => (int) ($group['id_group'] ?? 0),
			'name' => (string) ($group['name'] ?? ''),
			'required' => !empty($group['required']),
			'values_text' => implode("\n", is_array($group['values'] ?? null) ? $group['values'] : []),
		];
	}

	/** POST option_groups[...] */
	public static function parseFormRows(array $rows): array
	{
		$parsed = [];

		foreach ($rows as $row) {
			if (!is_array($row)) {
				continue;
			}

			$name = trim((string) ($row['name'] ?? ''));
			$required = !isset($row['required']) || filter_var($row['required'], FILTER_VALIDATE_BOOLEAN);
			$valuesText = (string) ($row['values_text'] ?? '');

			if ($valuesText === '' && !empty($row['values']) && is_array($row['values'])) {
				$valuesText = implode("\n", $row['values']);
			}

			$values = [];

			foreach (preg_split('/\R/u', $valuesText) ?: [] as $line) {
				$line = trim((string) $line);

				if ($line !== '') {
					$values[] = $line;
				}
			}

			if ($name === '' || $values === []) {
				continue;
			}

			$parsed[] = [
				'id_group' => (int) ($row['id_group'] ?? 0),
				'name' => $name,
				'required' => $required,
				'values' => array_values(array_unique($values)),
			];
		}

		return $parsed;
	}

	/** @param array<int, array<string, mixed>> $groups */
	public static function saveForProduct(int $idProduct, array $groups): ?array
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return ['success' => false, 'message' => 'Geçersiz ürün'];
		}

		self::deleteByProduct($idProduct);

		foreach ($groups as $index => $group) {
			$name = trim((string) ($group['name'] ?? ''));
			$values = is_array($group['values'] ?? null) ? $group['values'] : [];
			$required = !isset($group['required']) || filter_var($group['required'], FILTER_VALIDATE_BOOLEAN);

			if ($name === '' || $values === []) {
				return ['success' => false, 'message' => 'Seçenek grubu #' . ($index + 1) . ' için ad ve değer girin'];
			}

			$idGroup = DB::insert('product_option_groups', [
				'id_product' => $idProduct,
				'group_name' => mb_substr($name, 0, 64),
				'required' => $required ? 1 : 0,
				'sort_order' => $index,
			]);

			if (!$idGroup) {
				return ['success' => false, 'message' => 'Seçenek grubu kaydedilemedi'];
			}

			foreach ($values as $vIndex => $label) {
				$label = trim((string) $label);

				if ($label === '') {
					continue;
				}

				DB::insert('product_option_values', [
					'id_group' => (int) $idGroup,
					'label' => mb_substr($label, 0, 128),
					'sort_order' => (int) $vIndex,
				]);
			}
		}

		return null;
	}

	public static function deleteByProduct(int $idProduct): void
	{
		self::ensureSchema();

		if ($idProduct <= 0) {
			return;
		}

		$groupIds = DB::execute(
			'SELECT id_group FROM product_option_groups WHERE id_product = ?',
			[$idProduct]
		) ?: [];

		foreach ($groupIds as $row) {
			DB::execute('DELETE FROM product_option_values WHERE id_group = ?', [(int) $row['id_group']]);
		}

		DB::execute('DELETE FROM product_option_groups WHERE id_product = ?', [$idProduct]);
	}

	/** @param array<string, string> $selected */
	public static function validateSelections(int $idProduct, array $selected): ?string
	{
		$groups = self::getByProduct($idProduct);

		foreach ($groups as $group) {
			$name = (string) ($group['name'] ?? '');
			$value = trim((string) ($selected[$name] ?? ''));

			if ($name === '') {
				continue;
			}

			if (!empty($group['required']) && $value === '') {
				return $name . ' seçin';
			}

			if ($value !== '' && !in_array($value, $group['values'], true)) {
				return 'Geçersiz seçenek: ' . $name;
			}
		}

		return null;
	}

	/** @param array<string, string> $selected */
	public static function formatLabel(array $selected): string
	{
		$parts = [];

		foreach ($selected as $name => $value) {
			$name = trim((string) $name);
			$value = trim((string) $value);

			if ($name === '' || $value === '') {
				continue;
			}

			$parts[] = $name . ': ' . $value;
		}

		return implode(', ', $parts);
	}

	/** @param array<string, string> $selected */
	public static function normalizeSelections(array $selected): array
	{
		$normalized = [];

		foreach ($selected as $name => $value) {
			$name = trim((string) $name);
			$value = trim((string) $value);

			if ($name !== '' && $value !== '') {
				$normalized[$name] = $value;
			}
		}

		ksort($normalized);

		return $normalized;
	}
}
