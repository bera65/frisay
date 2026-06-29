<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class PaytrModule extends ModuleBase
{
	public string $name = 'paytr';
	public string $title = 'PayTR';
	public string $version = '1.0.0';
	public string $description = 'PayTR iFrame ile kredi/banka kartı ödemesi';
	public string $author = 'FShop';

	public bool $isPayment = true;
	public string $paymentMethodId = 'paytr';
	public string $paymentMethodLabel = 'Kredi / Banka Kartı (PayTR)';

	public array $routes = [
		'paytr-payment' => 'front/payment.php',
	];

	public array $displayHooks = [
		'order_payment' => 'Checkout ödeme seçeneği',
	];

	public array $defaultDisplayHooks = ['order_payment'];

	public array $frontStylesheets = ['paytr.css'];

	public array $apiActions = [
		'callback' => 'api/callback.php',
	];

	public function install(): bool
	{
		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		$flash = '';

		if (Tools::isSubmit('savePaytr')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('PAYTR_MERCHANT_ID', trim((string) Tools::getValue('merchant_id')));
				Settings::set('PAYTR_MERCHANT_KEY', trim((string) Tools::getValue('merchant_key')));
				Settings::set('PAYTR_MERCHANT_SALT', trim((string) Tools::getValue('merchant_salt')));
				Settings::set('PAYTR_TEST_MODE', Tools::getValue('test_mode') ? '1' : '0');
				Settings::set('PAYTR_DEBUG', Tools::getValue('debug_on') ? '1' : '0');
				Settings::set('PAYTR_NO_INSTALLMENT', Tools::getValue('no_installment') ? '1' : '0');
				Settings::set('PAYTR_MAX_INSTALLMENT', (string) max(0, (int) Tools::getValue('max_installment')));
				$flash = 'PayTR ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'paytrMerchantId' => Settings::get('PAYTR_MERCHANT_ID'),
			'paytrMerchantKey' => Settings::get('PAYTR_MERCHANT_KEY'),
			'paytrMerchantSalt' => Settings::get('PAYTR_MERCHANT_SALT'),
			'paytrTestMode' => Settings::get('PAYTR_TEST_MODE') === '1',
			'paytrDebug' => Settings::get('PAYTR_DEBUG') === '1',
			'paytrNoInstallment' => Settings::get('PAYTR_NO_INSTALLMENT') !== '0',
			'paytrMaxInstallment' => (int) (Settings::get('PAYTR_MAX_INSTALLMENT') ?: 0),
			'paytrCallbackUrl' => rtrim($domain, '/') . '/api/module.php?m=paytr&action=callback',
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

	public function processPayment(array $order): array
	{
		global $domain;

		$idOrder = (int) ($order['id_order'] ?? 0);

		if ($idOrder <= 0) {
			return [
				'success' => false,
				'redirect' => '',
				'message' => 'Sipariş bulunamadı',
			];
		}

		return [
			'success' => true,
			'redirect' => rtrim($domain, '/') . '/paytr-payment?id=' . $idOrder,
			'message' => '',
		];
	}

	public static function isConfigured(): bool
	{
		return trim((string) Settings::get('PAYTR_MERCHANT_ID')) !== ''
			&& trim((string) Settings::get('PAYTR_MERCHANT_KEY')) !== ''
			&& trim((string) Settings::get('PAYTR_MERCHANT_SALT')) !== '';
	}

	/** @return array{success: bool, token?: string, message?: string} */
	public function getIframeToken(array $order): array
	{
		if (!self::isConfigured()) {
			return ['success' => false, 'message' => 'PayTR yapılandırması eksik'];
		}

		$merchantId = trim((string) Settings::get('PAYTR_MERCHANT_ID'));
		$merchantKey = trim((string) Settings::get('PAYTR_MERCHANT_KEY'));
		$merchantSalt = trim((string) Settings::get('PAYTR_MERCHANT_SALT'));
		$testMode = Settings::get('PAYTR_TEST_MODE') === '1' ? 1 : 0;
		$debugOn = Settings::get('PAYTR_DEBUG') === '1' ? 1 : 0;
		$noInstallment = Settings::get('PAYTR_NO_INSTALLMENT') !== '0' ? 1 : 0;
		$maxInstallment = (int) (Settings::get('PAYTR_MAX_INSTALLMENT') ?: 0);

		global $domain;

		$merchantOid = (string) $order['reference'];
		$email = self::resolveCustomerEmail((int) $order['id_user']);
		$paymentAmount = (int) round((float) $order['total'] * 100);
		$userBasket = self::buildUserBasket($order);
		$userIp = self::getClientIp();
		$currency = 'TL';

		$hashStr = $merchantId . $userIp . $merchantOid . $email . $paymentAmount
			. $userBasket . $noInstallment . $maxInstallment . $currency . $testMode;
		$paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $merchantSalt, $merchantKey, true));

		$postVals = [
			'merchant_id' => $merchantId,
			'user_ip' => $userIp,
			'merchant_oid' => $merchantOid,
			'email' => $email,
			'payment_amount' => $paymentAmount,
			'paytr_token' => $paytrToken,
			'user_basket' => $userBasket,
			'debug_on' => $debugOn,
			'no_installment' => $noInstallment,
			'max_installment' => $maxInstallment,
			'user_name' => (string) $order['customer_name'],
			'user_address' => self::formatAddress($order),
			'user_phone' => (string) $order['customer_phone'],
			'merchant_ok_url' => rtrim($domain, '/') . '/checkout-success?id=' . (int) $order['id_order'],
			'merchant_fail_url' => rtrim($domain, '/') . '/paytr-payment?id=' . (int) $order['id_order'] . '&fail=1',
			'timeout_limit' => '30',
			'currency' => $currency,
			'test_mode' => $testMode,
		];

		$result = self::httpPost('https://www.paytr.com/odeme/api/get-token', $postVals);

		if ($result === null) {
			return ['success' => false, 'message' => 'PayTR bağlantı hatası'];
		}

		$data = json_decode($result, true);

		if (!is_array($data)) {
			return ['success' => false, 'message' => 'PayTR geçersiz yanıt'];
		}

		if (($data['status'] ?? '') === 'success' && !empty($data['token'])) {
			return ['success' => true, 'token' => (string) $data['token']];
		}

		return [
			'success' => false,
			'message' => 'PayTR: ' . (string) ($data['reason'] ?? 'Token alınamadı'),
		];
	}

	public static function handleNotification(array $post): void
	{
		if ($post === []) {
			echo 'OK';
			exit;
		}

		if (!isset($post['merchant_oid'], $post['status'], $post['total_amount'], $post['hash'])) {
			http_response_code(400);
			exit('PAYTR notification failed: missing fields');
		}

		if (!self::isConfigured()) {
			http_response_code(400);
			exit('PAYTR notification failed: not configured');
		}

		$merchantKey = trim((string) Settings::get('PAYTR_MERCHANT_KEY'));
		$merchantSalt = trim((string) Settings::get('PAYTR_MERCHANT_SALT'));

		$hashString = $post['merchant_oid'] . $merchantSalt . $post['status'] . $post['total_amount'];
		$calculatedHash = base64_encode(hash_hmac('sha256', $hashString, $merchantKey, true));

		if (!hash_equals($calculatedHash, (string) $post['hash'])) {
			http_response_code(400);
			exit('PAYTR notification failed: bad hash');
		}

		$order = DB::getRowSafe('orders', 'reference = ?', [(string) $post['merchant_oid']]);

		if (!$order) {
			echo 'OK';
			exit;
		}

		if ((string) $post['status'] === 'success') {
			self::markOrderPaid($order, (int) $post['total_amount']);
		}

		echo 'OK';
		exit;
	}

	private static function markOrderPaid(array $order, int $totalAmountKurus): void
	{
		$expected = (int) round((float) $order['total'] * 100);

		if ($totalAmountKurus !== $expected) {
			error_log('PayTR amount mismatch for order ' . $order['reference']);
		}

		if ((int) $order['status'] !== Order::STATUS_PENDING) {
			return;
		}

		Order::updateStatus((int) $order['id_order'], Order::STATUS_PROCESSING);
	}

	private static function buildUserBasket(array $order): string
	{
		$basket = [];

		foreach ($order['items'] ?? [] as $item) {
			$basket[] = [
				(string) $item['product_name'],
				(float) $item['price'],
				(int) $item['qty'],
			];
		}

		if ((float) ($order['shipping'] ?? 0) > 0) {
			$basket[] = ['Kargo', (float) $order['shipping'], 1];
		}

		if ((float) ($order['coupon_discount'] ?? 0) > 0) {
			$basket[] = ['İndirim', -(float) $order['coupon_discount'], 1];
		}

		if ($basket === []) {
			$basket[] = ['Sipariş', (float) $order['total'], 1];
		}

		return base64_encode(json_encode($basket, JSON_UNESCAPED_UNICODE));
	}

	private static function formatAddress(array $order): string
	{
		$parts = array_filter([
			trim((string) ($order['address_text'] ?? '')),
			trim((string) ($order['address_district'] ?? '')),
			trim((string) ($order['address_city'] ?? '')),
		]);

		return $parts !== [] ? implode(', ', $parts) : 'Türkiye';
	}

	private static function resolveCustomerEmail(int $idUser): string
	{
		$email = trim((string) DB::getValue('SELECT email FROM users WHERE id_user = ? LIMIT 1', [$idUser]));

		if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return $email;
		}

		$domain = parse_url(Settings::get('DOMAIN') ?: '', PHP_URL_HOST) ?: 'fshop.local';

		return 'musteri' . $idUser . '@' . $domain;
	}

	public static function getClientIp(): string
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return (string) $_SERVER['HTTP_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);

			return trim($ips[0]);
		}

		return (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
	}

	/** @param array<string, scalar> $fields */
	private static function httpPost(string $url, array $fields): ?string
	{
		if (!function_exists('curl_init')) {
			return null;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			error_log('PayTR curl error: ' . curl_error($ch));
			curl_close($ch);

			return null;
		}

		curl_close($ch);

		return is_string($result) ? $result : null;
	}
}
