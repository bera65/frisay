<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class ReviewsInviteService
{
	const STATUS_PENDING = 'pending';
	const STATUS_SENT = 'sent';
	const STATUS_FAILED = 'failed';
	const STATUS_SKIPPED = 'skipped';

	const SETTING_ENABLED = 'REVIEWS_INVITE_ENABLED';
	const SETTING_DELAY_DAYS = 'REVIEWS_INVITE_DELAY_DAYS';
	const SETTING_SUBJECT = 'REVIEWS_INVITE_SUBJECT';
	const SETTING_BODY = 'REVIEWS_INVITE_BODY';
	const SETTING_COUPON_ENABLED = 'REVIEWS_INVITE_COUPON_ENABLED';
	const SETTING_COUPON_TYPE = 'REVIEWS_INVITE_COUPON_TYPE';
	const SETTING_COUPON_VALUE = 'REVIEWS_INVITE_COUPON_VALUE';
	const SETTING_COUPON_MIN_CART = 'REVIEWS_INVITE_COUPON_MIN_CART';
	const SETTING_COUPON_VALID_DAYS = 'REVIEWS_INVITE_COUPON_VALID_DAYS';
	const SETTING_COUPON_PREFIX = 'REVIEWS_INVITE_COUPON_PREFIX';

	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'review_invite_queue'");

		if (!empty($table)) {
			return;
		}

		DB::execute(
			"CREATE TABLE `review_invite_queue` (
				`id_queue` int(11) NOT NULL AUTO_INCREMENT,
				`id_order` int(11) NOT NULL,
				`id_user` int(11) NOT NULL DEFAULT 0,
				`customer_email` varchar(255) NOT NULL DEFAULT '',
				`customer_name` varchar(255) NOT NULL DEFAULT '',
				`order_reference` varchar(64) NOT NULL DEFAULT '',
				`scheduled_at` datetime NOT NULL,
				`status` varchar(16) NOT NULL DEFAULT 'pending',
				`coupon_code` varchar(32) NOT NULL DEFAULT '',
				`error_message` varchar(255) NOT NULL DEFAULT '',
				`sent_at` datetime DEFAULT NULL,
				`date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id_queue`),
				UNIQUE KEY `id_order` (`id_order`),
				KEY `status_scheduled` (`status`, `scheduled_at`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);
	}

	public static function ensureDefaultSettings(): void
	{
		$defaults = self::getDefaultSettings();

		foreach ($defaults as $key => $value) {
			$current = Settings::get($key);
			if ($current === null || $current === '') {
				Settings::set($key, $value);
			}
		}
	}

	public static function getDefaultSettings(): array
	{
		return [
			self::SETTING_ENABLED => '0',
			self::SETTING_DELAY_DAYS => '7',
			self::SETTING_SUBJECT => 'Satın aldığınız ürünler hakkında yorum yapın',
			self::SETTING_BODY => self::getDefaultBody(),
			self::SETTING_COUPON_ENABLED => '0',
			self::SETTING_COUPON_TYPE => 'percent',
			self::SETTING_COUPON_VALUE => '5',
			self::SETTING_COUPON_MIN_CART => '0',
			self::SETTING_COUPON_VALID_DAYS => '30',
			self::SETTING_COUPON_PREFIX => 'RVW',
		];
	}

	public static function getDefaultBody(): string
	{
		return '<p>Merhaba {customer_name},</p>'
			. '<p>{order_reference} numaralı siparişiniz teslim edildi. Deneyiminizi paylaşır mısınız?</p>'
			. '<p>{products_list}</p>'
			. '<p>{coupon_info}</p>'
			. '<p>Teşekkürler,<br>{site_name}</p>';
	}

	public static function getSettings(): array
	{
		self::ensureDefaultSettings();

		return [
			'enabled' => Settings::get(self::SETTING_ENABLED) === '1',
			'delay_days' => max(0, (int) Settings::get(self::SETTING_DELAY_DAYS)),
			'subject' => (string) Settings::get(self::SETTING_SUBJECT),
			'body' => (string) Settings::get(self::SETTING_BODY),
			'coupon_enabled' => Settings::get(self::SETTING_COUPON_ENABLED) === '1',
			'coupon_type' => ((string) Settings::get(self::SETTING_COUPON_TYPE) === 'fixed') ? 'fixed' : 'percent',
			'coupon_value' => (float) Settings::get(self::SETTING_COUPON_VALUE),
			'coupon_min_cart' => (float) Settings::get(self::SETTING_COUPON_MIN_CART),
			'coupon_valid_days' => max(0, (int) Settings::get(self::SETTING_COUPON_VALID_DAYS)),
			'coupon_prefix' => (string) Settings::get(self::SETTING_COUPON_PREFIX) ?: 'RVW',
		];
	}

	public static function saveSettings(array $data): array
	{
		$enabled = !empty($data['enabled']) ? '1' : '0';
		$delayDays = max(0, min(365, (int) ($data['delay_days'] ?? 7)));
		$subject = trim(strip_tags((string) ($data['subject'] ?? '')));
		$body = trim((string) ($data['body'] ?? ''));
		$couponEnabled = !empty($data['coupon_enabled']) ? '1' : '0';
		$couponType = ((string) ($data['coupon_type'] ?? 'percent') === 'fixed') ? 'fixed' : 'percent';
		$couponValue = (float) ($data['coupon_value'] ?? 5);
		$couponMinCart = max(0.0, (float) ($data['coupon_min_cart'] ?? 0));
		$couponValidDays = max(0, min(365, (int) ($data['coupon_valid_days'] ?? 30)));
		$couponPrefix = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) ($data['coupon_prefix'] ?? 'RVW'))) ?: 'RVW';

		if ($subject === '') {
			return ['success' => false, 'message' => 'E-posta konusu gerekli'];
		}

		if (trim(strip_tags($body)) === '') {
			return ['success' => false, 'message' => 'E-posta metni gerekli'];
		}

		if ($couponEnabled === '1') {
			if ($couponValue <= 0 || ($couponType === 'percent' && $couponValue > 100)) {
				return ['success' => false, 'message' => 'Geçerli bir kupon değeri girin'];
			}
		}

		Settings::set(self::SETTING_ENABLED, $enabled);
		Settings::set(self::SETTING_DELAY_DAYS, (string) $delayDays);
		Settings::set(self::SETTING_SUBJECT, mb_substr($subject, 0, 255));
		Settings::set(self::SETTING_BODY, $body);
		Settings::set(self::SETTING_COUPON_ENABLED, $couponEnabled);
		Settings::set(self::SETTING_COUPON_TYPE, $couponType);
		Settings::set(self::SETTING_COUPON_VALUE, (string) $couponValue);
		Settings::set(self::SETTING_COUPON_MIN_CART, (string) $couponMinCart);
		Settings::set(self::SETTING_COUPON_VALID_DAYS, (string) $couponValidDays);
		Settings::set(self::SETTING_COUPON_PREFIX, $couponPrefix);

		return ['success' => true, 'message' => 'Yorum davet ayarları kaydedildi'];
	}

	public static function handleOrderStatusChange(array $order, int $oldStatus, int $newStatus): void
	{
		self::ensureSchema();

		$idOrder = (int) ($order['id_order'] ?? 0);

		if ($idOrder <= 0 || $newStatus === $oldStatus) {
			return;
		}

		if ($newStatus === Order::STATUS_CANCELLED || $newStatus === Order::STATUS_RETURNED) {
			self::skipPendingForOrder($idOrder, 'Sipariş iptal/iade edildi');

			return;
		}

		if ($newStatus !== Order::STATUS_DELIVERED) {
			return;
		}

		$settings = self::getSettings();

		if (!$settings['enabled']) {
			return;
		}

		self::queueForOrder($order, $settings);
	}

	public static function queueForOrder(array $order, ?array $settings = null): void
	{
		self::ensureSchema();

		$settings = $settings ?? self::getSettings();
		$idOrder = (int) ($order['id_order'] ?? 0);

		if ($idOrder <= 0 || !$settings['enabled']) {
			return;
		}

		$email = trim((string) ($order['customer_email'] ?? ''));
		$idUser = (int) ($order['id_user'] ?? 0);

		if ($email === '' && $idUser > 0) {
			$user = DB::getRowSafe('users', 'id_user = ?', [$idUser]);
			$email = trim((string) ($user['email'] ?? ''));
		}

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return;
		}

		$exists = DB::getValue(
			'SELECT id_queue FROM review_invite_queue WHERE id_order = ? LIMIT 1',
			[$idOrder]
		);

		if ($exists) {
			return;
		}

		$base = (string) ($order['date_delivered'] ?? '');
		if ($base === '') {
			$base = date('Y-m-d H:i:s');
		}

		$delayDays = (int) $settings['delay_days'];
		$scheduledTs = strtotime($base . ' +' . $delayDays . ' days');
		$scheduledAt = $scheduledTs ? date('Y-m-d H:i:s', $scheduledTs) : date('Y-m-d H:i:s');

		$name = trim((string) ($order['customer_name'] ?? ''));
		if ($name === '' && $idUser > 0) {
			$user = $user ?? DB::getRowSafe('users', 'id_user = ?', [$idUser]);
			$name = trim((string) ($user['user_full_name'] ?? ''));
		}

		DB::insert('review_invite_queue', [
			'id_order' => $idOrder,
			'id_user' => $idUser,
			'customer_email' => mb_substr($email, 0, 255),
			'customer_name' => mb_substr($name !== '' ? $name : 'Müşteri', 0, 255),
			'order_reference' => mb_substr((string) ($order['reference'] ?? ''), 0, 64),
			'scheduled_at' => $scheduledAt,
			'status' => self::STATUS_PENDING,
			'coupon_code' => '',
			'error_message' => '',
			'date_add' => date('Y-m-d H:i:s'),
		]);
	}

	public static function skipPendingForOrder(int $idOrder, string $reason): void
	{
		self::ensureSchema();

		DB::update(
			'review_invite_queue',
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

	public static function processPendingBatch(int $limit = 50): array
	{
		self::ensureSchema();

		$settings = self::getSettings();

		if (!$settings['enabled']) {
			return ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'processed' => 0];
		}

		$rows = DB::execute(
			'SELECT * FROM review_invite_queue
			 WHERE status = ?
			 AND scheduled_at <= NOW()
			 ORDER BY scheduled_at ASC
			 LIMIT ' . (int) $limit,
			[self::STATUS_PENDING]
		) ?: [];

		$sent = 0;
		$failed = 0;
		$skipped = 0;

		foreach ($rows as $row) {
			$result = self::sendQueueItem($row, $settings);

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

	public static function sendQueueItem(array $row, ?array $settings = null): string
	{
		$settings = $settings ?? self::getSettings();
		$idQueue = (int) ($row['id_queue'] ?? 0);
		$idOrder = (int) ($row['id_order'] ?? 0);
		$idUser = (int) ($row['id_user'] ?? 0);
		$order = DB::getRowSafe('orders', 'id_order = ?', [$idOrder]);

		if (!$order) {
			self::markQueue($idQueue, self::STATUS_SKIPPED, 'Sipariş bulunamadı');

			return self::STATUS_SKIPPED;
		}

		$status = (int) ($order['status'] ?? 0);

		if ($status === Order::STATUS_CANCELLED || $status === Order::STATUS_RETURNED) {
			self::markQueue($idQueue, self::STATUS_SKIPPED, 'Sipariş iptal/iade');

			return self::STATUS_SKIPPED;
		}

		if ($status !== Order::STATUS_DELIVERED) {
			self::markQueue($idQueue, self::STATUS_SKIPPED, 'Sipariş teslim durumunda değil');

			return self::STATUS_SKIPPED;
		}

		$email = trim((string) ($row['customer_email'] ?? ''));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			self::markQueue($idQueue, self::STATUS_FAILED, 'Geçersiz e-posta');

			return self::STATUS_FAILED;
		}

		$products = self::getReviewableProducts($idOrder, $idUser);

		if ($products === []) {
			self::markQueue($idQueue, self::STATUS_SKIPPED, 'Yorumlanacak ürün yok');

			return self::STATUS_SKIPPED;
		}

		$couponCode = trim((string) ($row['coupon_code'] ?? ''));

		if ($couponCode === '' && !empty($settings['coupon_enabled']) && $idUser > 0) {
			$dateTo = '';
			$validDays = (int) $settings['coupon_valid_days'];

			if ($validDays > 0) {
				$dateTo = date('Y-m-d H:i:s', strtotime('+' . $validDays . ' days'));
			}

			$couponResult = Coupon::createPersonal([
				'id_user' => $idUser,
				'prefix' => $settings['coupon_prefix'],
				'discount_type' => $settings['coupon_type'],
				'discount_value' => $settings['coupon_value'],
				'min_cart' => $settings['coupon_min_cart'],
				'max_uses' => 1,
				'date_to' => $dateTo,
			]);

			if (empty($couponResult['success'])) {
				self::markQueue($idQueue, self::STATUS_FAILED, mb_substr((string) ($couponResult['message'] ?? 'Kupon oluşturulamadı'), 0, 250));

				return self::STATUS_FAILED;
			}

			$couponCode = (string) ($couponResult['code'] ?? '');

			// Mail başarısız olursa tekrar denemede aynı kod kullanılsın
			if ($couponCode !== '') {
				DB::update(
					'review_invite_queue',
					['coupon_code' => $couponCode],
					'id_queue = :id_queue',
					['id_queue' => $idQueue]
				);
			}
		}

		$placeholders = self::buildPlaceholders($row, $products, $settings, $couponCode);
		$subject = self::replacePlaceholders((string) $settings['subject'], $placeholders);
		$body = self::replacePlaceholders((string) $settings['body'], $placeholders);

		if (trim(strip_tags($body)) === '') {
			$body = self::replacePlaceholders(self::getDefaultBody(), $placeholders);
		}

		if (strpos($body, '<') === false) {
			$body = nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8'), false);
		}

		if (!class_exists('Mail', false)) {
			require_once dirname(__DIR__, 2) . '/core/Mail.php';
		}

		$ok = Mail::send($email, $subject, $body);

		if (!$ok) {
			$error = Mail::getLastError() ?: 'E-posta gönderilemedi';
			self::markQueue($idQueue, self::STATUS_FAILED, mb_substr($error, 0, 250));

			return self::STATUS_FAILED;
		}

		DB::update(
			'review_invite_queue',
			[
				'status' => self::STATUS_SENT,
				'sent_at' => date('Y-m-d H:i:s'),
				'coupon_code' => $couponCode,
				'error_message' => '',
			],
			'id_queue = :id_queue',
			['id_queue' => $idQueue]
		);

		return self::STATUS_SENT;
	}

	public static function getReviewableProducts(int $idOrder, int $idUser): array
	{
		$items = DB::execute(
			'SELECT od.id_product, od.product_name, p.product_link, c.category_link
			 FROM order_detail od
			 LEFT JOIN products p ON p.id_product = od.id_product
			 LEFT JOIN categories c ON c.id_category = p.id_category
			 WHERE od.id_order = ?
			 ORDER BY od.id_order_detail ASC',
			[$idOrder]
		) ?: [];

		$out = [];
		$seen = [];

		foreach ($items as $item) {
			$idProduct = (int) ($item['id_product'] ?? 0);

			if ($idProduct <= 0 || isset($seen[$idProduct])) {
				continue;
			}

			$seen[$idProduct] = true;

			if ($idUser > 0) {
				$existing = DB::getValue(
					'SELECT id_review FROM product_reviews WHERE id_product = ? AND id_user = ? LIMIT 1',
					[$idProduct, $idUser]
				);

				if ($existing) {
					continue;
				}
			}

			$name = trim((string) ($item['product_name'] ?? ''));
			$link = '';

			if (!empty($item['product_link']) && !empty($item['category_link'])) {
				$link = Product::getLink([
					'category_link' => $item['category_link'],
					'product_link' => $item['product_link'],
					'id_product' => $idProduct,
				]);
			}

			$out[] = [
				'id_product' => $idProduct,
				'product_name' => $name !== '' ? $name : ('Ürün #' . $idProduct),
				'url' => $link,
			];
		}

		return $out;
	}

	private static function buildPlaceholders(array $row, array $products, array $settings, string $couponCode): array
	{
		$siteName = trim((string) Settings::get('SITE_NAME')) ?: 'Mağazamız';
		$listHtml = '<ul>';

		foreach ($products as $product) {
			$name = htmlspecialchars((string) $product['product_name'], ENT_QUOTES, 'UTF-8');
			$url = trim((string) ($product['url'] ?? ''));

			if ($url !== '') {
				$listHtml .= '<li><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $name . '</a></li>';
			} else {
				$listHtml .= '<li>' . $name . '</li>';
			}
		}

		$listHtml .= '</ul>';

		$couponInfo = '';

		if ($couponCode !== '') {
			$label = $settings['coupon_type'] === 'percent'
				? '%' . rtrim(rtrim(number_format((float) $settings['coupon_value'], 2, '.', ''), '0'), '.')
				: Tools::displayPrice($settings['coupon_value']);

			$couponInfo = 'Yorum yaptığınız için size özel kupon kodunuz: <strong>'
				. htmlspecialchars($couponCode, ENT_QUOTES, 'UTF-8')
				. '</strong> (' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' indirim).';
		}

		return [
			'{customer_name}' => (string) ($row['customer_name'] ?? 'Müşteri'),
			'{customer_email}' => (string) ($row['customer_email'] ?? ''),
			'{order_reference}' => (string) ($row['order_reference'] ?? ''),
			'{products_list}' => $listHtml,
			'{coupon_code}' => $couponCode,
			'{coupon_info}' => $couponInfo,
			'{site_name}' => $siteName,
		];
	}

	private static function replacePlaceholders(string $text, array $map): string
	{
		return str_replace(array_keys($map), array_values($map), $text);
	}

	private static function markQueue(int $idQueue, string $status, string $error = ''): void
	{
		DB::update(
			'review_invite_queue',
			[
				'status' => $status,
				'error_message' => mb_substr($error, 0, 250),
			],
			'id_queue = :id_queue',
			['id_queue' => $idQueue]
		);
	}

	public static function getQueueList(int $limit = 100): array
	{
		self::ensureSchema();

		$rows = DB::execute(
			'SELECT * FROM review_invite_queue
			 ORDER BY id_queue DESC
			 LIMIT ' . (int) $limit
		) ?: [];

		foreach ($rows as &$row) {
			$row['scheduled_formatted'] = Tools::formatDate3($row['scheduled_at']);
			$row['sent_formatted'] = !empty($row['sent_at']) ? Tools::formatDate3($row['sent_at']) : '—';
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
		}
		unset($row);

		return $rows;
	}

	public static function getQueueStats(): array
	{
		self::ensureSchema();

		return [
			'pending' => (int) DB::getValue('SELECT COUNT(*) FROM review_invite_queue WHERE status = ?', [self::STATUS_PENDING]),
			'sent' => (int) DB::getValue('SELECT COUNT(*) FROM review_invite_queue WHERE status = ?', [self::STATUS_SENT]),
			'failed' => (int) DB::getValue('SELECT COUNT(*) FROM review_invite_queue WHERE status = ?', [self::STATUS_FAILED]),
			'skipped' => (int) DB::getValue('SELECT COUNT(*) FROM review_invite_queue WHERE status = ?', [self::STATUS_SKIPPED]),
		];
	}
}
