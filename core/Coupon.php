<?php

class Coupon
{
	const SESSION_KEY = 'applied_coupon';

	public static function normalizeCode(string $code): string
	{
		return strtoupper(preg_replace('/\s+/', '', trim($code)));
	}

	public static function getApplied(): ?array
	{
		$code = $_SESSION[self::SESSION_KEY] ?? '';

		if ($code === '') {
			return null;
		}

		$coupon = self::getByCode((string) $code);

		return $coupon ?: null;
	}

	public static function apply(string $code, float $subtotal): array
	{
		$code = self::normalizeCode($code);

		if ($code === '') {
			return self::fail('Kupon kodu girin');
		}

		$validation = self::validate($code, $subtotal);

		if (!$validation['success']) {
			return $validation;
		}

		$_SESSION[self::SESSION_KEY] = $code;

		return self::ok('Kupon uygulandı', $subtotal);
	}

	public static function remove(): array
	{
		unset($_SESSION[self::SESSION_KEY]);

		return [
			'success' => true,
			'message' => 'Kupon kaldırıldı',
			'discount' => 0.0,
			'discount_formatted' => Tools::displayPrice(0),
			'code' => '',
		];
	}

	public static function getDiscount(float $subtotal): float
	{
		$coupon = self::getApplied();

		if (!$coupon) {
			return 0.0;
		}

		$validation = self::validate($coupon['code'], $subtotal);

		if (!$validation['success']) {
			unset($_SESSION[self::SESSION_KEY]);

			return 0.0;
		}

		return (float) $validation['discount'];
	}

	public static function getCheckoutSummary(float $subtotal): array
	{
		$discount = self::getDiscount($subtotal);
		$coupon = self::getApplied();
		$afterDiscount = max(0.0, $subtotal - $discount);
		$requiresShipping = Cart::requiresShipping();
		$shipping = $requiresShipping ? Order::getShippingFee($afterDiscount) : 0.0;
		$total = $afterDiscount + $shipping;

		return [
			'subtotal' => $subtotal,
			'subtotal_formatted' => Tools::displayPrice($subtotal),
			'discount' => $discount,
			'discount_formatted' => Tools::displayPrice($discount),
			'coupon_code' => $coupon['code'] ?? '',
			'has_coupon' => $discount > 0,
			'shipping' => $shipping,
			'shipping_formatted' => $requiresShipping && $shipping > 0
				? Tools::displayPrice($shipping)
				: ($requiresShipping ? 'Ücretsiz' : '—'),
			'total' => $total,
			'total_formatted' => Tools::displayPrice($total),
			'free_shipping_min' => (float) (Settings::get('FREE_SHIPPING_MIN') ?: 1500),
			'requires_shipping' => $requiresShipping,
		];
	}

	public static function validate(string $code, float $subtotal): array
	{
		$code = self::normalizeCode($code);
		$coupon = self::getByCode($code);

		if (!$coupon) {
			return self::fail('Geçersiz kupon kodu');
		}

		if (!(int) $coupon['active']) {
			return self::fail('Bu kupon artık geçerli değil');
		}

		$now = date('Y-m-d H:i:s');

		if (!empty($coupon['date_from']) && $coupon['date_from'] > $now) {
			return self::fail('Bu kupon henüz aktif değil');
		}

		if (!empty($coupon['date_to']) && $coupon['date_to'] < $now) {
			return self::fail('Bu kuponun süresi dolmuş');
		}

		$minCart = (float) $coupon['min_cart'];
		if ($minCart > 0 && $subtotal < $minCart) {
			return self::fail('Bu kupon için minimum sepet tutarı ' . Tools::displayPrice($minCart));
		}

		$maxUses = (int) $coupon['max_uses'];
		if ($maxUses > 0 && (int) $coupon['used_count'] >= $maxUses) {
			return self::fail('Bu kupon kullanım limitine ulaştı');
		}

		$discount = self::calculateDiscount($coupon, $subtotal);

		if ($discount <= 0) {
			return self::fail('Kupon bu sepet için geçerli değil');
		}

		return [
			'success' => true,
			'message' => 'Kupon geçerli',
			'discount' => $discount,
			'discount_formatted' => Tools::displayPrice($discount),
			'coupon' => $coupon,
		];
	}

	public static function calculateDiscount(array $coupon, float $subtotal): float
	{
		$value = (float) $coupon['discount_value'];

		if ($coupon['discount_type'] === 'percent') {
			$discount = round($subtotal * $value / 100, 2);
		} else {
			$discount = $value;
		}

		return min($subtotal, max(0.0, $discount));
	}

