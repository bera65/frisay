<?php

class SmartCampaignService
{
	public const STATUS_PENDING = 'pending';
	public const STATUS_SENT = 'sent';
	public const STATUS_FAILED = 'failed';
	public const STATUS_SKIPPED = 'skipped';

	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'smart_campaign_rules'");

		if (empty($table)) {
			$file = dirname(__DIR__) . '/install.sql';

			if (!is_file($file)) {
				return;
			}

			$sql = file_get_contents($file);
			$statements = array_filter(array_map('trim', preg_split('/;\s*[\r\n]+/', (string) $sql)));

			foreach ($statements as $statement) {
				if ($statement === '' || strpos($statement, '--') === 0) {
					continue;
				}

				DB::execute($statement);
			}

			return;
		}

		self::migrateSchema();
	}

	private static function migrateSchema(): void
	{
		$col = DB::execute("SHOW COLUMNS FROM `smart_campaign_rules` LIKE 'trigger_status'");

		if (empty($col)) {
			DB::execute(
				'ALTER TABLE `smart_campaign_rules`
				ADD COLUMN `trigger_status` tinyint(2) NOT NULL DEFAULT 0 AFTER `delay_unit`'
			);
		}
	}

	/** @return array<int, string> */
	public static function getTriggerStatusOptions(): array
	{
		return [
			0 => 'Sipariş oluşturulduğunda',
			Order::STATUS_PROCESSING => 'Hazırlanıyor durumuna geçince',
			Order::STATUS_SHIPPED => 'Kargoya verildiğinde',
			Order::STATUS_DELIVERED => 'Teslim edildiğinde',
		];
	}

	public static function getTriggerStatusLabel(int $status): string
	{
		$options = self::getTriggerStatusOptions();

		return $options[$status] ?? 'Bilinmeyen';
	}

	public static function normalizeTriggerStatus(int $status): int
	{
		$allowed = array_keys(self::getTriggerStatusOptions());

		return in_array($status, $allowed, true) ? $status : 0;
	}

	public static function queueForOrder(array $order): void
	{
		self::queueForOrderWithTrigger(
			$order,
			0,
			(string) ($order['date_add'] ?? date('Y-m-d H:i:s'))
		);
	}

	public static function handleOrderStatusChange(array $order, int $oldStatus, int $newStatus): void
	{
		$idOrder = (int) ($order['id_order'] ?? 0);

		if ($idOrder <= 0 || $newStatus === $oldStatus) {
			return;
		}

		if ($newStatus === Order::STATUS_CANCELLED) {
			self::skipPendingForOrder($idOrder, 'Sipariş iptal edildi');

			return;
		}

		if ($newStatus <= 0 || !array_key_exists($newStatus, self::getTriggerStatusOptions())) {
			return;
		}

		self::queueForOrderWithTrigger($order, $newStatus, date('Y-m-d H:i:s'));
	}

	private static function skipPendingForOrder(int $idOrder, string $reason): void
	{
		self::ensureSchema();

		DB::update(
			'smart_campaign_queue',
			[
				'status' => self::STATUS_SKIPPED,
				'error_message' => mb_substr($reason, 0, 250),
			],
			'id_order = :id_order AND status = :status',
			[
				'id_order' => $idOrder,
				'status' => self::STATUS_PENDING,
			]
		);
	}

	private static function queueForOrderWithTrigger(array $order, int $ruleTriggerStatus, string $baseDateTime): void
	{
		self::ensureSchema();

		$idOrder = (int) ($order['id_order'] ?? 0);

		if ($idOrder <= 0) {
			return;
		}

		$email = trim((string) ($order['customer_email'] ?? ''));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return;
		}

		$status = (int) ($order['status'] ?? 0);

		if ($status === Order::STATUS_CANCELLED) {
			return;
		}

		$productIds = [];

		foreach ($order['items'] ?? [] as $item) {
			$idProduct = (int) ($item['id_product'] ?? 0);

			if ($idProduct > 0) {
				$productIds[$idProduct] = (string) ($item['product_name'] ?? '');
			}
		}

		if ($productIds === []) {
			$items = DB::execute(
				'SELECT id_product, product_name FROM order_detail WHERE id_order = ?',
				[$idOrder]
			) ?: [];

			foreach ($items as $item) {
				$idProduct = (int) ($item['id_product'] ?? 0);

				if ($idProduct > 0) {
					$productIds[$idProduct] = (string) ($item['product_name'] ?? '');
				}
			}
		}

		if ($productIds === []) {
			return;
		}

		$rules = DB::execute(
			'SELECT * FROM smart_campaign_rules
			WHERE active = 1
			AND trigger_status = ?
			AND id_product IN (' . implode(',', array_map('intval', array_keys($productIds))) . ')',
			[$ruleTriggerStatus]
		) ?: [];

		if ($rules === []) {
			return;
		}

		$reference = (string) ($order['reference'] ?? '');
		$name = (string) ($order['customer_name'] ?? '');

		foreach ($rules as $rule) {
			$idRule = (int) $rule['id_rule'];
			$idProduct = (int) $rule['id_product'];
			$exists = DB::getValue(
				'SELECT id_queue FROM smart_campaign_queue WHERE id_rule = ? AND id_order = ? LIMIT 1',
				[$idRule, $idOrder]
			);

			if ($exists) {
				continue;
			}

			DB::insert('smart_campaign_queue', [
				'id_rule' => $idRule,
				'id_order' => $idOrder,
				'id_product' => $idProduct,
				'customer_email' => $email,
				'customer_name' => $name,
				'product_name' => $productIds[$idProduct] ?? '',
				'order_reference' => $reference,
				'tracking_code' => self::generateTrackingCode(),
				'send_after' => self::calculateSendAfter($baseDateTime, (int) $rule['delay_amount'], (string) $rule['delay_unit']),
				'status' => self::STATUS_PENDING,
			]);
		}
	}

	public static function calculateSendAfter(string $orderDate, int $amount, string $unit): string
	{
		$amount = max(1, $amount);
		$unit = strtolower(trim($unit));
		$modifier = '+';

		if ($unit === 'hours') {
			$modifier .= $amount . ' hours';
		} elseif ($unit === 'minutes') {
			$modifier .= $amount . ' minutes';
		} else {
			$modifier .= $amount . ' days';
		}

		$ts = strtotime($orderDate . ' ' . $modifier);

		if ($ts === false) {
			$ts = time() + ($amount * 86400);
		}

		return date('Y-m-d H:i:s', $ts);
	}

	public static function processPendingBatch(int $limit = 50): array
	{
		self::ensureSchema();

		$rows = DB::execute(
			'SELECT q.*, r.email_subject, r.email_body, r.target_url
			FROM smart_campaign_queue q
			INNER JOIN smart_campaign_rules r ON r.id_rule = q.id_rule
			WHERE q.status = ? AND q.send_after <= NOW() AND r.active = 1
			ORDER BY q.send_after ASC
			LIMIT ' . (int) $limit,
			[self::STATUS_PENDING]
		) ?: [];

		$sent = 0;
		$failed = 0;
		$skipped = 0;

		foreach ($rows as $row) {
			$result = self::sendQueueItem($row);

			if ($result === self::STATUS_SENT) {
				++$sent;
			} elseif ($result === self::STATUS_SKIPPED) {
				++$skipped;
			} else {
				++$failed;
			}
		}

		return [
			'sent' => $sent,
			'failed' => $failed,
			'skipped' => $skipped,
			'processed' => count($rows),
		];
	}

	public static function sendQueueItem(array $row): string
	{
		$idQueue = (int) ($row['id_queue'] ?? 0);
		$idOrder = (int) ($row['id_order'] ?? 0);
		$order = DB::getRowSafe('orders', 'id_order = ?', [$idOrder]);

		if (!$order || (int) $order['status'] === Order::STATUS_CANCELLED) {
			self::markQueue($idQueue, self::STATUS_SKIPPED, 'Sipariş iptal edilmiş');

			return self::STATUS_SKIPPED;
		}

		$email = trim((string) ($row['customer_email'] ?? ''));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			self::markQueue($idQueue, self::STATUS_FAILED, 'Geçersiz e-posta');

			return self::STATUS_FAILED;
		}

		$trackingCode = (string) ($row['tracking_code'] ?? '');
		$targetUrl = self::appendTrackingToUrl((string) ($row['target_url'] ?? ''), $trackingCode);
		$trackUrl = self::buildTrackUrl($trackingCode);
		$subject = self::replacePlaceholders((string) ($row['email_subject'] ?? ''), $row, $targetUrl, $trackUrl);
		$body = self::replacePlaceholders((string) ($row['email_body'] ?? ''), $row, $targetUrl, $trackUrl);

		if (trim(strip_tags($body)) === '') {
			$body = '<p>Merhaba {customer_name},</p><p><a href="{track_url}">Teklifimizi görüntüleyin</a></p>';
			$body = self::replacePlaceholders($body, $row, $targetUrl, $trackUrl);
		}

		if (strpos($body, '<') === false) {
			$body = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'), false);
		}

		if (!class_exists('Mail', false)) {
			require_once dirname(__DIR__, 3) . '/core/Mail.php';
		}

		$ok = Mail::send($email, $subject, $body);

		if (!$ok) {
			$error = Mail::getLastError() ?: 'E-posta gönderilemedi';
			self::markQueue($idQueue, self::STATUS_FAILED, mb_substr($error, 0, 250));

			return self::STATUS_FAILED;
		}

		DB::update(
			'smart_campaign_queue',
			[
				'status' => self::STATUS_SENT,
				'sent_at' => date('Y-m-d H:i:s'),
				'error_message' => '',
			],
			'id_queue = :id_queue',
			['id_queue' => $idQueue]
		);

		return self::STATUS_SENT;
	}

	private static function markQueue(int $idQueue, string $status, string $error = ''): void
	{
		DB::update(
			'smart_campaign_queue',
			[
				'status' => $status,
				'error_message' => mb_substr($error, 0, 250),
			],
			'id_queue = :id_queue',
			['id_queue' => $idQueue]
		);
	}

	public static function replacePlaceholders(string $text, array $row, string $targetUrl, string $trackUrl): string
	{
		$map = [
			'{customer_name}' => (string) ($row['customer_name'] ?? ''),
			'{customer_email}' => (string) ($row['customer_email'] ?? ''),
			'{product_name}' => (string) ($row['product_name'] ?? ''),
			'{order_reference}' => (string) ($row['order_reference'] ?? ''),
			'{tracking_code}' => (string) ($row['tracking_code'] ?? ''),
			'{target_url}' => $targetUrl,
			'{track_url}' => $trackUrl,
		];

		return str_replace(array_keys($map), array_values($map), $text);
	}

	public static function appendTrackingToUrl(string $url, string $code): string
	{
		$url = trim($url);

		if ($url === '' || $code === '') {
			return $url;
		}

		$separator = strpos($url, '?') !== false ? '&' : '?';

		return $url . $separator . 'sc=' . rawurlencode($code);
	}

	public static function buildTrackUrl(string $code): string
	{
		global $domain;

		return rtrim((string) $domain, '/') . '/api/module.php?m=smart-campaign&action=track&c=' . rawurlencode($code);
	}

	public static function generateTrackingCode(): string
	{
		return bin2hex(random_bytes(8));
	}

	public static function getRuleById(int $idRule): ?array
	{
		self::ensureSchema();

		$row = DB::getRowSafe('smart_campaign_rules', 'id_rule = ?', [$idRule]);

		return $row ?: null;
	}

	/** @return array<int, array<string, mixed>> */
	public static function getRules(bool $activeOnly = false): array
	{
		self::ensureSchema();

		$sql = 'SELECT r.*, p.product_name,
			(SELECT COUNT(*) FROM smart_campaign_queue q WHERE q.id_rule = r.id_rule) AS queue_total,
			(SELECT COUNT(*) FROM smart_campaign_queue q WHERE q.id_rule = r.id_rule AND q.status = \'sent\') AS sent_total,
			(SELECT COUNT(*) FROM smart_campaign_queue q WHERE q.id_rule = r.id_rule AND q.click_count > 0) AS click_total
			FROM smart_campaign_rules r
			LEFT JOIN products p ON p.id_product = r.id_product';

		if ($activeOnly) {
			$sql .= ' WHERE r.active = 1';
		}

		$sql .= ' ORDER BY r.id_rule DESC';

		$rows = DB::execute($sql) ?: [];

		foreach ($rows as &$row) {
			$row['trigger_status_label'] = self::getTriggerStatusLabel((int) ($row['trigger_status'] ?? 0));
		}
		unset($row);

		return $rows;
	}

	public static function saveRule(array $data): array
	{
		self::ensureSchema();

		$idRule = (int) ($data['id_rule'] ?? 0);
		$name = trim((string) ($data['name'] ?? ''));
		$idProduct = (int) ($data['id_product'] ?? 0);
		$delayAmount = max(1, (int) ($data['delay_amount'] ?? 1));
		$delayUnit = strtolower(trim((string) ($data['delay_unit'] ?? 'days')));

		if (!in_array($delayUnit, ['minutes', 'hours', 'days'], true)) {
			$delayUnit = 'days';
		}

		$subject = trim((string) ($data['email_subject'] ?? ''));
		$body = trim((string) ($data['email_body'] ?? ''));
		$targetUrl = trim((string) ($data['target_url'] ?? ''));
		$active = !empty($data['active']) ? 1 : 0;
		$triggerStatus = self::normalizeTriggerStatus((int) ($data['trigger_status'] ?? 0));

		if ($name === '') {
			return ['success' => false, 'message' => 'Kural adı zorunludur'];
		}

		if ($idProduct <= 0) {
			return ['success' => false, 'message' => 'Ürün seçin'];
		}

		if ($subject === '') {
			return ['success' => false, 'message' => 'E-posta konusu zorunludur'];
		}

		if ($targetUrl === '' || !filter_var($targetUrl, FILTER_VALIDATE_URL)) {
			return ['success' => false, 'message' => 'Geçerli bir hedef URL girin'];
		}

		$payload = [
			'name' => mb_substr($name, 0, 128),
			'id_product' => $idProduct,
			'delay_amount' => $delayAmount,
			'delay_unit' => $delayUnit,
			'trigger_status' => $triggerStatus,
			'email_subject' => mb_substr($subject, 0, 255),
			'email_body' => $body,
			'target_url' => mb_substr($targetUrl, 0, 512),
			'active' => $active,
		];

		if ($idRule > 0) {
			DB::update('smart_campaign_rules', $payload, 'id_rule = :id_rule', ['id_rule' => $idRule]);

			return ['success' => true, 'message' => 'Kural güncellendi', 'id_rule' => $idRule];
		}

		$newId = DB::insert('smart_campaign_rules', $payload);

		return [
			'success' => (bool) $newId,
			'message' => $newId ? 'Kural eklendi' : 'Kayıt başarısız',
			'id_rule' => (int) $newId,
		];
	}

	public static function deleteRule(int $idRule): bool
	{
		self::ensureSchema();

		DB::execute(
			'DELETE c FROM smart_campaign_clicks c
			INNER JOIN smart_campaign_queue q ON q.id_queue = c.id_queue
			WHERE q.id_rule = ?',
			[$idRule]
		);
		DB::execute('DELETE FROM smart_campaign_queue WHERE id_rule = ?', [$idRule]);

		return DB::execute('DELETE FROM smart_campaign_rules WHERE id_rule = ? LIMIT 1', [$idRule]) !== false;
	}

	public static function toggleRule(int $idRule): bool
	{
		self::ensureSchema();
		$row = self::getRuleById($idRule);

		if (!$row) {
			return false;
		}

		return DB::update(
			'smart_campaign_rules',
			['active' => (int) $row['active'] === 1 ? 0 : 1],
			'id_rule = :id_rule',
			['id_rule' => $idRule]
		) !== false;
	}

	public static function getQueueList(int $idRule = 0, int $limit = 100): array
	{
		self::ensureSchema();

		$params = [];
		$sql = 'SELECT q.*, r.name AS rule_name
			FROM smart_campaign_queue q
			INNER JOIN smart_campaign_rules r ON r.id_rule = q.id_rule';

		if ($idRule > 0) {
			$sql .= ' WHERE q.id_rule = ?';
			$params[] = $idRule;
		}

		$sql .= ' ORDER BY q.id_queue DESC LIMIT ' . (int) $limit;

		$rows = DB::execute($sql, $params) ?: [];

		foreach ($rows as &$row) {
			$row['target_url_tracked'] = self::appendTrackingToUrl(
				(string) DB::getValue('SELECT target_url FROM smart_campaign_rules WHERE id_rule = ?', [(int) $row['id_rule']]),
				(string) $row['tracking_code']
			);
			$row['track_url'] = self::buildTrackUrl((string) $row['tracking_code']);
		}
		unset($row);

		return $rows;
	}

	public static function getStats(): array
	{
		self::ensureSchema();

		return [
			'rules' => (int) DB::getValue('SELECT COUNT(*) FROM smart_campaign_rules'),
			'pending' => (int) DB::getValue('SELECT COUNT(*) FROM smart_campaign_queue WHERE status = ?', [self::STATUS_PENDING]),
			'sent' => (int) DB::getValue('SELECT COUNT(*) FROM smart_campaign_queue WHERE status = ?', [self::STATUS_SENT]),
			'clicked' => (int) DB::getValue('SELECT COUNT(*) FROM smart_campaign_queue WHERE click_count > 0'),
			'clicks' => (int) DB::getValue('SELECT COUNT(*) FROM smart_campaign_clicks'),
		];
	}

	public static function handleTrackRequest(string $code): void
	{
		self::ensureSchema();

		$code = trim($code);

		if ($code === '') {
			http_response_code(404);
			echo 'Geçersiz bağlantı';
			exit;
		}

		$row = self::fetchRow(
			'SELECT q.*, r.target_url
			FROM smart_campaign_queue q
			INNER JOIN smart_campaign_rules r ON r.id_rule = q.id_rule
			WHERE q.tracking_code = ?
			LIMIT 1',
			[$code]
		);

		if (!$row) {
			http_response_code(404);
			echo 'Kampanya bulunamadı';
			exit;
		}

		self::recordClick((int) $row['id_queue'], $code);

		$target = self::appendTrackingToUrl((string) ($row['target_url'] ?? ''), $code);
		header('Location: ' . $target, true, 302);
		exit;
	}

	public static function recordClickByCode(string $code, bool $requireSent = true): bool
	{
		self::ensureSchema();

		$code = trim($code);

		if ($code === '') {
			return false;
		}

		$sql = 'SELECT id_queue, status FROM smart_campaign_queue WHERE tracking_code = ? LIMIT 1';
		$row = self::fetchRow($sql, [$code]);

		if (!$row) {
			return false;
		}

		if ($requireSent && (string) $row['status'] !== self::STATUS_SENT) {
			return false;
		}

		return self::recordClick((int) $row['id_queue'], $code);
	}

	private static function recordClick(int $idQueue, string $code): bool
	{
		$ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
		$ua = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);

		DB::insert('smart_campaign_clicks', [
			'id_queue' => $idQueue,
			'tracking_code' => $code,
			'ip_address' => $ip,
			'user_agent' => $ua,
		]);

		$row = DB::getRowSafe('smart_campaign_queue', 'id_queue = ?', [$idQueue]);
		$clickCount = (int) ($row['click_count'] ?? 0) + 1;
		$update = [
			'click_count' => $clickCount,
		];

		if (empty($row['first_click_at'])) {
			$update['first_click_at'] = date('Y-m-d H:i:s');
		}

		DB::update('smart_campaign_queue', $update, 'id_queue = :id_queue', ['id_queue' => $idQueue]);

		return true;
	}

	/** @return array<int, array<string, mixed>> */
	public static function getProductOptions(int $limit = 500): array
	{
		return DB::execute(
			'SELECT id_product, product_name FROM products ORDER BY product_name ASC LIMIT ' . (int) $limit
		) ?: [];
	}

	public static function delayUnitLabel(string $unit, int $amount): string
	{
		if ($unit === 'hours') {
			return $amount . ' saat';
		}

		if ($unit === 'minutes') {
			return $amount . ' dakika';
		}

		return $amount . ' gün';
	}

	/** @return array<string, mixed>|null */
	private static function fetchRow(string $sql, array $params = []): ?array
	{
		$rows = DB::execute($sql, $params);

		if (!$rows || !isset($rows[0])) {
			return null;
		}

		return $rows[0];
	}
}
