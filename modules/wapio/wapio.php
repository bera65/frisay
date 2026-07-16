<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class WapioModule extends ModuleBase
{
	public string $name = 'wapio';
	public string $title = 'Wapio WhatsApp';
	public string $version = '1.0.0';
	public string $description = 'Sipariş durumu değişince müşteriye WhatsApp mesajı gönderir (Wapio API)';
	public string $author = 'FShop';

	public array $adminStylesheets = ['admin.css'];

	private const API_URL = 'https://my.wapio.com.tr/send-text';
	private const SETTINGS_SESSION = 'WAPIO_SESSION_ID';
	private const SETTINGS_ENABLED = 'WAPIO_ENABLED';

	public function install(): bool
	{
		if (!$this->runSqlFile('install.sql')) {
			return false;
		}

		$this->seedDefaultTemplates();

		return true;
	}

	public function uninstall(): bool
	{
		Settings::set(self::SETTINGS_SESSION, '');
		Settings::set(self::SETTINGS_ENABLED, '0');

		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		$module = $this;

		Module::registerHook('order.placed', function ($order) use ($module): void {
			if (!is_array($order)) {
				return;
			}

			$module->notifyOrderStatus($order, (int) ($order['status'] ?? Order::STATUS_PENDING));
		});

		Module::registerHook('order.updated', function ($order, $oldStatus) use ($module): void {
			if (!is_array($order)) {
				return;
			}

			$newStatus = (int) ($order['status'] ?? 0);
			$oldStatus = (int) $oldStatus;

			if ($newStatus === $oldStatus) {
				return;
			}

			$module->notifyOrderStatus($order, $newStatus);
		});
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'success';
		$view = Tools::getValue('view') === 'report' ? 'report' : 'settings';
		$page = max(1, (int) Tools::getValue('page'));

		if (Tools::isSubmit('saveWapio')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$result = $this->saveSettings($_POST);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		if (Tools::isSubmit('testWapio')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$result = $this->sendTestMessage(
					trim((string) Tools::getValue('test_phone')),
					trim((string) Tools::getValue('test_message'))
				);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		$report = $view === 'report' ? $this->getReport($page, 25) : ['rows' => [], 'total' => 0, 'pages' => 1];

		$smarty->assign([
			'wapioView' => $view,
			'wapioEnabled' => Settings::get(self::SETTINGS_ENABLED) === '1',
			'wapioSessionId' => Settings::get(self::SETTINGS_SESSION),
			'wapioTemplateRows' => $this->getTemplateRows(),
			'wapioPlaceholders' => $this->getPlaceholderHelp(),
			'wapioReport' => $report['rows'],
			'wapioReportTotal' => $report['total'],
			'wapioReportPage' => $page,
			'wapioReportPages' => $report['pages'],
			'flash' => $flash,
			'flashType' => $flashType,
		]);
	}

	/** @return array<int, array{enabled: bool, message: string}> */
	public static function getDefaultTemplates(): array
	{
		return [
			Order::STATUS_PENDING => [
				'enabled' => true,
				'message' => "Merhaba {customer_name},\n\n#{reference} numaralı siparişiniz başarıyla alındı.\nToplam: {total}\n\n{site_name}",
			],
			Order::STATUS_PROCESSING => [
				'enabled' => true,
				'message' => "Merhaba {customer_name},\n\n#{reference} siparişiniz hazırlanıyor. Kısa süre içinde kargoya verilecektir.\n\n{site_name}",
			],
			Order::STATUS_SHIPPED => [
				'enabled' => true,
				'message' => "Merhaba {customer_name},\n\n#{reference} siparişiniz kargoya verildi.\nKargo: {cargo_company}\nTakip no: {tracking_number}\n{tracking_url}\n\n{site_name}",
			],
			Order::STATUS_DELIVERED => [
				'enabled' => true,
				'message' => "Merhaba {customer_name},\n\n#{reference} siparişiniz teslim edildi. Bizi tercih ettiğiniz için teşekkürler!\n\n{site_name}",
			],
			Order::STATUS_CANCELLED => [
				'enabled' => true,
				'message' => "Merhaba {customer_name},\n\n#{reference} numaralı siparişiniz iptal edilmiştir.\n\n{site_name}",
			],
		];
	}

	/** @return array<int, string> */
	private function getStatusOptions(): array
	{
		return [
			Order::STATUS_PENDING => Order::getStatusLabel(Order::STATUS_PENDING),
			Order::STATUS_PROCESSING => Order::getStatusLabel(Order::STATUS_PROCESSING),
			Order::STATUS_SHIPPED => Order::getStatusLabel(Order::STATUS_SHIPPED),
			Order::STATUS_DELIVERED => Order::getStatusLabel(Order::STATUS_DELIVERED),
			Order::STATUS_CANCELLED => Order::getStatusLabel(Order::STATUS_CANCELLED),
		];
	}

	/** @return string[] */
	private function getPlaceholderHelp(): array
	{
		return [
			'{customer_name}', '{reference}', '{total}', '{status_label}',
			'{cargo_company}', '{tracking_number}', '{tracking_url}', '{site_name}',
		];
	}

	/** @return array<int, array{enabled: bool, message: string}> */
	public function getTemplates(): array
	{
		$this->ensureSchema();

		$defaults = self::getDefaultTemplates();
		$rows = DB::execute('SELECT order_status, enabled, message FROM wapio_templates') ?: [];

		foreach ($rows as $row) {
			$status = (int) ($row['order_status'] ?? 0);

			if (!isset($defaults[$status])) {
				continue;
			}

			$defaults[$status] = [
				'enabled' => (int) ($row['enabled'] ?? 0) === 1,
				'message' => trim((string) ($row['message'] ?? $defaults[$status]['message'])),
			];
		}

		return $defaults;
	}

	/** @return array<int, array{status_id: int, status_label: string, enabled: bool, message: string}> */
	private function getTemplateRows(): array
	{
		$templates = $this->getTemplates();
		$rows = [];

		foreach ($this->getStatusOptions() as $statusId => $label) {
			$tpl = $templates[$statusId] ?? ['enabled' => false, 'message' => ''];
			$rows[] = [
				'status_id' => (int) $statusId,
				'status_label' => $label,
				'enabled' => !empty($tpl['enabled']),
				'message' => (string) ($tpl['message'] ?? ''),
			];
		}

		return $rows;
	}

	private function seedDefaultTemplates(): void
	{
		foreach (self::getDefaultTemplates() as $status => $tpl) {
			$exists = (int) DB::getValue(
				'SELECT COUNT(*) FROM wapio_templates WHERE order_status = ? LIMIT 1',
				[(int) $status]
			);

			if ($exists > 0) {
				continue;
			}

			DB::insert('wapio_templates', [
				'order_status' => (int) $status,
				'enabled' => !empty($tpl['enabled']) ? 1 : 0,
				'message' => (string) $tpl['message'],
			]);
		}
	}

	/** @param array<string, mixed> $post */
	private function saveSettings(array $post): array
	{
		$this->ensureSchema();

		$enabled = !empty($post['wapio_enabled']) ? '1' : '0';
		$sessionId = trim((string) ($post['wapio_session_id'] ?? ''));
		$incoming = is_array($post['templates'] ?? null) ? $post['templates'] : [];
		$defaults = self::getDefaultTemplates();

		foreach ($defaults as $status => $default) {
			$row = is_array($incoming[$status] ?? null) ? $incoming[$status] : [];
			$message = trim((string) ($row['message'] ?? $default['message']));
			$isEnabled = !empty($row['enabled']) ? 1 : 0;
			$status = (int) $status;

			$exists = (int) DB::getValue(
				'SELECT COUNT(*) FROM wapio_templates WHERE order_status = ? LIMIT 1',
				[$status]
			);

			if ($exists > 0) {
				DB::update(
					'wapio_templates',
					[
						'enabled' => $isEnabled,
						'message' => $message,
					],
					'order_status = :where_status',
					['where_status' => $status]
				);
			} else {
				DB::insert('wapio_templates', [
					'order_status' => $status,
					'enabled' => $isEnabled,
					'message' => $message,
				]);
			}
		}

		Settings::set(self::SETTINGS_ENABLED, $enabled);
		Settings::set(self::SETTINGS_SESSION, $sessionId);

		return ['success' => true, 'message' => 'Wapio ayarları kaydedildi'];
	}

	public function notifyOrderStatus(array $order, int $status): void
	{
		if (Settings::get(self::SETTINGS_ENABLED) !== '1') {
			return;
		}

		$idOrder = (int) ($order['id_order'] ?? 0);
		$status = (int) $status;

		if ($idOrder <= 0 || $status <= 0) {
			return;
		}

		$templates = $this->getTemplates();

		if (empty($templates[$status]['enabled'])) {
			return;
		}

		$template = trim((string) ($templates[$status]['message'] ?? ''));

		if ($template === '') {
			return;
		}

		if ($this->wasAlreadySent($idOrder, $status)) {
			return;
		}

		$phone = $this->normalizePhone((string) ($order['customer_phone'] ?? ''));

		if ($phone === '') {
			$this->writeLog([
				'id_order' => $idOrder,
				'order_reference' => (string) ($order['reference'] ?? ''),
				'customer_phone' => (string) ($order['customer_phone'] ?? ''),
				'order_status' => $status,
				'message' => '',
				'api_status' => 'error',
				'api_message' => 'Geçersiz telefon numarası',
				'message_id' => '',
				'success' => 0,
			]);

			return;
		}

		$message = $this->renderTemplate($template, $order, $status);
		$result = $this->sendText($phone, $message);

		$this->writeLog([
			'id_order' => $idOrder,
			'order_reference' => (string) ($order['reference'] ?? ''),
			'customer_phone' => $phone,
			'order_status' => $status,
			'message' => $message,
			'api_status' => (string) ($result['status'] ?? 'error'),
			'api_message' => (string) ($result['message'] ?? ''),
			'message_id' => (string) ($result['messageId'] ?? ''),
			'success' => !empty($result['success']) ? 1 : 0,
		]);
	}

	private function wasAlreadySent(int $idOrder, int $status): bool
	{
		$count = (int) DB::getValue(
			'SELECT COUNT(*) FROM wapio_log WHERE id_order = ? AND order_status = ? AND success = 1 LIMIT 1',
			[$idOrder, $status]
		);

		return $count > 0;
	}

	private function renderTemplate(string $template, array $order, int $status): string
	{
		$trackingNumber = trim((string) ($order['tracking_number'] ?? ''));
		$cargoCompany = trim((string) ($order['cargo_company'] ?? ''));
		$trackingUrl = $trackingNumber !== '' ? $this->buildTrackingUrl($trackingNumber, $cargoCompany) : '';

		$replace = [
			'{customer_name}' => trim((string) ($order['customer_name'] ?? '')),
			'{reference}' => (string) ($order['reference'] ?? ''),
			'{total}' => Tools::displayPrice((float) ($order['total'] ?? 0)),
			'{status_label}' => Order::getStatusLabel($status),
			'{cargo_company}' => $cargoCompany,
			'{tracking_number}' => $trackingNumber,
			'{tracking_url}' => $trackingUrl,
			'{site_name}' => trim((string) Settings::get('SITE_NAME')) ?: 'Mağazamız',
		];

		return strtr($template, $replace);
	}

	private function buildTrackingUrl(string $trackingNumber, string $cargoCompany): string
	{
		if (class_exists('Cargo')) {
			$url = Cargo::buildTrackingUrl($trackingNumber, $cargoCompany);

			if ($url !== '') {
				return $url;
			}
		}

		return '';
	}

	public function normalizePhone(string $phone): string
	{
		$digits = preg_replace('/\D+/', '', $phone) ?: '';

		if ($digits === '') {
			return '';
		}

		if (strpos($digits, '90') === 0 && strlen($digits) === 12) {
			return $digits;
		}

		if (strpos($digits, '0') === 0 && strlen($digits) === 11) {
			return '90' . substr($digits, 1);
		}

		if (strpos($digits, '5') === 0 && strlen($digits) === 10) {
			return '90' . $digits;
		}

		if (strlen($digits) >= 10 && strlen($digits) <= 15) {
			return $digits;
		}

		return '';
	}

	/** @return array{success: bool, status?: string, message?: string, messageId?: string, http_code?: int} */
	public function sendText(string $phone, string $message): array
	{
		$sessionId = trim((string) Settings::get(self::SETTINGS_SESSION));

		if ($sessionId === '') {
			return ['success' => false, 'status' => 'error', 'message' => 'Session ID tanımlı değil'];
		}

		if ($message === '') {
			return ['success' => false, 'status' => 'error', 'message' => 'Mesaj boş'];
		}

		$payload = json_encode([
			'phone' => $phone,
			'is_group' => false,
			'is_channel' => false,
			'data' => [
				'message' => $message,
				'messageId' => '',
			],
		], JSON_UNESCAPED_UNICODE);

		if ($payload === false) {
			return ['success' => false, 'status' => 'error', 'message' => 'JSON oluşturulamadı'];
		}

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => self::API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_HTTPHEADER => [
				'session_id: ' . $sessionId,
				'Content-Type: application/json',
			],
		]);

		$response = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if ($curlError !== '') {
			return [
				'success' => false,
				'status' => 'error',
				'message' => 'cURL: ' . $curlError,
				'http_code' => $httpCode,
			];
		}

		$decoded = json_decode((string) $response, true);
		$apiStatus = is_array($decoded) ? (string) ($decoded['status'] ?? '') : '';
		$apiMessage = is_array($decoded) ? (string) ($decoded['message'] ?? $response) : (string) $response;
		$messageId = is_array($decoded) ? (string) ($decoded['messageId'] ?? '') : '';
		$success = $httpCode >= 200 && $httpCode < 300 && $apiStatus === 'success';

		return [
			'success' => $success,
			'status' => $apiStatus !== '' ? $apiStatus : (string) $httpCode,
			'message' => $apiMessage,
			'messageId' => $messageId,
			'http_code' => $httpCode,
		];
	}

	/** @return array{success: bool, message: string} */
	private function sendTestMessage(string $phone, string $message): array
	{
		if ($phone === '') {
			return ['success' => false, 'message' => 'Test telefon numarası girin'];
		}

		if ($message === '') {
			$message = 'Wapio test mesajı — ' . (Settings::get('SITE_NAME') ?: 'FShop');
		}

		$phone = $this->normalizePhone($phone);

		if ($phone === '') {
			return ['success' => false, 'message' => 'Geçersiz telefon formatı (ör. 05551234567)'];
		}

		$result = $this->sendText($phone, $message);

		$this->writeLog([
			'id_order' => 0,
			'order_reference' => 'TEST',
			'customer_phone' => $phone,
			'order_status' => 0,
			'message' => $message,
			'api_status' => (string) ($result['status'] ?? 'error'),
			'api_message' => (string) ($result['message'] ?? ''),
			'message_id' => (string) ($result['messageId'] ?? ''),
			'success' => !empty($result['success']) ? 1 : 0,
		]);

		if (!empty($result['success'])) {
			return ['success' => true, 'message' => 'Test mesajı gönderildi'];
		}

		return ['success' => false, 'message' => 'Gönderilemedi: ' . ($result['message'] ?? 'Bilinmeyen hata')];
	}

	/** @param array<string, mixed> $row */
	private function writeLog(array $row): void
	{
		$this->ensureSchema();

		DB::insert('wapio_log', [
			'id_order' => (int) ($row['id_order'] ?? 0),
			'order_reference' => mb_substr((string) ($row['order_reference'] ?? ''), 0, 32),
			'customer_phone' => mb_substr((string) ($row['customer_phone'] ?? ''), 0, 32),
			'order_status' => (int) ($row['order_status'] ?? 0),
			'message' => (string) ($row['message'] ?? ''),
			'api_status' => mb_substr((string) ($row['api_status'] ?? ''), 0, 32),
			'api_message' => mb_substr((string) ($row['api_message'] ?? ''), 0, 512),
			'message_id' => mb_substr((string) ($row['message_id'] ?? ''), 0, 128),
			'success' => (int) ($row['success'] ?? 0),
		]);
	}

	/** @return array{rows: array<int, array<string, mixed>>, total: int, pages: int} */
	private function getReport(int $page, int $perPage): array
	{
		$this->ensureSchema();

		$perPage = max(10, min(100, $perPage));
		$page = max(1, $page);
		$offset = ($page - 1) * $perPage;

		$total = (int) DB::getValue('SELECT COUNT(*) FROM wapio_log');
		$rows = DB::execute(
			'SELECT * FROM wapio_log ORDER BY id_log DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
		) ?: [];

		$statusLabels = $this->getStatusOptions();

		foreach ($rows as &$row) {
			$status = (int) ($row['order_status'] ?? 0);
			$row['status_label'] = $status > 0 ? ($statusLabels[$status] ?? '-') : 'Test';
			$row['date_formatted'] = Tools::formatDate3((string) ($row['date_add'] ?? ''));
			$row['message_short'] = Tools::strlen((string) ($row['message'] ?? '')) > 120
				? mb_substr((string) $row['message'], 0, 117, 'UTF-8') . '...'
				: (string) ($row['message'] ?? '');
		}
		unset($row);

		$pages = max(1, (int) ceil($total / $perPage));

		return [
			'rows' => $rows,
			'total' => $total,
			'pages' => $pages,
		];
	}

	private function ensureSchema(): void
	{
		static $ready = false;

		if ($ready) {
			return;
		}

		$ready = true;

		$needsInstall = empty(DB::execute("SHOW TABLES LIKE 'wapio_log'"))
			|| empty(DB::execute("SHOW TABLES LIKE 'wapio_templates'"));

		if ($needsInstall) {
			$this->runSqlFile('install.sql');
			$this->seedDefaultTemplates();
		}
	}
}