	public static function markUsed(string $code): void
	{
		$code = self::normalizeCode($code);

		DB::execute(
			'UPDATE coupons SET used_count = used_count + 1 WHERE code = ?',
			[$code]
		);
	}

	public static function getByCode(string $code): ?array
	{
		$code = self::normalizeCode($code);
		$row = DB::getRowSafe('coupons', 'code = ?', [$code]);

		return $row ?: null;
	}

	public static function getById(int $idCoupon): ?array
	{
		$row = DB::getRowSafe('coupons', 'id_coupon = ?', [$idCoupon]);

		return $row ?: null;
	}

	public static function getAdminList(): array
	{
		$rows = DB::execute('SELECT * FROM coupons ORDER BY date_add DESC') ?: [];

		foreach ($rows as &$row) {
			$row = self::enrichAdmin($row);
		}
		unset($row);

		return $rows;
	}

	public static function save(array $data, int $idCoupon = 0): array
	{
		$code = self::normalizeCode((string) ($data['code'] ?? ''));
		$type = (string) ($data['discount_type'] ?? 'percent');
		$value = (float) ($data['discount_value'] ?? 0);
		$minCart = max(0.0, (float) ($data['min_cart'] ?? 0));
		$maxUses = max(0, (int) ($data['max_uses'] ?? 0));
		$active = !empty($data['active']) ? 1 : 0;
		$dateFrom = self::normalizeDateTime((string) ($data['date_from'] ?? ''));
		$dateTo = self::normalizeDateTime((string) ($data['date_to'] ?? ''));

		if ($code === '' || strlen($code) < 3) {
			return self::fail('Geçerli bir kupon kodu girin');
		}

		if (!in_array($type, ['percent', 'fixed'], true)) {
			return self::fail('Geçersiz indirim tipi');
		}

		if ($value <= 0 || ($type === 'percent' && $value > 100)) {
			return self::fail('Geçerli bir indirim değeri girin');
		}

		$exists = DB::getValue(
			'SELECT id_coupon FROM coupons WHERE code = ? AND id_coupon != ? LIMIT 1',
			[$code, $idCoupon]
		);

		if ($exists) {
			return self::fail('Bu kupon kodu zaten kullanılıyor');
		}

		$payload = [
			'code' => $code,
			'discount_type' => $type,
			'discount_value' => $value,
			'min_cart' => $minCart,
			'max_uses' => $maxUses,
			'active' => $active,
			'date_from' => $dateFrom !== '' ? $dateFrom : null,
			'date_to' => $dateTo !== '' ? $dateTo : null,
		];

		if ($idCoupon > 0) {
			$updated = DB::update('coupons', $payload, 'id_coupon = :id_coupon', ['id_coupon' => $idCoupon]);

			if ($updated === false) {
				return self::fail('Kupon güncellenemedi');
			}

			return self::ok('Kupon güncellendi');
		}

		$id = DB::insert('coupons', $payload);

		if (!$id) {
			return self::fail('Kupon oluşturulamadı');
		}

		return self::ok('Kupon oluşturuldu');
	}

	public static function delete(int $idCoupon): array
	{
		if ($idCoupon <= 0) {
			return self::fail('Geçersiz kupon');
		}

		DB::execute('DELETE FROM coupons WHERE id_coupon = ?', [$idCoupon]);

		return self::ok('Kupon silindi');
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

	public static function formatDateTimeInput(?string $value): string
	{
		if ($value === null || $value === '') {
			return '';
		}

		return substr(str_replace(' ', 'T', $value), 0, 16);
	}

	private static function enrichAdmin(array $row): array
	{
		$row['discount_label'] = $row['discount_type'] === 'percent'
			? '%' . (float) $row['discount_value']
			: Tools::displayPrice($row['discount_value']);
		$row['min_cart_formatted'] = Tools::displayPrice($row['min_cart']);
		$row['active'] = (int) $row['active'];
		$row['date_formatted'] = Tools::formatDate3($row['date_add']);

		return $row;
	}

	private static function ok(string $message, float $subtotal = 0.0): array
	{
		$summary = $subtotal > 0 ? self::getCheckoutSummary($subtotal) : [];

		return array_merge([
			'success' => true,
			'message' => $message,
		], $summary);
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
		];
	}
}
