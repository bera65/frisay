<?php

class CartPromotion
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$table = DB::execute("SHOW TABLES LIKE 'cart_promotions'");
		if (empty($table)) {
			DB::execute(
				"CREATE TABLE `cart_promotions` (
					`id_promotion` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(128) NOT NULL,
					`promo_type` enum('nth_item','buy_x_pay_y') NOT NULL DEFAULT 'nth_item',
					`item_position` int(11) NOT NULL DEFAULT 2,
					`item_discount_type` enum('percent','fixed') NOT NULL DEFAULT 'fixed',
					`item_discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
					`repeat_every` tinyint(1) NOT NULL DEFAULT 0,
					`buy_qty` int(11) NOT NULL DEFAULT 3,
					`pay_qty` int(11) NOT NULL DEFAULT 2,
					`min_cart` decimal(10,2) NOT NULL DEFAULT 0.00,
					`priority` int(11) NOT NULL DEFAULT 0,
					`date_from` datetime DEFAULT NULL,
					`date_to` datetime DEFAULT NULL,
					`active` tinyint(1) NOT NULL DEFAULT 1,
					`date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id_promotion`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}

		$promoCol = DB::execute("SHOW COLUMNS FROM `orders` LIKE 'promotion_discount'");
		if (empty($promoCol)) {
			DB::execute(
				"ALTER TABLE `orders`
				 ADD COLUMN `promotion_name` varchar(128) NOT NULL DEFAULT '' AFTER `coupon_discount`,
				 ADD COLUMN `promotion_discount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `promotion_name`"
			);
		}
	}

	public static function getAdminList(): array
	{
		self::ensureSchema();

		$rows = DB::execute('SELECT * FROM cart_promotions ORDER BY date_add DESC') ?: [];

		foreach ($rows as &$row) {
			$row = self::enrichAdmin($row);
		}
		unset($row);

		return $rows;
	}

	public static function getById(int $idPromotion): ?array
	{
		self::ensureSchema();
		$row = DB::getRowSafe('cart_promotions', 'id_promotion = ?', [$idPromotion]);

		return $row ?: null;
	}

	public static function save(array $data, int $idPromotion = 0): array
	{
		self::ensureSchema();

		$name = trim((string) ($data['name'] ?? ''));
		$promoType = (string) ($data['promo_type'] ?? 'nth_item');
		$itemPosition = max(2, (int) ($data['item_position'] ?? 2));
		$itemDiscountType = (string) ($data['item_discount_type'] ?? 'fixed');
		$itemDiscountValue = (float) ($data['item_discount_value'] ?? 0);
		$repeatEvery = !empty($data['repeat_every']) ? 1 : 0;
		$buyQty = max(2, (int) ($data['buy_qty'] ?? 3));
		$payQty = max(1, (int) ($data['pay_qty'] ?? 2));
		$minCart = max(0.0, (float) ($data['min_cart'] ?? 0));
		$active = !empty($data['active']) ? 1 : 0;
		$dateFrom = self::normalizeDateTime((string) ($data['date_from'] ?? ''));
		$dateTo = self::normalizeDateTime((string) ($data['date_to'] ?? ''));

		if ($name === '') {
			return self::fail('Kampanya adı girin');
		}

		if (!in_array($promoType, ['nth_item', 'buy_x_pay_y'], true)) {
			return self::fail('Geçersiz kampanya tipi');
		}

		if ($promoType === 'nth_item') {
			if (!in_array($itemDiscountType, ['percent', 'fixed'], true)) {
				return self::fail('Geçersiz indirim tipi');
			}

			if ($itemDiscountValue <= 0 || ($itemDiscountType === 'percent' && $itemDiscountValue > 100)) {
				return self::fail('Geçerli bir indirim değeri girin');
			}
		}

		if ($promoType === 'buy_x_pay_y' && $payQty >= $buyQty) {
			return self::fail('Ödenecek adet, alınacak adetten küçük olmalı');
		}

		$payload = [
			'name' => $name,
			'promo_type' => $promoType,
			'item_position' => $itemPosition,
			'item_discount_type' => $itemDiscountType,
			'item_discount_value' => $itemDiscountValue,
			'repeat_every' => $repeatEvery,
			'buy_qty' => $buyQty,
			'pay_qty' => $payQty,
			'min_cart' => $minCart,
			'priority' => 0,
			'active' => $active,
			'date_from' => $dateFrom !== '' ? $dateFrom : null,
			'date_to' => $dateTo !== '' ? $dateTo : null,
		];

		if ($idPromotion > 0) {
			$updated = DB::update('cart_promotions', $payload, 'id_promotion = :id_promotion', [
				'id_promotion' => $idPromotion,
			]);

			if ($updated === false) {
				return self::fail('Kampanya güncellenemedi');
			}

			return self::ok('Kampanya güncellendi');
		}

		$id = DB::insert('cart_promotions', $payload);

		if (!$id) {
			return self::fail('Kampanya oluşturulamadı');
		}

		return self::ok('Kampanya oluşturuldu');
	}

	public static function delete(int $idPromotion): array
	{
		self::ensureSchema();

		if ($idPromotion <= 0) {
			return self::fail('Geçersiz kampanya');
		}

		DB::execute('DELETE FROM cart_promotions WHERE id_promotion = ?', [$idPromotion]);

		return self::ok('Kampanya silindi');
	}

	/** @return array{
	 *   discount: float,
	 *   name: string,
	 *   label: string,
	 *   id_promotion: int,
	 *   lines: array<int, array{name: string, label: string, discount: float, discount_formatted: string, id_promotion: int}>
	 * } */
	public static function calculate(array $cart): array
	{
		self::ensureSchema();

		$empty = [
			'discount' => 0.0,
			'name' => '',
			'label' => '',
			'id_promotion' => 0,
			'lines' => [],
		];

		if (!empty($cart['empty']) || empty($cart['items'])) {
			return $empty;
		}

		$subtotal = (float) ($cart['total'] ?? $cart['subtotal'] ?? 0);
		$promotions = self::resolveActivePromotions($subtotal);

		if ($promotions === []) {
			return $empty;
		}

		$units = self::expandUnits($cart['items']);
		$totalDiscount = 0.0;
		$lines = [];
		$labels = [];
		$names = [];

		foreach ($promotions as $promotion) {
			if ($promotion['promo_type'] === 'buy_x_pay_y') {
				$discount = self::calculateBuyXPayY($units, $promotion);
			} else {
				$discount = self::calculateNthItem($units, $promotion);
			}

			$discount = max(0.0, round($discount, 2));

			if ($discount <= 0) {
				continue;
			}

			$totalDiscount += $discount;
			$name = (string) $promotion['name'];
			$label = self::buildLabel($promotion, $discount);
			$names[] = $name;
			$labels[] = $label;
			$lines[] = [
				'id_promotion' => (int) $promotion['id_promotion'],
				'name' => $name,
				'label' => $label,
				'discount' => $discount,
				'discount_formatted' => Tools::displayPrice($discount),
			];
		}

		$totalDiscount = min($subtotal, max(0.0, round($totalDiscount, 2)));

		if ($totalDiscount <= 0) {
			return $empty;
		}

		return [
			'discount' => $totalDiscount,
			'name' => implode(' + ', $names),
			'label' => implode('; ', $labels),
			'id_promotion' => count($lines) === 1 ? (int) $lines[0]['id_promotion'] : 0,
			'lines' => $lines,
		];
	}

	/** @return array<int, array<string, mixed>> */
	private static function resolveActivePromotions(float $subtotal): array
	{
		$now = date('Y-m-d H:i:s');

		return DB::execute(
			'SELECT * FROM cart_promotions
			 WHERE active = 1
			 AND (date_from IS NULL OR date_from <= ?)
			 AND (date_to IS NULL OR date_to >= ?)
			 AND (min_cart <= 0 OR min_cart <= ?)
			 ORDER BY id_promotion ASC',
			[$now, $now, $subtotal]
		) ?: [];
	}

	/** @param array<int, array{unit_price: float}> $units */
	private static function calculateNthItem(array $units, array $promotion): float
	{
		$position = max(2, (int) $promotion['item_position']);
		$repeat = (int) ($promotion['repeat_every'] ?? 0) === 1;
		$discount = 0.0;

		foreach ($units as $index => $unit) {
			$pos = $index + 1;
			$isTarget = $repeat ? ($pos % $position === 0) : ($pos === $position);

			if ($isTarget) {
				$discount += self::unitDiscount((float) $unit['unit_price'], $promotion);
			}
		}

		return $discount;
	}

	/** @param array<int, array{unit_price: float}> $units */
	private static function calculateBuyXPayY(array $units, array $promotion): float
	{
		$buyQty = max(2, (int) $promotion['buy_qty']);
		$payQty = max(1, (int) $promotion['pay_qty']);
		$freePerGroup = $buyQty - $payQty;

		if ($freePerGroup <= 0) {
			return 0.0;
		}

		usort($units, static function ($a, $b) {
			return $a['unit_price'] <=> $b['unit_price'];
		});

		$groups = intdiv(count($units), $buyQty);
		$freeCount = $groups * $freePerGroup;
		$discount = 0.0;

		for ($i = 0; $i < $freeCount && isset($units[$i]); $i++) {
			$discount += (float) $units[$i]['unit_price'];
		}

		return $discount;
	}

	private static function unitDiscount(float $unitPrice, array $promotion): float
	{
		$value = (float) $promotion['item_discount_value'];

		if ($promotion['item_discount_type'] === 'percent') {
			return round($unitPrice * $value / 100, 2);
		}

		return min($unitPrice, $value);
	}

	/** @param array<int, array<string, mixed>> $items */
	private static function expandUnits(array $items): array
	{
		$units = [];

		foreach ($items as $item) {
			$price = (float) ($item['price'] ?? 0);
			$qty = max(1, (int) ($item['qty'] ?? 1));

			for ($i = 0; $i < $qty; $i++) {
				$units[] = ['unit_price' => $price];
			}
		}

		return $units;
	}

	private static function buildLabel(array $promotion, float $discount): string
	{
		if ($promotion['promo_type'] === 'buy_x_pay_y') {
			return (string) $promotion['name'] . ' (-' . Tools::displayPrice($discount) . ')';
		}

		$position = (int) $promotion['item_position'];
		$valueLabel = $promotion['item_discount_type'] === 'percent'
			? '%' . (float) $promotion['item_discount_value']
			: Tools::displayPrice($promotion['item_discount_value']);

		return $position . '. ürün ' . $valueLabel . ' (-' . Tools::displayPrice($discount) . ')';
	}

	public static function formatDateTimeInput(?string $value): string
	{
		if ($value === null || $value === '') {
			return '';
		}

		return substr(str_replace(' ', 'T', $value), 0, 16);
	}

	private static function normalizeDateTime(string $value): string
	{
		$value = trim(str_replace('T', ' ', $value));

		if ($value === '') {
			return '';
		}

		$ts = strtotime($value);

		return $ts ? date('Y-m-d H:i:s', $ts) : '';
	}

	private static function enrichAdmin(array $row): array
	{
		$row['active'] = (int) $row['active'];
		$row['rule_label'] = self::describeRule($row);
		$row['min_cart_formatted'] = Tools::displayPrice($row['min_cart']);

		return $row;
	}

	private static function describeRule(array $row): string
	{
		if ($row['promo_type'] === 'buy_x_pay_y') {
			return (int) $row['buy_qty'] . ' al ' . (int) $row['pay_qty'] . ' öde';
		}

		$valueLabel = $row['item_discount_type'] === 'percent'
			? '%' . (float) $row['item_discount_value']
			: Tools::displayPrice($row['item_discount_value']);
		$suffix = (int) $row['repeat_every'] === 1 ? ' (her ' . (int) $row['item_position'] . '.)' : '';

		return (int) $row['item_position'] . '. ürüne ' . $valueLabel . ' indirim' . $suffix;
	}

	private static function ok(string $message): array
	{
		return ['success' => true, 'message' => $message];
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message];
	}
}
