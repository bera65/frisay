<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class NkolaypayModule extends ModuleBase
{
	public string $name = 'nkolaypay';
	public string $title = 'N Kolay Pay';
	public string $version = '1.0.0';
	public string $description = 'N Kolay Pay VPOS v1 ile 3D kart ödemesi';
	public string $author = 'FShop';

	public bool $isPayment = true;
	public bool $paysBeforeOrder = true;
	public string $paymentMethodId = 'nkolaypay';
	public string $paymentMethodLabel = 'Kredi / Banka Kartı (N Kolay Pay)';

	public array $routes = [
		'nkolaypay-payment' => 'front/payment.php',
		'nkolaypay-success' => 'front/success.php',
		'nkolaypay-fail' => 'front/fail.php',
		'nkolaypay-result' => 'front/result.php',
	];

	public array $displayHooks = [
		'order_payment' => 'Checkout ödeme seçeneği',
	];

	public array $defaultDisplayHooks = ['order_payment'];
	public array $frontStylesheets = ['nkolaypay.css'];
	public array $frontScripts = [];

	public function install(): bool
	{
		self::ensurePendingStorage();

		return true;
	}

	public function boot(): void
	{
		self::ensurePendingStorage();
	}

	public function uninstall(): bool
	{
		DB::execute('DROP TABLE IF EXISTS nkolaypay_pending_checkouts');

		return true;
	}

	public function getPaymentPageUrl(): string
	{
		global $domain;

		return rtrim($domain, '/') . '/nkolaypay-payment';
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		$flash = '';

		if (Tools::isSubmit('saveNkolaypay')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('NKOLAYPAY_SX', trim((string) Tools::getValue('sx')));
				Settings::set('NKOLAYPAY_SECRET_KEY', trim((string) Tools::getValue('secret_key')));
				Settings::set('NKOLAYPAY_CUSTOMER_KEY', trim((string) Tools::getValue('customer_key')));
				Settings::set('NKOLAYPAY_TEST_MODE', Tools::getValue('test_mode') ? '1' : '0');
				Settings::set('NKOLAYPAY_FORCE_3D', Tools::getValue('force_3d') ? '1' : '0');
				$flash = 'N Kolay Pay ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'nkolaypaySx' => Settings::get('NKOLAYPAY_SX'),
			'nkolaypaySecretKey' => Settings::get('NKOLAYPAY_SECRET_KEY'),
			'nkolaypayCustomerKey' => Settings::get('NKOLAYPAY_CUSTOMER_KEY'),
			'nkolaypayTestMode' => Settings::get('NKOLAYPAY_TEST_MODE') !== '0',
			'nkolaypayForce3d' => Settings::get('NKOLAYPAY_FORCE_3D') !== '0',
			'nkolaypaySuccessUrl' => rtrim($domain, '/') . '/nkolaypay-success',
			'nkolaypayFailUrl' => rtrim($domain, '/') . '/nkolaypay-fail',
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'order_payment') {
			return null;
		}

		if (!self::isConfigured()) {
			return null;
		}

		$html = $this->renderFrontTemplate('order_payment', []);

		return $html !== '' ? $html : null;
	}

	public static function isConfigured(): bool
	{
		return trim((string) Settings::get('NKOLAYPAY_SX')) !== ''
			&& trim((string) Settings::get('NKOLAYPAY_SECRET_KEY')) !== '';
	}

	public static function getGatewayUrl(): string
	{
		return Settings::get('NKOLAYPAY_TEST_MODE') !== '0'
			? 'https://paynkolaytest.nkolayislem.com.tr/Vpos'
			: 'https://paynkolay.nkolayislem.com.tr/Vpos';
	}

	public static function ensurePendingStorage(): void
	{
		static $ready = false;

		if ($ready) {
			return;
		}

		$ready = true;

		DB::execute(
			'CREATE TABLE IF NOT EXISTS nkolaypay_pending_checkouts (
				reference VARCHAR(32) NOT NULL,
				return_token VARCHAR(64) NOT NULL,
				payload LONGTEXT NOT NULL,
				last_error TEXT NULL,
				date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (reference),
				UNIQUE KEY uniq_return_token (return_token)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
		);

		self::ensurePendingColumns();
	}

	private static function ensurePendingColumns(): void
	{
		static $colsReady = false;

		if ($colsReady) {
			return;
		}

		$colsReady = true;

		$tokenRows = DB::execute("SHOW COLUMNS FROM nkolaypay_pending_checkouts LIKE 'return_token'");

		if (empty($tokenRows)) {
			DB::execute("ALTER TABLE nkolaypay_pending_checkouts ADD COLUMN return_token VARCHAR(64) NOT NULL DEFAULT '' AFTER reference");

			$legacy = DB::execute('SELECT reference FROM nkolaypay_pending_checkouts WHERE return_token = \'\' OR return_token IS NULL');

			if (is_array($legacy)) {
				foreach ($legacy as $legacyRow) {
					$ref = (string) ($legacyRow['reference'] ?? '');

					if ($ref === '') {
						continue;
					}

					DB::execute(
						'UPDATE nkolaypay_pending_checkouts SET return_token = ? WHERE reference = ?',
						[bin2hex(random_bytes(16)), $ref]
					);
				}
			}

			try {
				DB::execute('ALTER TABLE nkolaypay_pending_checkouts ADD UNIQUE KEY uniq_return_token (return_token)');
			} catch (Throwable $e) {
				// index already exists
			}
		}

		$errorRows = DB::execute("SHOW COLUMNS FROM nkolaypay_pending_checkouts LIKE 'last_error'");

		if (empty($errorRows)) {
			DB::execute('ALTER TABLE nkolaypay_pending_checkouts ADD COLUMN last_error TEXT NULL AFTER payload');
		}
	}

	public static function persistPendingCheckout(string $reference, array $checkoutData, array $cart): string
	{
		self::ensurePendingStorage();

		$existing = DB::getRowSafe('nkolaypay_pending_checkouts', 'reference = ?', [$reference]);
		$returnToken = '';

		if ($existing && trim((string) ($existing['return_token'] ?? '')) !== '') {
			$returnToken = (string) $existing['return_token'];
		} else {
			$returnToken = bin2hex(random_bytes(16));
		}

		$payload = json_encode([
			'checkout' => $checkoutData,
			'cart' => $cart,
			'coupon_code' => (string) ($_SESSION[Coupon::SESSION_KEY] ?? ''),
			'id_user' => Customer::getId(),
			'return_token' => $returnToken,
		], JSON_UNESCAPED_UNICODE);

		DB::execute(
			'INSERT INTO nkolaypay_pending_checkouts (reference, return_token, payload, last_error) VALUES (?, ?, ?, NULL)
			 ON DUPLICATE KEY UPDATE return_token = VALUES(return_token), payload = VALUES(payload), date_add = CURRENT_TIMESTAMP',
			[$reference, $returnToken, $payload]
		);

		return $returnToken;
	}

	/** @return array{checkout: array, cart: array, coupon_code: string, id_user: int}|null */
	public static function loadPendingCheckout(string $reference): ?array
	{
		self::ensurePendingStorage();

		$row = DB::getRowSafe('nkolaypay_pending_checkouts', 'reference = ?', [$reference]);

		if (!$row || empty($row['payload'])) {
			return null;
		}

		$data = json_decode((string) $row['payload'], true);

		return is_array($data) ? $data : null;
	}

	public static function deletePendingCheckout(string $reference): void
	{
		self::ensurePendingStorage();
		DB::execute('DELETE FROM nkolaypay_pending_checkouts WHERE reference = ?', [$reference]);
	}

	public static function buildPreviewOrder(array $pendingData, array $cart): array
	{
		$name = trim((string) ($pendingData['customer_name'] ?? ''));
		$phone = Customer::normalizePhone((string) ($pendingData['customer_phone'] ?? ''));
		$customerEmail = strtolower(trim((string) ($pendingData['customer_email'] ?? '')));
		$city = trim((string) ($pendingData['address_city'] ?? ''));
		$district = trim((string) ($pendingData['address_district'] ?? ''));
		$address = trim((string) ($pendingData['address_text'] ?? ''));
		$idUser = Customer::getId();
		$idAddress = (int) ($pendingData['id_address'] ?? 0);

		if ($idAddress > 0 && $idUser > 0) {
			$savedAddress = Address::getForUser($idAddress, $idUser);

			if ($savedAddress) {
				$name = $savedAddress['full_name'];
				$phone = $savedAddress['phone'];
				$city = $savedAddress['city'];
				$district = $savedAddress['district'];
				$address = $savedAddress['address_text'];
			}
		}

		if ($idUser > 0 && $customerEmail === '') {
			$current = Customer::getCurrent();
			$customerEmail = strtolower(trim((string) ($current['email'] ?? '')));
		}

		$subtotal = (float) $cart['total'];
		$summary = Coupon::getCheckoutSummary($subtotal);
		$items = [];

		foreach ($cart['items'] as $item) {
			$lineTotal = (float) $item['line_total'];
			$items[] = [
				'product_name' => (string) $item['product_name'],
				'price' => (float) $item['price'],
				'qty' => (int) $item['qty'],
				'total' => $lineTotal,
				'total_formatted' => Tools::displayPrice($lineTotal),
				'id_product' => (int) ($item['id_product'] ?? 0),
			];
		}

		$reference = (string) ($pendingData['_nkolaypay_reference'] ?? '');

		return [
			'id_order' => 0,
			'reference' => $reference,
			'id_user' => $idUser,
			'customer_name' => $name,
			'customer_phone' => $phone,
			'customer_email' => $customerEmail,
			'address_text' => $address,
			'address_district' => $district,
			'address_city' => $city,
			'items' => $items,
			'subtotal' => (float) $summary['subtotal'],
			'subtotal_formatted' => (string) $summary['subtotal_formatted'],
			'shipping' => (float) $summary['shipping'],
			'shipping_formatted' => (string) $summary['shipping_formatted'],
			'coupon_discount' => (float) $summary['discount'],
			'total' => (float) $summary['total'],
			'total_formatted' => (string) $summary['total_formatted'],
		];
	}

	/** @return array{success: bool, gateway_url?: string, fields?: array<string, string>, message?: string} */
	public function buildGatewayPost(array $order, string $returnToken = ''): array
	{
		if (!self::isConfigured()) {
			return ['success' => false, 'message' => 'N Kolay Pay yapılandırması eksik'];
		}

		global $domain;

		$sx = trim((string) Settings::get('NKOLAYPAY_SX'));
		$secretKey = trim((string) Settings::get('NKOLAYPAY_SECRET_KEY'));
		$customerKey = trim((string) Settings::get('NKOLAYPAY_CUSTOMER_KEY'));
		$reference = (string) $order['reference'];
		$clientRefCode = $reference . '|' . random_int(10000, 99999);
		$amount = self::formatAmount((float) $order['total']);
		$base = rtrim($domain, '/');
		$rtQuery = $returnToken !== '' ? ('?rt=' . rawurlencode($returnToken)) : '';
		$successUrl = $base . '/nkolaypay-success' . $rtQuery;
		$failUrl = $base . '/nkolaypay-fail' . $rtQuery;
		$rnd = date('d-m-Y H:i:s');
		$merchantCustomerNo = 'CUS' . max(0, (int) ($order['id_user'] ?? 0));

		$hashStr = $sx . '|' . $clientRefCode . '|' . $amount . '|' . $successUrl
			. '|' . $failUrl . '|' . $rnd . '|' . $customerKey . '|' . $secretKey;
		$hash = mb_convert_encoding($hashStr, 'UTF-8');
		$hashDataV2 = base64_encode(hash('sha512', $hash, true));

		$use3d = Settings::get('NKOLAYPAY_FORCE_3D') !== '0' ? 'true' : '';

		$postData = [
			'sx' => $sx,
			'clientRefCode' => $clientRefCode,
			'successUrl' => $successUrl,
			'failUrl' => $failUrl,
			'amount' => $amount,
			'installmentNo' => '1',
			'use3D' => $use3d,
			'transactionType' => 'SALES',
			'hashDataV2' => $hashDataV2,
			'rnd' => $rnd,
			'currencyNumber' => '949',
			'MerchantCustomerNo' => $merchantCustomerNo,
		];

		return [
			'success' => true,
			'gateway_url' => self::getGatewayUrl(),
			'fields' => $postData,
		];
	}

	public static function completeOrderAfterPayment(string $reference, float $paidAmount): void
	{
		$order = DB::getRowSafe('orders', 'reference = ?', [$reference]);

		if ($order) {
			self::markOrderPaid($order, $paidAmount);

			return;
		}

		$pending = self::loadPendingCheckout($reference);

		if (!$pending) {
			error_log('NKolayPay: pending checkout not found for ' . $reference);

			return;
		}

		$checkout = is_array($pending['checkout'] ?? null) ? $pending['checkout'] : [];
		$cart = is_array($pending['cart'] ?? null) ? $pending['cart'] : [];

		if ($cart === [] || !empty($cart['empty'])) {
			error_log('NKolayPay: empty cart snapshot for ' . $reference);

			return;
		}

		$checkout['_payment_done'] = 1;
		$checkout['_reference'] = $reference;
		$checkout['_cart_snapshot'] = $cart;
		$checkout['_stored_id_user'] = (int) ($pending['id_user'] ?? 0);
		$checkout['_stored_coupon_code'] = (string) ($pending['coupon_code'] ?? '');

		$result = Order::place($checkout);

		if (!$result['success']) {
			error_log('NKolayPay: order create failed for ' . $reference . ' — ' . ($result['message'] ?? ''));

			return;
		}

		$idOrder = (int) ($result['id_order'] ?? 0);

		if ($idOrder > 0) {
			$created = DB::getRowSafe('orders', 'id_order = ?', [$idOrder]);

			if ($created) {
				self::markOrderPaid($created, $paidAmount);
			}
		}

		self::deletePendingCheckout($reference);
	}

	private static function markOrderPaid(array $order, float $paidAmount): void
	{
		$expected = (float) $order['total'];

		if (abs($paidAmount - $expected) > 0.05) {
			error_log('NKolayPay amount mismatch for order ' . $order['reference']);
		}

		if ((int) $order['status'] !== Order::STATUS_PENDING) {
			return;
		}

		Order::updateStatus((int) $order['id_order'], Order::STATUS_PROCESSING);
	}

	private static function formatAmount(float $amount): string
	{
		return number_format(round(max(0, $amount), 2), 2, '.', '');
	}

	public static function parseAmount(string $amount): float
	{
		$amount = trim($amount);

		if ($amount === '') {
			return 0.0;
		}

		if (strpos($amount, ',') !== false && strpos($amount, '.') !== false) {
			$amount = str_replace('.', '', $amount);
			$amount = str_replace(',', '.', $amount);
		} elseif (strpos($amount, ',') !== false) {
			$amount = str_replace(',', '.', $amount);
		}

		return (float) preg_replace('/[^\d\.\-]/', '', $amount);
	}

	public static function extractReference(string $clientRefCode): string
	{
		$clientRefCode = trim($clientRefCode);

		if ($clientRefCode === '') {
			return '';
		}

		$parts = explode('|', $clientRefCode);
		$first = trim((string) ($parts[0] ?? ''));

		if ($first !== '' && preg_match('/^FS[A-Z0-9]+$/i', $first)) {
			return $first;
		}

		if (preg_match('/(FS[0-9A-Z]{8,})/i', $clientRefCode, $m)) {
			return strtoupper($m[1]);
		}

		return $first;
	}

	/** @param array<string, mixed> $request */
	public static function resolveReturnContext(array $request): array
	{
		$returnToken = trim((string) ($request['rt'] ?? ''));
		$clientRefCode = trim((string) (
			$request['clientRefCode']
			?? $request['ClientRefCode']
			?? $request['CLIENTREFCODE']
			?? ''
		));
		$reference = self::extractReference($clientRefCode);

		if ($returnToken !== '') {
			$row = self::loadPendingByReturnToken($returnToken);

			if ($row) {
				$reference = (string) $row['reference'];
			}
		} elseif ($reference !== '') {
			$row = DB::getRowSafe('nkolaypay_pending_checkouts', 'reference = ?', [$reference]);
			$returnToken = $row ? trim((string) ($row['return_token'] ?? '')) : '';
		}

		$message = trim((string) (
			$request['responseMessage']
			?? $request['ResponseMessage']
			?? $request['RETURN_MESSAGE']
			?? $request['errorMessage']
			?? $request['ErrorMessage']
			?? $request['mdErrorMsg']
			?? ''
		));

		$returnCode = trim((string) (
			$request['returnCode']
			?? $request['ReturnCode']
			?? $request['RETURN_CODE']
			?? ''
		));
		$responseCode = trim((string) (
			$request['responseCode']
			?? $request['ResponseCode']
			?? $request['RESPONSE_CODE']
			?? $request['procReturnCode']
			?? ''
		));

		return [
			'reference' => $reference,
			'return_token' => $returnToken,
			'message' => $message,
			'return_code' => $returnCode,
			'response_code' => $responseCode,
			'amount' => (string) ($request['amount'] ?? $request['Amount'] ?? '0'),
		];
	}

	/** @return array{reference: string, return_token: string, payload: string, last_error?: string}|null */
	public static function loadPendingByReturnToken(string $returnToken): ?array
	{
		self::ensurePendingStorage();
		$returnToken = trim($returnToken);

		if ($returnToken === '') {
			return null;
		}

		$row = DB::getRowSafe('nkolaypay_pending_checkouts', 'return_token = ?', [$returnToken]);

		return $row ?: null;
	}

	public static function saveReturnError(string $reference, string $message): void
	{
		self::ensurePendingStorage();
		$reference = trim($reference);

		if ($reference === '') {
			return;
		}

		DB::execute(
			'UPDATE nkolaypay_pending_checkouts SET last_error = ? WHERE reference = ?',
			[$message, $reference]
		);
	}

	public static function getReturnError(string $reference): string
	{
		self::ensurePendingStorage();
		$reference = trim($reference);

		if ($reference === '') {
			return '';
		}

		return trim((string) DB::getValue(
			'SELECT last_error FROM nkolaypay_pending_checkouts WHERE reference = ? LIMIT 1',
			[$reference]
		));
	}

	public static function getResultUrl(string $returnToken, bool $failed = true): string
	{
		global $domain;

		$url = rtrim($domain, '/') . '/nkolaypay-result?rt=' . rawurlencode($returnToken);

		if ($failed) {
			$url .= '&fail=1';
		}

		return $url;
	}

	public static function restoreCartFromPending(string $reference): void
	{
		$reference = trim($reference);

		if ($reference === '') {
			return;
		}

		$pending = self::loadPendingCheckout($reference);

		if (!$pending) {
			return;
		}

		$checkout = is_array($pending['checkout'] ?? null) ? $pending['checkout'] : [];
		$cart = is_array($pending['cart'] ?? null) ? $pending['cart'] : [];
		$items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

		if ($checkout !== []) {
			if (empty($checkout['_nkolaypay_reference'])) {
				$checkout['_nkolaypay_reference'] = $reference;
			}

			$_SESSION['pending_order_data'] = $checkout;
		}

		if (!empty($pending['coupon_code'])) {
			$_SESSION[Coupon::SESSION_KEY] = (string) $pending['coupon_code'];
		}

		Cart::clear();

		foreach ($items as $item) {
			if (!is_array($item)) {
				continue;
			}

			$idProduct = (int) ($item['id_product'] ?? 0);
			$qty = max(1, (int) ($item['qty'] ?? 1));
			$idVariation = (int) ($item['id_variation'] ?? 0);
			$options = is_array($item['options'] ?? null) ? $item['options'] : [];

			if ($idProduct <= 0) {
				continue;
			}

			Cart::add($idProduct, $qty, $idVariation, $options);
		}
	}

	public static function getClientIp(): string
	{
		foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $header) {
			if (!empty($_SERVER[$header])) {
				$parts = explode(',', (string) $_SERVER[$header]);
				$ip = trim($parts[0]);

				if (filter_var($ip, FILTER_VALIDATE_IP)) {
					return $ip;
				}
			}
		}

		return '127.0.0.1';
	}

	public static function isValidCardNumber(string $number): bool
	{
		if (!preg_match('/^[0-9]{13,19}$/', $number)) {
			return false;
		}

		$sum = 0;
		$alt = false;

		for ($i = strlen($number) - 1; $i >= 0; $i--) {
			$digit = (int) $number[$i];

			if ($alt) {
				$digit *= 2;

				if ($digit > 9) {
					$digit -= 9;
				}
			}

			$sum += $digit;
			$alt = !$alt;
		}

		return $sum % 10 === 0;
	}

	public static function isValidExpiry(int $month, int $year): bool
	{
		if ($month < 1 || $month > 12) {
			return false;
		}

		if ($year < 100) {
			$year += 2000;
		}

		$now = (int) date('Y') * 100 + (int) date('n');

		return ($year * 100 + $month) >= $now;
	}

}
