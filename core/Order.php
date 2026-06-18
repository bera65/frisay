<?php

class Order
{
	const STATUS_PENDING = 1;
	const STATUS_PROCESSING = 2;
	const STATUS_SHIPPED = 3;
	const STATUS_DELIVERED = 4;
	const STATUS_CANCELLED = 5;

	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$columns = [
			'company_name' => "varchar(128) NOT NULL DEFAULT '' AFTER `customer_phone`",
			'tax_office' => "varchar(64) NOT NULL DEFAULT '' AFTER `company_name`",
			'tax_number' => "varchar(20) NOT NULL DEFAULT '' AFTER `tax_office`",
			'cargo_company' => "varchar(64) NOT NULL DEFAULT '' AFTER `status`",
			'tracking_number' => "varchar(64) NOT NULL DEFAULT '' AFTER `cargo_company`",
		];

		foreach ($columns as $name => $definition) {
			$exists = DB::execute("SHOW COLUMNS FROM `orders` LIKE '{$name}'");

			if (empty($exists)) {
				DB::execute("ALTER TABLE `orders` ADD COLUMN `{$name}` {$definition}");
			}
		}
	}

	public static function getStatusLabel(int $status): string
	{
		$labels = [
			self::STATUS_PENDING => 'Ödeme Bekliyor',
			self::STATUS_PROCESSING => 'Hazırlanıyor',
			self::STATUS_SHIPPED => 'Kargoda',
			self::STATUS_DELIVERED => 'Teslim Edildi',
			self::STATUS_CANCELLED => 'İptal Edildi',
		];

		return $labels[$status] ?? 'Bilinmiyor';
	}

	public static function getPaymentLabel(string $method): string
	{
		$methods = Module::getPaymentMethods();

		if (isset($methods[$method])) {
			return $methods[$method]['label'];
		}

		$labels = [
			'bank_transfer' => 'Havale / EFT',
			'cash_on_delivery' => 'Kapıda Ödeme',
		];

		return isset($labels[$method]) ? $labels[$method] : $method;
	}

	public static function getShippingFee(float $subtotal): float
	{
		$min = (float) (Settings::get('FREE_SHIPPING_MIN') ?: 1500);
		$fee = (float) (Settings::get('SHIPPING_FEE') ?: 49.90);

		return $subtotal >= $min ? 0.0 : $fee;
	}

	public static function getCheckoutTotals(float $subtotal, float $discount = 0.0): array
	{
		$discount = max(0.0, min($subtotal, $discount));
		$afterDiscount = $subtotal - $discount;
		$shipping = self::getShippingFee($afterDiscount);
		$total = $afterDiscount + $shipping;

		return [
			'subtotal' => $subtotal,
			'subtotal_formatted' => Tools::displayPrice($subtotal),
			'discount' => $discount,
			'discount_formatted' => Tools::displayPrice($discount),
			'shipping' => $shipping,
			'shipping_formatted' => Tools::displayPrice($shipping),
			'total' => $total,
			'total_formatted' => Tools::displayPrice($total),
			'free_shipping_min' => (float) (Settings::get('FREE_SHIPPING_MIN') ?: 1500),
		];
	}

	public static function place(array $data): array
	{
		self::ensureSchema();

		if (!Customer::isLoggedIn()) {
			return self::fail('Sipariş vermek için giriş yapmalısınız');
		}

		$cart = Cart::getSummary();
		if ($cart['empty']) {
			return self::fail('Sepetiniz boş');
		}

		$name = trim((string) ($data['customer_name'] ?? ''));
		$phone = Customer::normalizePhone((string) ($data['customer_phone'] ?? ''));
		$city = trim((string) ($data['address_city'] ?? ''));
		$district = trim((string) ($data['address_district'] ?? ''));
		$address = trim((string) ($data['address_text'] ?? ''));
		$note = trim((string) ($data['note'] ?? ''));
		$companyName = mb_substr(trim(strip_tags((string) ($data['company_name'] ?? ''))), 0, 128);
		$taxOffice = mb_substr(trim(strip_tags((string) ($data['tax_office'] ?? ''))), 0, 64);
		$taxNumber = preg_replace('/\D+/', '', (string) ($data['tax_number'] ?? ''));
		$taxNumber = mb_substr($taxNumber, 0, 20);
		$payment = (string) ($data['payment_method'] ?? '');
		$idUser = Customer::getId();
		$idAddress = (int) ($data['id_address'] ?? 0);

		if ($idAddress > 0) {
			$savedAddress = Address::getForUser($idAddress, $idUser);

			if (!$savedAddress) {
				return self::fail('Seçilen adres bulunamadı');
			}

			$name = $savedAddress['full_name'];
			$phone = $savedAddress['phone'];
			$city = $savedAddress['city'];
			$district = $savedAddress['district'];
			$address = $savedAddress['address_text'];

			if ($companyName === '' && trim((string) ($savedAddress['company_name'] ?? '')) !== '') {
				$companyName = mb_substr(trim((string) $savedAddress['company_name']), 0, 128);
			}
			if ($taxOffice === '' && trim((string) ($savedAddress['tax_office'] ?? '')) !== '') {
				$taxOffice = mb_substr(trim((string) $savedAddress['tax_office']), 0, 64);
			}
			if ($taxNumber === '' && trim((string) ($savedAddress['tax_number'] ?? '')) !== '') {
				$taxNumber = mb_substr(preg_replace('/\D+/', '', (string) $savedAddress['tax_number']), 0, 20);
			}
		}

		if (!Validate::isName($name)) {
			return self::fail('Geçerli bir ad soyad girin');
		}

		if (!Customer::isValidPhone($phone)) {
			return self::fail('Geçerli bir telefon numarası girin');
		}

		if ($city === '' || $district === '' || $address === '') {
			return self::fail('Teslimat adresini eksiksiz doldurun');
		}

		$paymentMethods = Module::getPaymentMethods();

		if ($paymentMethods !== []) {
			// Ödeme modülleri kurulu: yöntem onlardan birine ait olmalı
			if (!isset($paymentMethods[$payment])) {
				return self::fail('Geçerli bir ödeme yöntemi seçin');
			}
		} elseif (!in_array($payment, ['bank_transfer', 'cash_on_delivery'], true)) {
			// Hiç ödeme modülü yoksa eski sabit yöntemler geçerli
			return self::fail('Geçerli bir ödeme yöntemi seçin');
		}

		if (empty($data['accept_terms'])) {
			return self::fail('Devam etmek için sözleşmeleri onaylamalısınız');
		}

		// "Önce ödeme" isteyen modül (sanal POS gibi): sipariş henüz OLUŞTURULMAZ.
		// Form verisi session'da bekletilir, müşteri kart sayfasına yönlendirilir.
		// Banka onayından sonra modül Order::placePending() ile siparişi oluşturur.
		if (empty($data['_payment_done'])) {
			$prePayModule = Module::getPaymentModule($payment);

			if ($prePayModule && $prePayModule->paysBeforeOrder) {
				$paymentPage = $prePayModule->getPaymentPageUrl();

				if ($paymentPage === '') {
					return self::fail('Ödeme sayfası yapılandırılmamış');
				}

				$_SESSION['pending_order_data'] = $data;

				return [
					'success' => true,
					'message' => 'Ödeme sayfasına yönlendiriliyorsunuz',
					'id_order' => 0,
					'reference' => '',
					'redirect' => $paymentPage,
				];
			}
		}

		$subtotal = (float) $cart['total'];
		$couponDiscount = Coupon::getDiscount($subtotal);
		$appliedCoupon = Coupon::getApplied();
		$couponCode = $appliedCoupon ? (string) $appliedCoupon['code'] : '';
		$totals = self::getCheckoutTotals($subtotal, $couponDiscount);
		$reference = self::generateReference();

		global $db;

		try {
			$db->beginTransaction();

			foreach ($cart['items'] as $item) {
				$product = Product::getById((int) $item['id_product']);

				if (!$product || !Product::isInStock($product, (int) $item['qty'])) {
					throw new RuntimeException('Sepette stokta olmayan ürün var: ' . ($item['product_name'] ?? ''));
				}

				if (!Product::decreaseStock((int) $item['id_product'], (int) $item['qty'])) {
					throw new RuntimeException('Stok yetersiz: ' . ($item['product_name'] ?? ''));
				}
			}

			$idOrder = DB::insert('orders', [
				'id_user' => $idUser,
				'reference' => $reference,
				'status' => self::STATUS_PENDING,
				'payment_method' => $payment,
				'customer_name' => $name,
				'customer_phone' => $phone,
				'company_name' => $companyName,
				'tax_office' => $taxOffice,
				'tax_number' => $taxNumber,
				'address_city' => $city,
				'address_district' => $district,
				'address_text' => $address,
				'note' => $note,
				'coupon_code' => $couponCode,
				'coupon_discount' => $totals['discount'],
				'subtotal' => $totals['subtotal'],
				'shipping' => $totals['shipping'],
				'total' => $totals['total'],
			]);

			if (!$idOrder) {
				throw new RuntimeException('Sipariş kaydedilemedi');
			}

			foreach ($cart['items'] as $item) {
				$ok = DB::insert('order_detail', [
					'id_order' => (int) $idOrder,
					'id_product' => (int) $item['id_product'],
					'product_name' => $item['product_name'],
					'price' => (float) $item['price'],
					'qty' => (int) $item['qty'],
					'total' => (float) $item['line_total'],
				]);

				if (!$ok) {
					throw new RuntimeException('Sipariş satırı kaydedilemedi');
				}
			}

			$db->commit();
			Cart::clear();

			if ($couponCode !== '') {
				Coupon::markUsed($couponCode);
				Coupon::remove();
			}

			Notification::orderPlaced($idUser, $reference, (float) $totals['total']);

			if ($idAddress === 0 && !empty($data['save_address'])) {
				Address::save($idUser, [
					'label' => isset($data['address_label']) ? $data['address_label'] : '',
					'full_name' => $name,
					'phone' => $phone,
					'company_name' => $companyName,
					'tax_office' => $taxOffice,
					'tax_number' => $taxNumber,
					'city' => $city,
					'district' => $district,
					'address_text' => $address,
					'is_default' => isset($data['set_default_address']) ? $data['set_default_address'] : 0,
				]);
			}

			// Ödeme modülünü devreye al: PayTR gibi modüller redirect dönebilir.
			// Sipariş bu noktada kaydedildi; modül hatası siparişi iptal etmemeli.
			// Ödeme zaten alındıysa (_payment_done) processPayment atlanır.
			$redirect = '';
			$paymentModule = empty($data['_payment_done']) ? Module::getPaymentModule($payment) : null;

			if ($paymentModule) {
				try {
					$orderRow = self::getByIdAdmin((int) $idOrder);
					$process = $paymentModule->processPayment($orderRow ? $orderRow : []);

					if (!empty($process['redirect'])) {
						$redirect = (string) $process['redirect'];
					}
				} catch (Exception $e) {
					// Modül hatasında standart onay sayfasına devam edilir
				}
			}

			return [
				'success' => true,
				'message' => 'Siparişiniz alındı',
				'id_order' => (int) $idOrder,
				'reference' => $reference,
				'redirect' => $redirect,
			];
		} catch (Exception $e) {
			if ($db->inTransaction()) {
				$db->rollBack();
			}

			return self::fail('Sipariş oluşturulamadı, lütfen tekrar deneyin');
		}
	}

	/** Kart sayfası bekleyen bir checkout var mı? */
	public static function hasPendingPayment(): bool
	{
		return !empty($_SESSION['pending_order_data']);
	}

	/**
	 * Banka onayı alındıktan sonra ödeme modülü tarafından çağrılır:
	 * session'da bekletilen checkout verisiyle siparişi gerçekten oluşturur.
	 * Stok ve adres tekrar doğrulanır (kart sayfasında beklerken değişmiş olabilir).
	 */
	public static function placePending(): array
	{
		$data = isset($_SESSION['pending_order_data']) ? $_SESSION['pending_order_data'] : null;

		if (!is_array($data)) {
			return self::fail('Bekleyen sipariş bulunamadı, lütfen tekrar deneyin');
		}

		$data['_payment_done'] = 1;
		$result = self::place($data);

		if ($result['success']) {
			unset($_SESSION['pending_order_data']);
		}

		return $result;
	}

	/** Müşteri ödemeden vazgeçtiyse bekleyen checkout verisini temizler */
	public static function clearPendingPayment(): void
	{
		unset($_SESSION['pending_order_data']);
	}

	public static function getByIdForUser(int $idOrder, int $idUser): ?array
	{
		$order = DB::getRowSafe('orders', 'id_order = ? AND id_user = ?', [$idOrder, $idUser]);

		if (!$order) {
			return null;
		}

		$order['status_label'] = self::getStatusLabel((int) $order['status']);
		$order['payment_label'] = self::getPaymentLabel($order['payment_method']);
		$order['subtotal_formatted'] = Tools::displayPrice($order['subtotal']);
		$order['shipping_formatted'] = Tools::displayPrice($order['shipping']);
		$order['total_formatted'] = Tools::displayPrice($order['total']);
		$order['date_formatted'] = Tools::formatDate3($order['date_add']);
		$order['items'] = DB::execute(
			'SELECT od.*, p.barcode, p.stock_code, p.vat
			FROM order_detail od
			LEFT JOIN products p ON p.id_product = od.id_product
			WHERE od.id_order = ?
			ORDER BY od.id_order_detail ASC',
			[$idOrder]
		) ?: [];

		foreach ($order['items'] as &$item) {
			$item['price_formatted'] = Tools::displayPrice($item['price']);
			$item['total_formatted'] = Tools::displayPrice($item['total']);
		}
		unset($item);

		return $order;
	}

	public static function getUserOrders(int $idUser): array
	{
		$rows = DB::execute(
			'SELECT * FROM orders WHERE id_user = ? ORDER BY id_order DESC',
			[$idUser]
		);

		if (!$rows) {
			return [];
		}

		foreach ($rows as &$row) {
			$row['status_label'] = self::getStatusLabel((int) $row['status']);
			$row['payment_label'] = self::getPaymentLabel($row['payment_method']);
			$row['total_formatted'] = Tools::displayPrice($row['total']);
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
		}
		unset($row);

		return $rows;
	}

	public static function trackByReference(string $reference, ?int $idUser = null): ?array
	{
		$reference = strtoupper(trim($reference));

		if ($reference === '' || !preg_match('/^FS[0-9A-Z]+$/', $reference)) {
			return null;
		}

		if ($idUser) {
			return self::getByReferenceForUser($reference, $idUser);
		}

		$order = DB::getRowSafe('orders', 'reference = ?', [$reference]);

		if (!$order) {
			return null;
		}

		return [
			'id_order' => (int) $order['id_order'],
			'reference' => $order['reference'],
			'status' => (int) $order['status'],
			'status_label' => self::getStatusLabel((int) $order['status']),
			'date_formatted' => Tools::formatDate3($order['date_add']),
			'public' => true,
		];
	}

	public static function getByReferenceForUser(string $reference, int $idUser): ?array
	{
		$reference = strtoupper(trim($reference));
		$order = DB::getRowSafe('orders', 'reference = ? AND id_user = ?', [$reference, $idUser]);

		if (!$order) {
			return null;
		}

		return self::getByIdForUser((int) $order['id_order'], $idUser);
	}

	public static function getStatusOptions(): array
	{
		return [
			self::STATUS_PENDING => self::getStatusLabel(self::STATUS_PENDING),
			self::STATUS_PROCESSING => self::getStatusLabel(self::STATUS_PROCESSING),
			self::STATUS_SHIPPED => self::getStatusLabel(self::STATUS_SHIPPED),
			self::STATUS_DELIVERED => self::getStatusLabel(self::STATUS_DELIVERED),
			self::STATUS_CANCELLED => self::getStatusLabel(self::STATUS_CANCELLED),
		];
	}

	public static function getStatusBadgeClass(int $status): string
	{
		$map = [
			self::STATUS_PENDING => 'pending',
			self::STATUS_PROCESSING => 'processing',
			self::STATUS_SHIPPED => 'shipped',
			self::STATUS_DELIVERED => 'delivered',
			self::STATUS_CANCELLED => 'cancelled',
		];

		return $map[$status] ?? 'default';
	}

	public static function enrichAdminRows(array $rows): array
	{
		foreach ($rows as &$row) {
			$row['location'] = trim($row['address_city'] . '/' . $row['address_district'], '/');
			$row['status_class'] = self::getStatusBadgeClass((int) $row['status']);
			$row['date_full'] = date('Y-m-d H:i:s', strtotime($row['date_add']));

			$firstItem = DB::execute(
				'SELECT od.product_name, od.id_product, i.id_image
				 FROM order_detail od
				 LEFT JOIN products p ON p.id_product = od.id_product
				 LEFT JOIN images i ON p.id_product = i.id_product
				 WHERE od.id_order = ?
				 ORDER BY od.id_order_detail ASC
				 LIMIT 1',
				[(int) $row['id_order']]
			);

			$item = $firstItem[0] ?? null;
			$row['thumb_product'] = $item['product_name'] ?? '';
			if ($item['id_image'])
				$row['thumb_url'] = Product::getImageUrl($item['id_image']);
			else
				$row['thumb_url'] = '../img/default.jpg';
		}
		unset($row);

		return $rows;
	}

	public static function getDashboardRecentOrders(int $limit = 15): array
	{
		return self::enrichAdminRows(self::getAdminList(0, $limit, 0));
	}

	public static function getAdminList(int $status = 0, int $limit = 30, int $offset = 0, string $dateFrom = '', string $dateTo = ''): array
	{
		$sql = 'SELECT * FROM orders WHERE 1=1';
		$params = [];

		if ($status > 0) {
			$sql .= ' AND status = ?';
			$params[] = $status;
		}

		self::applyDateFilters($sql, $params, $dateFrom, $dateTo);

		$sql .= ' ORDER BY id_order DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params) ?: [];

		foreach ($rows as &$row) {
			$row['status_label'] = self::getStatusLabel((int) $row['status']);
			$row['payment_label'] = self::getPaymentLabel($row['payment_method']);
			$row['total_formatted'] = Tools::displayPrice($row['total']);
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
		}
		unset($row);

		return $rows;
	}

	public static function countAdmin(int $status = 0, string $dateFrom = '', string $dateTo = ''): int
	{
		$sql = 'SELECT COUNT(*) FROM orders WHERE 1=1';
		$params = [];

		if ($status > 0) {
			$sql .= ' AND status = ?';
			$params[] = $status;
		}

		self::applyDateFilters($sql, $params, $dateFrom, $dateTo);

		return (int) DB::getValue($sql, $params);
	}

	private static function applyDateFilters(string &$sql, array &$params, string $dateFrom, string $dateTo): void
	{
		$dateFrom = trim($dateFrom);
		$dateTo = trim($dateTo);

		if ($dateFrom !== '') {
			$sql .= ' AND date_add >= ?';
			$params[] = $dateFrom;
		}

		if ($dateTo !== '') {
			$sql .= ' AND date_add <= ?';
			$params[] = $dateTo;
		}
	}

	public static function getByIdAdmin(int $idOrder): ?array
	{
		$order = DB::getRowSafe('orders', 'id_order = ?', [$idOrder]);

		if (!$order) {
			return null;
		}

		$order['status_label'] = self::getStatusLabel((int) $order['status']);
		$order['payment_label'] = self::getPaymentLabel($order['payment_method']);
		$order['subtotal_formatted'] = Tools::displayPrice($order['subtotal']);
		$order['shipping_formatted'] = Tools::displayPrice($order['shipping']);
		$order['total_formatted'] = Tools::displayPrice($order['total']);
		$order['date_formatted'] = Tools::formatDate3($order['date_add']);
		$order['items'] = DB::execute(
			'SELECT od.*, p.barcode, p.stock_code, p.vat
			FROM order_detail od
			LEFT JOIN products p ON p.id_product = od.id_product
			WHERE od.id_order = ?
			ORDER BY od.id_order_detail ASC',
			[$idOrder]
		) ?: [];

		foreach ($order['items'] as &$item) {
			$item['price_formatted'] = Tools::displayPrice($item['price']);
			$item['total_formatted'] = Tools::displayPrice($item['total']);
		}
		unset($item);

		return $order;
	}

	public static function updateStatus(int $idOrder, int $status): array
	{
		return self::updateFromApi($idOrder, ['status' => $status]);
	}

	public static function updateFromApi(int $idOrder, array $data): array
	{
		self::ensureSchema();

		$order = self::getByIdAdmin($idOrder);

		if (!$order) {
			return self::fail('Sipariş bulunamadı');
		}

		$row = [];
		$oldStatus = (int) $order['status'];

		if (array_key_exists('status', $data)) {
			$status = (int) $data['status'];

			if (!isset(self::getStatusOptions()[$status])) {
				return self::fail('Geçersiz sipariş durumu');
			}

			$row['status'] = $status;
		}

		if (array_key_exists('cargo_company', $data)) {
			$row['cargo_company'] = mb_substr(trim(strip_tags((string) $data['cargo_company'])), 0, 64);
		}

		if (array_key_exists('tracking_number', $data)) {
			$row['tracking_number'] = mb_substr(trim(strip_tags((string) $data['tracking_number'])), 0, 64);
		}

		if ($row === []) {
			return self::fail('Güncellenecek alan yok');
		}

		$newStatus = (int) ($row['status'] ?? $oldStatus);

		if (
			isset($row['status'])
			&& $newStatus === $oldStatus
			&& !array_key_exists('cargo_company', $row)
			&& !array_key_exists('tracking_number', $row)
		) {
			return self::ok('Sipariş durumu zaten güncel');
		}

		if (
			!isset($row['status'])
			&& array_key_exists('cargo_company', $row)
			&& $row['cargo_company'] === (string) ($order['cargo_company'] ?? '')
			&& array_key_exists('tracking_number', $row)
			&& $row['tracking_number'] === (string) ($order['tracking_number'] ?? '')
		) {
			return self::ok('Sipariş bilgileri zaten güncel');
		}

		DB::update(
			'orders',
			$row,
			'id_order = :id_order',
			['id_order' => $idOrder]
		);

		if ($newStatus === self::STATUS_CANCELLED && $oldStatus !== self::STATUS_CANCELLED) {
			self::restoreStock($idOrder);
		}

		if (isset($row['status']) && $newStatus !== $oldStatus) {
			$order['status'] = $newStatus;
			Notification::orderStatusChanged($order, $oldStatus, $newStatus);
		}

		return self::ok('Sipariş güncellendi');
	}

	public static function restoreStock(int $idOrder): void
	{
		$items = DB::execute(
			'SELECT id_product, qty FROM order_detail WHERE id_order = ?',
			[$idOrder]
		) ?: [];

		foreach ($items as $item) {
			Product::increaseStock((int) $item['id_product'], (int) $item['qty']);
		}
	}

	/** Web API: siparişlere satır ve müşteri e-postası ekler */
	public static function attachApiDetails(array $orders): array
	{
		if ($orders === []) {
			return [];
		}

		$orderIds = array_map(static fn(array $row): int => (int) $row['id_order'], $orders);
		$userIds = array_values(array_unique(array_filter(array_map(
			static fn(array $row): int => (int) ($row['id_user'] ?? 0),
			$orders
		))));

		$linesByOrder = self::getLinesGroupedByOrderIds($orderIds);
		$emailsByUser = self::getEmailsByUserIds($userIds);
		$prepared = [];

		foreach ($orders as $order) {
			$idOrder = (int) $order['id_order'];
			$idUser = (int) ($order['id_user'] ?? 0);
			$order['items'] = $linesByOrder[$idOrder] ?? [];
			$order['customer_email'] = $emailsByUser[$idUser] ?? '';
			$prepared[] = $order;
		}

		return $prepared;
	}

	private static function getLinesGroupedByOrderIds(array $orderIds): array
	{
		$orderIds = array_values(array_filter(array_map('intval', $orderIds)));

		if ($orderIds === []) {
			return [];
		}

		$placeholders = implode(',', array_fill(0, count($orderIds), '?'));
		$rows = DB::execute(
			'SELECT od.*, p.barcode, p.stock_code, p.vat
			FROM order_detail od
			LEFT JOIN products p ON p.id_product = od.id_product
			WHERE od.id_order IN (' . $placeholders . ')
			ORDER BY od.id_order ASC, od.id_order_detail ASC',
			$orderIds
		) ?: [];

		$grouped = [];

		foreach ($rows as $row) {
			$grouped[(int) $row['id_order']][] = $row;
		}

		return $grouped;
	}

	private static function getEmailsByUserIds(array $userIds): array
	{
		$userIds = array_values(array_filter(array_map('intval', $userIds)));

		if ($userIds === []) {
			return [];
		}

		$placeholders = implode(',', array_fill(0, count($userIds), '?'));
		$rows = DB::execute(
			'SELECT id_user, email FROM users WHERE id_user IN (' . $placeholders . ')',
			$userIds
		) ?: [];

		$map = [];

		foreach ($rows as $row) {
			$map[(int) $row['id_user']] = (string) ($row['email'] ?? '');
		}

		return $map;
	}

	private static function ok(string $message): array
	{
		return [
			'success' => true,
			'message' => $message,
		];
	}

	private static function generateReference(): string
	{
		do {
			$reference = 'FS' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
			$exists = DB::getValue('SELECT id_order FROM orders WHERE reference = ? LIMIT 1', [$reference]);
		} while ($exists);

		return $reference;
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
		];
	}
}
