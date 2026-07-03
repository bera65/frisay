<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class BasitKargoModule extends ModuleBase
{
	public string $name = 'basitkargo';
	public string $title = 'Basit Kargo';
	public string $version = '1.0.0';
	public string $description = 'Basitkargo ile kargo gönder';
	public string $author = 'FShop';

	public array $displayHooks = [
		'admin_order_detail' => 'Sipariş detayında ek panel',
	];
	public array $defaultDisplayHooks = ['admin_order_detail'];

	public array $apiActions = [
		'webhook' => 'api/webhook.php',
	];

	public function install(): bool
	{
		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		$module = $this;

		Module::registerHook('order.placed', function ($order) use ($module): void {
			if (!is_array($order)) {
				return;
			}

			$module->submitCargo((int) ($order['id_order'] ?? 0));
		});

		Module::registerHook('order.updated', function ($order, $oldStatus) use ($module): void {
			if (!is_array($order)) {
				return;
			}

			$newStatus = (int) ($order['status'] ?? 0);
			$oldStatus = (int) $oldStatus;

			if ($newStatus !== Order::STATUS_PROCESSING || $oldStatus === Order::STATUS_PROCESSING) {
				return;
			}

			$module->submitCargo((int) ($order['id_order'] ?? 0));
		});
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_order_detail') {
			return null;
		}

		$idOrder = (int) ($context['id_order'] ?? Tools::getValue('id'));
		$order = is_array($context['order'] ?? null) ? $context['order'] : null;
		$cargoRow = $this->getCargoRow($idOrder);
		$preview = $idOrder > 0 ? $this->buildCargoPayload($idOrder) : null;

		$html = $this->renderAdminTemplate('admin_order_detail', [
			'id_order' => $idOrder,
			'order' => $order,
			'cargoRow' => $cargoRow,
			'cargoPreview' => is_array($preview) ? $preview : null,
			'cargoPreviewJson' => is_array($preview)
				? json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
				: '',
			'cargoError' => is_string($preview) ? $preview : '',
			'hasToken' => trim((string) Settings::get('BASITKARGO_TOKEN')) !== '',
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;
		$flash = '';
		$sonuc = '';

		if (Tools::isSubmit('testOrder')) {
			$postToken = (string) Tools::getValue('testOrder');

			if (hash_equals($adminToken, $postToken)) {
				$siparisVerisi = [
					'type' => 'OUTGOING',
					'content' => [
						'name' => 'Test Sipariş',
						'code' => '#123456',
						'items' => [
							['name' => 'Test Ürünü', 'code' => 'STK32', 'quantity' => '1'],
						],
						'packages' => [
							['height' => 10, 'width' => 15, 'depth' => 5, 'weight' => 1],
						],
					],
					'client' => [
						'name' => 'Test Alıcı',
						'phone' => '5555555555',
						'city' => 'İstanbul',
						'town' => 'Kadıköy',
						'address' => 'Koşuyolu Mah.',
					],
					'collect' => 100,
					'collectOnDeliveryType' => 'CASH',
				];

				$sonuc = $this->basitKargoApi('order', 'POST', $siparisVerisi);
				if (($sonuc['http_code'] ?? 0) === 200) {
					$flash = 'Test kargosu başarılı';
				}
			} else {
				$flash = 'Test sipariş oluşturulamadı';
			}
		}

		if (Tools::isSubmit('saveKargo')) {
			$postToken = (string) Tools::getValue('saveKargo');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BASITKARGO_TOKEN', trim((string) Tools::getValue('basitKargoToken')));
				Settings::set('BASITKARGO_LINK_TOKEN', trim((string) Tools::getValue('basitLinkToken')));
				$flash = 'Kargo token bilgisi kayıt edildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'basitKargoToken' => Settings::get('BASITKARGO_TOKEN'),
			'basitLinkToken' => Settings::get('BASITKARGO_LINK_TOKEN'),
			'flash' => $flash,
			'sonuc' => json_encode($sonuc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		]);
	}

	public function basitKargoApi($action, $type = 'GET', $data = null)
	{
		$baseUrl = 'https://basitkargo.com/api';
		$url = $baseUrl;

		if ($action === 'order') {
			$url = $baseUrl . '/v2/order';
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . Settings::get('BASITKARGO_TOKEN'),
		];

		if (strtoupper($type) === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			if ($data !== null) {
				$payload = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			}
		} elseif (strtoupper($type) !== 'GET') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($type));
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_errno($ch)) {
			$errorMsg = curl_error($ch);
			curl_close($ch);

			return [
				'success' => false,
				'message' => 'cURL Hatası: ' . $errorMsg,
			];
		}

		curl_close($ch);

		return [
			'http_code' => $httpCode,
			'response' => json_decode($response, true),
		];
	}

	/** Sipariş oluşunca / onaylanınca API'ye gönderir. */
	public function submitCargo(int $idOrder): ?string
	{
		$idOrder = (int) $idOrder;

		if ($idOrder <= 0) {
			return 'Geçersiz sipariş';
		}

		if (trim((string) Settings::get('BASITKARGO_TOKEN')) === '') {
			return 'Basit Kargo token tanımlı değil';
		}

		if ($this->getCargoRow($idOrder)) {
			return null;
		}

		$payload = $this->buildCargoPayload($idOrder);

		if (!is_array($payload)) {
			return is_string($payload) ? $payload : 'Kargo verisi oluşturulamadı';
		}

		$result = $this->basitKargoApi('order', 'POST', $payload);
		$httpCode = (int) ($result['http_code'] ?? 0);
		$response = is_array($result['response'] ?? null) ? $result['response'] : [];
		$cargoCode = (string) ($response['id'] ?? $response['code'] ?? '');

		if ($httpCode < 200 || $httpCode >= 300 || $cargoCode === '') {
			return 'Basit Kargo API hatası (HTTP ' . $httpCode . ')';
		}

		DB::insert('basitkargo', [
			'id_order' => $idOrder,
			'cargo_code' => mb_substr($cargoCode, 0, 128),
			'cargo' => mb_substr((string) ($response['carrier'] ?? ''), 0, 128),
		]);

		return null;
	}

	/** @return array<string, mixed>|null */
	private function getCargoRow(int $idOrder): ?array
	{
		if ($idOrder <= 0) {
			return null;
		}

		$row = DB::execute('SELECT * FROM basitkargo WHERE id_order = ? LIMIT 1', [$idOrder]);

		return is_array($row[0] ?? null) ? $row[0] : null;
	}

	/**
	 * API gövdesi (önizleme) veya hata metni — API çağırmaz.
	 * @return array<string, mixed>|string
	 */
	public function buildCargoPayload(int $idOrder)
	{
		$idOrder = (int) $idOrder;

		if ($idOrder <= 0) {
			return 'Geçersiz sipariş';
		}

		if ($this->getCargoRow($idOrder)) {
			return 'Bu sipariş Basit Kargo paneline aktarılmış';
		}

		$orderRows = DB::execute('SELECT * FROM orders WHERE id_order = ? LIMIT 1', [$idOrder]) ?: [];
		$order = $orderRows[0] ?? null;

		if (!$order) {
			return 'Sipariş bulunamadı';
		}

		$details = DB::execute(
			'SELECT od.*, p.stock_code
			 FROM order_detail od
			 LEFT JOIN products p ON p.id_product = od.id_product
			 WHERE od.id_order = ?',
			[$idOrder]
		) ?: [];
		$items = [];

		foreach ($details as $row) {
			$stockCode = trim((string) ($row['stock_code'] ?? ''));

			$items[] = [
				'name' => (string) ($row['product_name'] ?? ''),
				'code' => $stockCode !== '' ? $stockCode : 'SK' . (int) ($row['id_product'] ?? 0),
				'quantity' => (string) (int) ($row['qty'] ?? 1),
			];
		}

		$siteName = trim((string) Settings::get('SITE_NAME'));

		if ($siteName === '') {
			$siteName = 'Mağaza';
		}

		return [
			'type' => 'OUTGOING',
			'content' => [
				'name' => $siteName . ' sipariş',
				'code' => (string) ($order['reference'] ?? ''),
				'items' => $items,
				'packages' => [
					['height' => 10, 'width' => 10, 'depth' => 10, 'weight' => 1],
				],
			],
			'client' => [
				'name' => (string) ($order['customer_name'] ?? ''),
				'phone' => (string) ($order['customer_phone'] ?? ''),
				'city' => (string) ($order['address_city'] ?? ''),
				'town' => (string) ($order['address_district'] ?? ''),
				'address' => (string) ($order['address_text'] ?? ''),
			],
			'collect' => 0,
			'collectOnDeliveryType' => 'CASH',
		];
	}
}
