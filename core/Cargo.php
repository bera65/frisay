<?php

class Cargo
{
	public const SESSION_KEY = 'checkout_id_cargo';

	public static function ensureSchema(): void
	{
		$companies = DB::execute("SHOW TABLES LIKE 'cargo_companies'");

		if (empty($companies)) {
			DB::execute(
				"CREATE TABLE `cargo_companies` (
					`id_cargo` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(128) NOT NULL,
					`tracking_url` varchar(512) NOT NULL DEFAULT '',
					`active` tinyint(1) NOT NULL DEFAULT 1,
					`is_default` tinyint(1) NOT NULL DEFAULT 0,
					`position` int(11) NOT NULL DEFAULT 0,
					`date_add` datetime NOT NULL,
					`date_upd` datetime NOT NULL,
					PRIMARY KEY (`id_cargo`),
					KEY `active` (`active`),
					KEY `is_default` (`is_default`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		$rates = DB::execute("SHOW TABLES LIKE 'cargo_rates'");

		if (empty($rates)) {
			DB::execute(
				"CREATE TABLE `cargo_rates` (
					`id_rate` int(11) NOT NULL AUTO_INCREMENT,
					`id_cargo` int(11) NOT NULL,
					`min_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
					`max_amount` decimal(20,2) NOT NULL DEFAULT 0.00,
					`fee` decimal(20,2) NOT NULL DEFAULT 0.00,
					`position` int(11) NOT NULL DEFAULT 0,
					PRIMARY KEY (`id_rate`),
					KEY `id_cargo` (`id_cargo`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	/** @return array<int, array<string, mixed>> */
	public static function getList(bool $activeOnly = false): array
	{
		self::ensureSchema();

		$sql = 'SELECT * FROM cargo_companies';

		if ($activeOnly) {
			$sql .= ' WHERE active = 1';
		}

		$sql .= ' ORDER BY position ASC, name ASC, id_cargo ASC';

		$rows = DB::execute($sql) ?: [];

		foreach ($rows as &$row) {
			$row = self::hydrate($row);
		}
		unset($row);

		return $rows;
	}

	/** @return array<string, mixed>|null */
	public static function getById(int $id): ?array
	{
		self::ensureSchema();

		if ($id <= 0) {
			return null;
		}

		$row = DB::getRowSafe('cargo_companies', 'id_cargo = ?', [$id]);

		return is_array($row) ? self::hydrate($row) : null;
	}

	/** @return array<string, mixed>|null */
	public static function getByName(string $name): ?array
	{
		self::ensureSchema();

		$name = trim($name);

		if ($name === '') {
			return null;
		}

		$row = DB::getRowSafe('cargo_companies', 'name = ?', [$name]);

		return is_array($row) ? self::hydrate($row) : null;
	}

	/** @return array<string, mixed>|null */
	public static function getDefault(): ?array
	{
		self::ensureSchema();

		$row = DB::getRowSafe('cargo_companies', 'active = 1 AND is_default = 1');

		if (is_array($row)) {
			return self::hydrate($row);
		}

		$list = self::getList(true);

		return $list[0] ?? null;
	}

	/** @return array<int, array<string, mixed>> */
	public static function getRates(int $idCargo): array
	{
		self::ensureSchema();

		if ($idCargo <= 0) {
			return [];
		}

		$rows = DB::execute(
			'SELECT * FROM cargo_rates WHERE id_cargo = ? ORDER BY position ASC, min_amount ASC, id_rate ASC',
			[$idCargo]
		) ?: [];

		foreach ($rows as &$row) {
			$row['id_rate'] = (int) $row['id_rate'];
			$row['id_cargo'] = (int) $row['id_cargo'];
			$row['min_amount'] = (float) $row['min_amount'];
			$row['max_amount'] = (float) $row['max_amount'];
			$row['fee'] = (float) $row['fee'];
			$row['position'] = (int) $row['position'];
			$row['label'] = self::formatRateLabel($row['min_amount'], $row['max_amount'], $row['fee']);
		}
		unset($row);

		return $rows;
	}

	public static function getSelectedId(): int
	{
		return (int) ($_SESSION[self::SESSION_KEY] ?? 0);
	}

	public static function setSelectedId(int $id): bool
	{
		if ($id <= 0) {
			unset($_SESSION[self::SESSION_KEY]);

			return true;
		}

		$cargo = self::getById($id);

		if (!$cargo || empty($cargo['active'])) {
			return false;
		}

		$_SESSION[self::SESSION_KEY] = $id;

		return true;
	}

	/** Checkout’ta seçili kargo yoksa varsayılanı oturuma yaz. */
	public static function ensureSelected(): int
	{
		$id = self::getSelectedId();

		if ($id > 0) {
			$cargo = self::getById($id);

			if ($cargo && !empty($cargo['active'])) {
				return $id;
			}
		}

		$default = self::getDefault();

		if ($default) {
			$id = (int) $default['id_cargo'];
			self::setSelectedId($id);

			return $id;
		}

		self::setSelectedId(0);

		return 0;
	}

	/**
	 * Belirli kargo firmasının tutara göre ücreti.
	 * Aralık yoksa veya eşleşme yoksa 0.
	 */
	public static function getFeeForCargo(float $amount, int $idCargo): float
	{
		self::ensureSchema();

		if ($idCargo <= 0) {
			return 0.0;
		}

		$cargo = self::getById($idCargo);

		if (!$cargo || empty($cargo['active'])) {
			return 0.0;
		}

		$rates = self::getRates($idCargo);

		if ($rates === []) {
			return 0.0;
		}

		$amount = max(0.0, $amount);
		$matched = null;

		foreach ($rates as $rate) {
			$min = (float) $rate['min_amount'];
			$max = (float) $rate['max_amount'];

			if ($amount < $min) {
				continue;
			}

			if ($max > 0 && $amount > $max) {
				continue;
			}

			if ($matched === null || $min >= (float) $matched['min_amount']) {
				$matched = $rate;
			}
		}

		if ($matched === null) {
			return 0.0;
		}

		return max(0.0, (float) $matched['fee']);
	}

	/**
	 * Sepet tutarına göre kargo ücreti.
	 * id verilmezse seçili / varsayılan kargo kullanılır; kargo yoksa null.
	 */
	public static function getFeeForAmount(float $amount, ?int $idCargo = null): ?float
	{
		self::ensureSchema();

		if ($idCargo === null || $idCargo <= 0) {
			$idCargo = self::getSelectedId();
		}

		if ($idCargo <= 0) {
			$default = self::getDefault();
			$idCargo = $default ? (int) $default['id_cargo'] : 0;
		}

		if ($idCargo <= 0) {
			return null;
		}

		return self::getFeeForCargo($amount, $idCargo);
	}

	/**
	 * Checkout kargo seçenekleri (aktif firmalar + güncel ücret).
	 *
	 * @return array<int, array{id_cargo:int,name:string,fee:float,fee_formatted:string,selected:bool}>
	 */
	public static function getCheckoutOptions(float $amount): array
	{
		self::ensureSchema();

		$selected = self::ensureSelected();
		$options = [];

		foreach (self::getList(true) as $cargo) {
			$id = (int) $cargo['id_cargo'];
			$fee = self::getFeeForCargo($amount, $id);
			$options[] = [
				'id_cargo' => $id,
				'name' => (string) $cargo['name'],
				'fee' => $fee,
				'fee_formatted' => $fee > 0 ? Tools::displayPrice($fee) : translate('Free'),
				'selected' => $selected === $id,
			];
		}

		return $options;
	}

	/**
	 * Ürün / sepet bilgilendirmesi (varsayılan kargonun aralıklarından).
	 *
	 * @return array{free_shipping_min:float,shipping_fee:float}
	 */
	public static function getDisplayHints(): array
	{
		self::ensureSchema();

		$cargo = self::getDefault();

		if (!$cargo) {
			return [
				'free_shipping_min' => 0.0,
				'shipping_fee' => 0.0,
			];
		}

		$freeMin = 0.0;
		$shippingFee = 0.0;

		foreach (self::getRates((int) $cargo['id_cargo']) as $rate) {
			$fee = (float) $rate['fee'];
			$min = (float) $rate['min_amount'];

			if ($fee <= 0) {
				if ($min > 0 && ($freeMin <= 0 || $min < $freeMin)) {
					$freeMin = $min;
				}
			} elseif ($shippingFee <= 0 || $fee < $shippingFee) {
				$shippingFee = $fee;
			}
		}

		return [
			'free_shipping_min' => $freeMin,
			'shipping_fee' => $shippingFee,
		];
	}

	public static function resolveLogoUrl(string $name, ?int $idCargo = null): ?string
	{
		$name = trim($name);
		$candidates = [];

		if ($idCargo !== null && $idCargo > 0) {
			$candidates[] = (string) $idCargo;
		}

		if ($name !== '') {
			$candidates[] = self::logoSlug($name);
		}

		$candidates = array_values(array_unique(array_filter($candidates)));

		if ($candidates === []) {
			return null;
		}

		$dir = dirname(__DIR__) . '/img/cargos';
		$extensions = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
		$domain = rtrim((string) ($GLOBALS['domain'] ?? ''), '/');

		if ($domain === '') {
			return null;
		}

		foreach ($candidates as $slug) {
			foreach ($extensions as $ext) {
				$file = $dir . '/' . $slug . '.' . $ext;

				if (is_file($file)) {
					return $domain . '/img/cargos/' . $slug . '.' . $ext;
				}
			}
		}

		return null;
	}

	public static function buildTrackingUrl(string $trackingNumber, $cargo = null): string
	{
		$trackingNumber = trim($trackingNumber);

		if ($trackingNumber === '') {
			return '';
		}

		$company = null;

		if (is_array($cargo)) {
			$company = $cargo;
		} elseif (is_int($cargo) || (is_string($cargo) && ctype_digit($cargo))) {
			$company = self::getById((int) $cargo);
		} elseif (is_string($cargo) && trim($cargo) !== '') {
			$company = self::getByName(trim($cargo));
		}

		$base = '';

		if ($company) {
			$base = trim((string) ($company['tracking_url'] ?? ''));
		}

		if ($base === '') {
			return '';
		}

		if (strpos($base, '{code}') !== false) {
			return str_replace('{code}', rawurlencode($trackingNumber), $base);
		}

		return $base . $trackingNumber;
	}

	/**
	 * @param array{name:string,tracking_url?:string,active?:bool,is_default?:bool,position?:int} $data
	 * @param array<int, array{min_amount:float|string,max_amount:float|string,fee:float|string}> $rates
	 * @return array{ok:bool,message:string,id?:int}
	 */
	public static function save(?int $id, array $data, array $rates): array
	{
		self::ensureSchema();

		$name = trim((string) ($data['name'] ?? ''));

		if ($name === '') {
			return ['ok' => false, 'message' => 'Kargo firması adı gerekli'];
		}

		$now = date('Y-m-d H:i:s');
		$row = [
			'name' => mb_substr($name, 0, 128),
			'tracking_url' => mb_substr(trim((string) ($data['tracking_url'] ?? '')), 0, 512),
			'active' => !empty($data['active']) ? 1 : 0,
			'is_default' => !empty($data['is_default']) ? 1 : 0,
			'position' => max(0, (int) ($data['position'] ?? 0)),
			'date_upd' => $now,
		];

		if ($id && self::getById($id)) {
			DB::update('cargo_companies', $row, 'id_cargo = :where_id', ['where_id' => $id]);
			$idCargo = $id;
		} else {
			$row['date_add'] = $now;
			DB::insert('cargo_companies', $row);
			$idCargo = (int) (DB::getValue(
				'SELECT id_cargo FROM cargo_companies WHERE name = ? ORDER BY id_cargo DESC LIMIT 1',
				[$row['name']]
			) ?: 0);
		}

		if ($idCargo <= 0) {
			return ['ok' => false, 'message' => 'Kargo kaydı oluşturulamadı'];
		}

		if (!empty($row['is_default'])) {
			DB::execute(
				'UPDATE cargo_companies SET is_default = 0 WHERE id_cargo != ?',
				[$idCargo]
			);
		}

		self::replaceRates($idCargo, $rates);

		return ['ok' => true, 'message' => 'Kargo kaydedildi', 'id' => $idCargo];
	}

	/** @return array{ok:bool,message:string} */
	public static function delete(int $id): array
	{
		self::ensureSchema();

		if ($id <= 0 || !self::getById($id)) {
			return ['ok' => false, 'message' => 'Kayıt bulunamadı'];
		}

		DB::execute('DELETE FROM cargo_rates WHERE id_cargo = ?', [$id]);
		DB::execute('DELETE FROM cargo_companies WHERE id_cargo = ?', [$id]);

		return ['ok' => true, 'message' => 'Kargo silindi'];
	}

	/**
	 * @param array<int, array{min_amount?:mixed,max_amount?:mixed,fee?:mixed}> $rates
	 */
	private static function replaceRates(int $idCargo, array $rates): void
	{
		DB::execute('DELETE FROM cargo_rates WHERE id_cargo = ?', [$idCargo]);

		$position = 0;

		foreach ($rates as $rate) {
			$min = (float) str_replace(',', '.', (string) ($rate['min_amount'] ?? 0));
			$max = (float) str_replace(',', '.', (string) ($rate['max_amount'] ?? 0));
			$fee = (float) str_replace(',', '.', (string) ($rate['fee'] ?? 0));

			if ($min < 0) {
				$min = 0;
			}

			if ($max < 0) {
				$max = 0;
			}

			if ($fee < 0) {
				$fee = 0;
			}

			$minRaw = trim((string) ($rate['min_amount'] ?? ''));
			$maxRaw = trim((string) ($rate['max_amount'] ?? ''));
			$feeRaw = trim((string) ($rate['fee'] ?? ''));

			if ($minRaw === '' && $maxRaw === '' && $feeRaw === '') {
				continue;
			}

			DB::insert('cargo_rates', [
				'id_cargo' => $idCargo,
				'min_amount' => $min,
				'max_amount' => $max,
				'fee' => $fee,
				'position' => $position++,
			]);
		}
	}

	private static function formatRateLabel(float $min, float $max, float $fee): string
	{
		$minLabel = Tools::displayPrice($min);
		$feeLabel = $fee > 0 ? Tools::displayPrice($fee) : 'Ücretsiz';

		if ($max <= 0) {
			return $minLabel . '+ → ' . $feeLabel;
		}

		return $minLabel . ' – ' . Tools::displayPrice($max) . ' → ' . $feeLabel;
	}

	/**
	 * @param array<string, mixed> $row
	 * @return array<string, mixed>
	 */
	private static function hydrate(array $row): array
	{
		$row['id_cargo'] = (int) ($row['id_cargo'] ?? 0);
		$row['active'] = (int) ($row['active'] ?? 0);
		$row['is_default'] = (int) ($row['is_default'] ?? 0);
		$row['position'] = (int) ($row['position'] ?? 0);
		$row['rates'] = self::getRates($row['id_cargo']);

		return $row;
	}

	private static function logoSlug(string $name): string
	{
		$name = mb_strtolower(trim($name), 'UTF-8');
		$name = preg_replace('/\s*(kargo|cargo)\s*/iu', ' ', $name);
		$ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);

		if (!is_string($ascii) || $ascii === '') {
			$ascii = $name;
		}

		$slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii));

		return trim((string) $slug, '-') ?: '';
	}
}
