<?php

class Admin
{
	public static function login(string $email, string $password): array
	{
		$email = trim(strtolower($email));

		if (!Validate::isEmail($email)) {
			return self::fail('E-posta veya şifre hatalı');
		}

		$row = DB::getRowSafe('admins', 'email = ? AND active = 1', [$email]);

		if (!$row || !password_verify($password, $row['password'])) {
			return self::fail('E-posta veya şifre hatalı');
		}

		session_regenerate_id(true);
		$_SESSION['id_admin'] = (int) $row['id_admin'];

		return self::ok('Giriş başarılı');
	}

	public static function logout(): void
	{
		unset($_SESSION['id_admin']);
		session_regenerate_id(true);
	}

	public static function isLoggedIn(): bool
	{
		return !empty($_SESSION['id_admin']);
	}

	public static function getId(): int
	{
		return (int) ($_SESSION['id_admin'] ?? 0);
	}

	public static function getCurrent(): ?array
	{
		if (!self::isLoggedIn()) {
			return null;
		}

		$row = DB::getRowSafe('admins', 'id_admin = ? AND active = 1', [self::getId()]);

		if (!$row) {
			self::logout();

			return null;
		}

		unset($row['password']);

		return $row;
	}

	public static function requireLogin(): void
	{
		if (!self::isLoggedIn()) {
			header('Location: ' . self::url('login'));
			exit;
		}
	}

	public static function url(string $path = ''): string
	{
		global $adminUrl;

		return $adminUrl . ltrim($path, '/');
	}

	public static function getDashboardStats(): array
	{
		$cancelled = Order::STATUS_CANCELLED;

		$ordersToday = (int) DB::getValue('SELECT COUNT(*) FROM orders WHERE DATE(date_add) = CURDATE()');
		$ordersYesterday = (int) DB::getValue(
			'SELECT COUNT(*) FROM orders WHERE DATE(date_add) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)'
		);
		$revenueToday = (float) DB::getValue(
			'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ? AND DATE(date_add) = CURDATE()',
			[$cancelled]
		);
		$revenueYesterday = (float) DB::getValue(
			'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ? AND DATE(date_add) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
			[$cancelled]
		);
		$revenuePrevWeek = (float) DB::getValue(
			'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ?
			 AND DATE(date_add) >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
			 AND DATE(date_add) < DATE_SUB(CURDATE(), INTERVAL 6 DAY)',
			[$cancelled]
		);

		$pendingReviews = 0;
		$reviewTable = DB::execute("SHOW TABLES LIKE 'product_reviews'");
		if (!empty($reviewTable)) {
			$pendingReviews = (int) DB::getValue('SELECT COUNT(*) FROM product_reviews WHERE active = 0');
		}

		return [
			'orders_total' => (int) DB::getValue('SELECT COUNT(*) FROM orders'),
			'orders_pending' => (int) DB::getValue('SELECT COUNT(*) FROM orders WHERE DATE(date_add) > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = ?', [Order::STATUS_PENDING]),
			'orders_processing' => (int) DB::getValue('SELECT COUNT(*) FROM orders WHERE DATE(date_add) > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = ?', [Order::STATUS_PROCESSING]),
			'orders_cargo' => (int) DB::getValue('SELECT COUNT(*) FROM orders WHERE DATE(date_add) > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = ?', [Order::STATUS_SHIPPED]),
			'orders_awaiting_shipment' => (int) DB::getValue(
				'SELECT COUNT(*) FROM orders WHERE status IN (?, ?)',
				[Order::STATUS_PROCESSING, Order::STATUS_PENDING]
			),
			'orders_today' => $ordersToday,
			'orders_yesterday' => $ordersYesterday,
			'products_total' => (int) DB::getValue('SELECT COUNT(*) FROM products WHERE active = 1'),
			'products_low_stock' => (int) DB::getValue('SELECT COUNT(*) FROM products WHERE active = 1 AND stock <= 5'),
			'users_total' => (int) DB::getValue('SELECT COUNT(*) FROM users WHERE active = 1'),
			'users_today' => (int) DB::getValue('SELECT COUNT(*) FROM users WHERE DATE(date_add) = CURDATE()'),
			'messages_unread' => Contact::countUnread(),
			'pending_reviews' => $pendingReviews,
			'revenue_total' => (float) DB::getValue(
				'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ?',
				[$cancelled]
			),
			'revenue_month' => (float) DB::getValue(
				'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ? AND date_add >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
				[$cancelled]
			),
			'revenue_today' => $revenueToday,
			'revenue_yesterday' => $revenueYesterday,
			'revenue_prev_week' => $revenuePrevWeek,
			'revenue_today_formatted' => Tools::displayPrice($revenueToday),
			'revenue_yesterday_formatted' => Tools::displayPrice($revenueYesterday),
			'revenue_prev_week_formatted' => Tools::displayPrice($revenuePrevWeek),
		];
	}

	public static function getDashboardCharts(): array
	{
		$cancelled = Order::STATUS_CANCELLED;
		$daily = [];

		for ($i = 6; $i >= 0; $i--) {
			$date = date('Y-m-d', strtotime('-' . $i . ' days'));
			$prevDate = date('Y-m-d', strtotime('-' . $i . ' days -7 days'));
			$daily[] = [
				'label' => date('Y-m-d', strtotime($date)),
				'label_short' => date('d.m', strtotime($date)),
				'orders' => (int) DB::getValue(
					'SELECT COUNT(*) FROM orders WHERE DATE(date_add) = ?',
					[$date]
				),
				'revenue' => (float) DB::getValue(
					'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ? AND DATE(date_add) = ?',
					[$cancelled, $date]
				),
				'revenue_prev' => (float) DB::getValue(
					'SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != ? AND DATE(date_add) = ?',
					[$cancelled, $prevDate]
				),
			];
		}

		$status = [];

		foreach (Order::getStatusOptions() as $statusId => $label) {
			$status[] = [
				'label' => $label,
				'count' => (int) DB::getValue('SELECT COUNT(*) FROM orders WHERE status = ?', [$statusId]),
			];
		}

		$topProducts = DB::execute(
			'SELECT od.product_name, SUM(od.qty) AS sold_qty
			 FROM order_detail od
			 INNER JOIN orders o ON o.id_order = od.id_order
			 WHERE o.status != ?
			 GROUP BY od.id_product, od.product_name
			 ORDER BY sold_qty DESC
			 LIMIT 5',
			[$cancelled]
		) ?: [];

		return [
			'daily' => $daily,
			'status' => $status,
			'top_products' => $topProducts,
		];
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
