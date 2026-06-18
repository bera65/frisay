<?php

class Product
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$col = DB::execute("SHOW FULL COLUMNS FROM `products` LIKE 'short_description'");
		$col = $col[0] ?? null;

		if (!$col) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `short_description` varchar(512)
				 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
				 AFTER `product_name`"
			);
		} else {
			$collation = (string) ($col['Collation'] ?? '');

			if ($collation !== '' && stripos($collation, 'utf8mb4') === false) {
				DB::execute(
					"ALTER TABLE `products` MODIFY COLUMN `short_description` varchar(512)
					 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''"
				);
			}
		}

		$metaTitle = DB::execute("SHOW COLUMNS FROM `products` LIKE 'meta_title'");
		if (empty($metaTitle)) {
			DB::execute(
				"ALTER TABLE `products`
				 ADD COLUMN `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `short_description`,
				 ADD COLUMN `meta_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `meta_title`"
			);
		}

		$productVideo = DB::execute("SHOW COLUMNS FROM `products` LIKE 'product_video'");
		if (empty($productVideo)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `product_video` varchar(256)
				 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
				 AFTER `stock`"
			);
		}

		$dovizCol = DB::execute("SHOW COLUMNS FROM `products` LIKE 'doviz'");
		if (empty($dovizCol)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `doviz` varchar(16)
				 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'try'
				 AFTER `price`"
			);
		}

		$dovizPrice = DB::execute("SHOW COLUMNS FROM `products` LIKE 'doviz_price'");
		if (empty($dovizPrice)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `doviz_price` decimal(20,2) NOT NULL DEFAULT 0.00 AFTER `doviz`"
			);
		}

		$dovizOldPrice = DB::execute("SHOW COLUMNS FROM `products` LIKE 'doviz_old_price'");
		if (empty($dovizOldPrice)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `doviz_old_price` decimal(20,2) NOT NULL DEFAULT 0.00 AFTER `doviz_price`"
			);
		}

		$cargoDay = DB::execute("SHOW COLUMNS FROM `products` LIKE 'cargo_day'");
		if (empty($cargoDay)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `cargo_day` int(3) NOT NULL DEFAULT 0 AFTER `stock`"
			);
		}

		$label = DB::execute("SHOW COLUMNS FROM `products` LIKE 'label'");
		if (empty($label)) {
			DB::execute(
				"ALTER TABLE `products` ADD COLUMN `label` varchar(128)
				 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
				 AFTER `cargo_day`"
			);
		}
	}

	public static function getLink(array $row): string
	{
		global $domain;

		return $domain
			. $row['category_link'] . '/'
			. $row['product_link'].'-'
			. (int) $row['id_product'];
	}

	public static function getImageUrl(?int $idImage): string
	{
		global $domain;

		if ($idImage) {
			$relative = 'img/products/' . $idImage . '.jpg';
			if (file_exists(dirname(__DIR__) . '/' . $relative)) {
				return $domain . $relative;
			}
		}

		return $domain . 'templates/default/img/favicon.png';
	}

	public static function getYoutubeEmbedUrl(string $url): string
	{
		$videoId = self::extractYoutubeId($url);

		if ($videoId === '') {
			return '';
		}

		return 'https://www.youtube-nocookie.com/embed/' . $videoId;
	}

	public static function extractYoutubeId(string $url): string
	{
		$url = trim($url);

		if ($url === '') {
			return '';
		}

		if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{11})~i', $url, $matches)) {
			return $matches[1];
		}

		if (preg_match('/^[\w-]{11}$/', $url)) {
			return $url;
		}

		return '';
	}

	public static function getStock(array $product): int
	{
		return max(0, (int) ($product['stock'] ?? 0));
	}

	public static function isInStock(array $product, int $qty = 1): bool
	{
		return self::getStock($product) >= max(1, $qty);
	}

	public static function decreaseStock(int $idProduct, int $qty): bool
	{
		global $db;

		if ($qty <= 0) {
			return false;
		}

		$stmt = $db->prepare(
			'UPDATE products SET stock = stock - ? WHERE id_product = ? AND stock >= ?'
		);
		$stmt->execute([$qty, $idProduct, $qty]);

		return $stmt->rowCount() > 0;
	}

	public static function increaseStock(int $idProduct, int $qty): void
	{
		if ($qty <= 0) {
			return;
		}

		DB::execute(
			'UPDATE products SET stock = stock + ? WHERE id_product = ?',
			[$qty, $idProduct]
		);
	}

	private static ?bool $reviewsEnabled = null;

	private static function reviewsEnabled(): bool
	{
		if (self::$reviewsEnabled === null) {
			$active = DB::getValue(
				"SELECT active FROM modules WHERE name = 'reviews' AND installed = 1 LIMIT 1"
			);

			self::$reviewsEnabled = $active !== false
				&& (int) $active === 1
				&& !empty(DB::execute("SHOW TABLES LIKE 'product_reviews'"));
		}

		return self::$reviewsEnabled;
	}

	/** Liste sayfaları için: enrich + toplu yorum puanı (tek sorgu, N+1 yok) */
	public static function enrichList(array $rows): array
	{
		$rows = array_map([self::class, 'enrich'], $rows);

		if (!$rows || !self::reviewsEnabled()) {
			return $rows;
		}

		$ids = array_map('intval', array_column($rows, 'id_product'));
		$placeholders = implode(',', array_fill(0, count($ids), '?'));

		$stats = DB::execute(
			"SELECT id_product, AVG(rating) AS avg_rating, COUNT(*) AS review_count
			 FROM product_reviews
			 WHERE active = 1 AND id_product IN ({$placeholders})
			 GROUP BY id_product",
			$ids
		) ?: [];

		$map = [];
		foreach ($stats as $stat) {
			$map[(int) $stat['id_product']] = $stat;
		}

		foreach ($rows as &$row) {
			$stat = $map[(int) $row['id_product']] ?? null;
			$row['rating'] = $stat ? round((float) $stat['avg_rating'], 1) : 0.0;
			$row['rating_label'] = number_format($row['rating'], 1, ',', '');
			$row['review_count'] = $stat ? (int) $stat['review_count'] : 0;
		}
		unset($row);

		return $rows;
	}

	public static function enrich(array $row): array
	{
		$row['url'] = self::getLink($row);
		$row['image_url'] = self::getImageUrl(isset($row['id_image']) ? (int) $row['id_image'] : null);
		$row['stock'] = (int) ($row['stock'] ?? 0);
		$row['rating'] = (float) ($row['rating'] ?? 0);
		$row['review_count'] = (int) ($row['review_count'] ?? 0);
		$row['in_stock'] = self::isInStock($row);
		$row['price_formatted'] = Tools::displayPrice((float) $row['price']);
		$row['old_price'] = (float) ($row['old_price'] ?? 0);
		$row['has_discount'] = $row['old_price'] > (float) $row['price'];
		$row['label'] = trim((string) ($row['label'] ?? ''));

		if ($row['has_discount']) {
			$row['old_price_formatted'] = Tools::displayPrice($row['old_price']);
		}

		return $row;
	}

	public static function getImages(int $idProduct): array
	{
		$rows = DB::execute(
			'SELECT id_image, cover FROM images WHERE id_product = ? ORDER BY cover DESC, id_image ASC',
			[$idProduct]
		);

		if (!$rows) {
			return [];
		}

		$images = [];

		foreach ($rows as $row) {
			$images[] = [
				'id_image' => (int) $row['id_image'],
				'url' => self::getImageUrl((int) $row['id_image']),
				'cover' => (int) $row['cover'],
			];
		}

		return $images;
	}

	public static function getById(int $id): ?array
	{
		$rows = DB::execute(
			'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link, i.id_image
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE p.id_product = ? AND p.active = 1
			LIMIT 1',
			[$id]
		);

		if (!$rows || !isset($rows[0])) {
			return null;
		}

		return self::enrich($rows[0]);
	}

	public static function getActiveList(
		?int $idCategory = null,
		int $limit = 24,
		int $offset = 0,
		string $sort = 'newest',
		?int $idBrand = null
	): array {
		$sql = 'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link, i.id_image
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE p.active = 1';
		$params = [];

		if ($idCategory) {
			$sql .= ' AND p.id_category = ?';
			$params[] = $idCategory;
		}

		if ($idBrand) {
			$sql .= ' AND p.id_brand = ?';
			$params[] = $idBrand;
		}

		$sql .= ' ORDER BY ' . Pagination::resolveSort($sort);
		$sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params);

		if (!$rows) {
			return [];
		}

		return self::enrichList($rows);
	}

	public static function getDiscountedList(int $limit = 24, int $offset = 0, string $sort = 'discount'): array
	{
		$sql = 'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link, i.id_image
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE p.active = 1 AND p.old_price > p.price
			ORDER BY ' . Pagination::resolveSort($sort) . '
			LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql);

		if (!$rows) {
			return [];
		}

		return self::enrichList($rows);
	}

	public static function search(string $query, int $limit = 24, int $offset = 0, string $sort = 'newest'): array
	{
		$query = trim($query);

		if ($query === '' || Tools::strlen($query) < 2) {
			return [];
		}

		$like = '%' . $query . '%';

		$rows = DB::execute(
			'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link, i.id_image
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE p.active = 1
			AND (p.product_name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ?)
			ORDER BY ' . Pagination::resolveSort($sort) . '
			LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
			[$like, $like, $like, $like]
		);

		if (!$rows) {
			return [];
		}

		return self::enrichList($rows);
	}

	public static function countActive(?int $idCategory = null, ?int $idBrand = null): int
	{
		$sql = 'SELECT COUNT(*) FROM products WHERE active = 1';
		$params = [];

		if ($idCategory) {
			$sql .= ' AND id_category = ?';
			$params[] = $idCategory;
		}

		if ($idBrand) {
			$sql .= ' AND id_brand = ?';
			$params[] = $idBrand;
		}

		return (int) DB::getValue($sql, $params);
	}

	public static function countDiscounted(): int
	{
		return (int) DB::getValue(
			'SELECT COUNT(*) FROM products WHERE active = 1 AND old_price > price'
		);
	}

	public static function countSearch(string $query): int
	{
		$query = trim($query);

		if ($query === '' || Tools::strlen($query) < 2) {
			return 0;
		}

		$like = '%' . $query . '%';

		return (int) DB::getValue(
			'SELECT COUNT(*)
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			WHERE p.active = 1
			AND (p.product_name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ?)',
			[$like, $like, $like, $like]
		);
	}

	public static function getAdminList(
		string $query = '',
		int $idCategory = 0,
		int $idBrand = 0,
		int $activeFilter = -1,
		int $limit = 30,
		int $offset = 0
	): array {
		$sql = 'SELECT p.*, b.brand_name, c.category_name, i.id_image
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE 1=1';
		$params = [];

		if ($query !== '') {
			$like = '%' . $query . '%';
			$sql .= ' AND (p.product_name LIKE ? OR p.product_link LIKE ?)';
			$params[] = $like;
			$params[] = $like;
		}

		if ($idCategory > 0) {
			$sql .= ' AND p.id_category = ?';
			$params[] = $idCategory;
		}

		if ($idBrand > 0) {
			$sql .= ' AND p.id_brand = ?';
			$params[] = $idBrand;
		}

		if ($activeFilter >= 0) {
			$sql .= ' AND p.active = ?';
			$params[] = $activeFilter;
		}

		$sql .= ' ORDER BY p.id_product DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params) ?: [];

		foreach ($rows as &$row) {
			$row['price_formatted'] = Tools::displayPrice((float) $row['price']);
			$row['image_url'] = self::getImageUrl(isset($row['id_image']) ? (int) $row['id_image'] : null);
			$row['active_label'] = (int) $row['active'] === 1 ? 'Aktif' : 'Pasif';
		}
		unset($row);

		return $rows;
	}

	public static function countAdmin(
		string $query = '',
		int $idCategory = 0,
		int $idBrand = 0,
		int $activeFilter = -1
	): int {
		$sql = 'SELECT COUNT(*) FROM products p WHERE 1=1';
		$params = [];

		if ($query !== '') {
			$like = '%' . $query . '%';
			$sql .= ' AND (p.product_name LIKE ? OR p.product_link LIKE ?)';
			$params[] = $like;
			$params[] = $like;
		}

		if ($idCategory > 0) {
			$sql .= ' AND p.id_category = ?';
			$params[] = $idCategory;
		}

		if ($idBrand > 0) {
			$sql .= ' AND p.id_brand = ?';
			$params[] = $idBrand;
		}

		if ($activeFilter >= 0) {
			$sql .= ' AND p.active = ?';
			$params[] = $activeFilter;
		}

		return (int) DB::getValue($sql, $params);
	}

	public static function getByIdAdmin(int $id): ?array
	{
		$rows = DB::execute(
			'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link
			FROM products p
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			WHERE p.id_product = ?
			LIMIT 1',
			[$id]
		);

		if (!$rows || !isset($rows[0])) {
			return null;
		}

		$product = $rows[0];
		$product['images'] = self::getImages($id);

		return $product;
	}

	public static function isLinkUnique(string $link, int $excludeId = 0): bool
	{
		$sql = 'SELECT COUNT(*) FROM products WHERE product_link = ?';
		$params = [$link];

		if ($excludeId > 0) {
			$sql .= ' AND id_product != ?';
			$params[] = $excludeId;
		}

		return (int) DB::getValue($sql, $params) === 0;
	}

	public static function save(array $data, int $id = 0): array
	{
		$name 			= trim((string) ($data['product_name'] ?? ''));
		$link 			= trim((string) ($data['product_link'] ?? ''));
		$idCategory 	= (int) ($data['id_category'] ?? 0);
		$idBrand 		= (int) ($data['id_brand'] ?? 0);
		$stockCode 		= trim((string) ($data['stock_code'] ?? ''));
		$barcode 		= trim((string) ($data['barcode'] ?? ''));
		$desi 			= (int) ($data['desi'] ?? 0);
		self::ensureSchema();

		$shortDescription 	= trim(strip_tags((string) ($data['short_description'] ?? '')));
		$metaTitle 			= trim(strip_tags((string) ($data['meta_title'] ?? '')));
		$metaDescription 	= trim(strip_tags((string) ($data['meta_description'] ?? '')));
		$description 		= (string) ($data['description'] ?? '');
		$price 				= (float) str_replace(',', '.', (string) ($data['price'] ?? 0));
		$oldPrice 			= (float) str_replace(',', '.', (string) ($data['old_price'] ?? 0));
		$vat 				= (float) str_replace(',', '.', (string) ($data['vat'] ?? 20));
		$stock 				= (int) ($data['stock'] ?? 0);
		$active 			= isset($data['active']) ? (int) $data['active'] : 0;
		$productVideo 		= mb_substr(trim((string) ($data['product_video'] ?? '')), 0, 256);
		$doviz 				= strtolower(trim((string) ($data['doviz'] ?? 'try')));
		$dovizPrice 		= (float) str_replace(',', '.', (string) ($data['doviz_price'] ?? 0));
		$dovizOldPrice		= (float) str_replace(',', '.', (string) ($data['doviz_old_price'] ?? 0));
		$allowedCurrencies 	= ['try', 'usd', 'eur', 'xau'];
		
		if (!in_array($doviz, $allowedCurrencies, true)) {
			$doviz = 'try';
		}

		if ($doviz === 'try') {
			if ($dovizPrice <= 0 && $price > 0) {
				$dovizPrice = $price;
			}
			if ($dovizOldPrice <= 0 && $oldPrice > 0) {
				$dovizOldPrice = $oldPrice;
			}
			$price = $dovizPrice;
			$oldPrice = $dovizOldPrice;
		} else {
			$price = self::kurPrice($dovizPrice, $doviz);
			$oldPrice = self::kurPrice($dovizOldPrice, $doviz);
		}

		$cargoDay = max(0, (int) ($data['cargo_day'] ?? $data['day'] ?? 0));
		$label = mb_substr(trim(strip_tags((string) ($data['label'] ?? $data['tag'] ?? ''))), 0, 128);

		if ($name === '') {
			return self::fail('Ürün adı zorunludur');
		}

		if ($idCategory <= 0 || $idBrand <= 0) {
			return self::fail('Kategori ve marka seçin');
		}

		if ($link === '') {
			$link = Tools::createSlug($name);
		} else {
			$link = Tools::createSlug($link);
		}

		if ($link === '') {
			return self::fail('Geçerli bir URL slug girin');
		}

		if (!self::isLinkUnique($link, $id)) {
			return self::fail('Bu URL slug zaten kullanılıyor');
		}

		if ($productVideo !== '' && self::extractYoutubeId($productVideo) === '') {
			return self::fail('Geçerli bir YouTube video linki girin');
		}

		$row = [
			'id_category' 		=> $idCategory,
			'id_brand' 			=> $idBrand,
			'product_name' 		=> $name,
			'short_description' => mb_substr($shortDescription, 0, 512),
			'meta_title' => mb_substr($metaTitle, 0, 255),
			'meta_description' => mb_substr($metaDescription, 0, 512),
			'description' 		=> $description,
			'product_link' 		=> $link,
			'price' 			=> max(0, $price),
			'doviz' 			=> $doviz,
			'doviz_price'		=> $dovizPrice,
			'doviz_old_price'	=> $dovizOldPrice,
			'old_price' 		=> max(0, $oldPrice),
			'vat' 				=> max(0, $vat),
			'stock' 			=> max(0, $stock),
			'cargo_day' 		=> $cargoDay,
			'label' 			=> $label,
			'product_video' 	=> $productVideo,
			'stock_code' 		=> $stockCode,
			'barcode' 			=> $barcode,
			'desi' 				=> (int)$desi,
			'active' 			=> $active,
		];

		if ($id > 0) {
			$ok = DB::update('products', $row, 'id_product = :where_id', ['where_id' => $id]);

			return $ok !== false
				? ['success' => true, 'message' => 'Ürün güncellendi', 'id' => $id]
				: self::fail('Ürün güncellenemedi');
		}

		$newId = DB::insert('products', $row);

		return $newId
			? ['success' => true, 'message' => 'Ürün eklendi', 'id' => (int) $newId]
			: self::fail('Ürün eklenemedi');
	}

	public static function uploadImage(int $idProduct, array $file): array
	{
		if ($idProduct <= 0 || !self::getByIdAdmin($idProduct)) {
			return self::fail('Ürün bulunamadı');
		}

		if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			return self::fail('Geçerli bir görsel seçin');
		}

		$binary = file_get_contents($file['tmp_name']);

		if (!is_string($binary) || $binary === '') {
			return self::fail('Görsel okunamadı');
		}

		return self::importImageBinary($idProduct, $binary);
	}

	public static function importImageBinary(int $idProduct, string $binary): array
	{
		if ($idProduct <= 0 || !self::getByIdAdmin($idProduct)) {
			return self::fail('Ürün bulunamadı');
		}

		if ($binary === '') {
			return self::fail('Geçerli bir görsel seçin');
		}

		$info = @getimagesizefromstring($binary);

		if (!$info) {
			return self::fail('Dosya bir görsel değil');
		}

		$allowed = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
		if (!in_array($info[2], $allowed, true)) {
			return self::fail('Sadece JPG, PNG veya WEBP yükleyebilirsiniz');
		}

		$hasCover = (int) DB::getValue(
			'SELECT COUNT(*) FROM images WHERE id_product = ? AND cover = 1',
			[$idProduct]
		);

		$idImage = DB::insert('images', [
			'id_product' => $idProduct,
			'cover' => $hasCover > 0 ? 0 : 1,
		]);

		if (!$idImage) {
			return self::fail('Görsel kaydedilemedi');
		}

		$dest = dirname(__DIR__) . '/img/products/' . (int) $idImage . '.jpg';
		$dir = dirname($dest);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return self::fail('Görsel klasörü oluşturulamadı');
		}

		$source = imagecreatefromstring($binary);

		if (!$source) {
			return self::fail('Görsel işlenemedi');
		}

		imagejpeg($source, $dest, 88);
		imagedestroy($source);

		return [
			'success' => true,
			'message' => 'Görsel yüklendi',
			'id' => (int) $idImage,
		];
	}

	public static function importImageFromUrl(int $idProduct, string $url): array
	{
		if ($idProduct <= 0 || !self::getByIdAdmin($idProduct)) {
			return self::fail('Ürün bulunamadı');
		}

		$url = trim($url);

		if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
			return self::fail('Geçersiz görsel URL');
		}

		$scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));

		if (!in_array($scheme, ['http', 'https'], true)) {
			return self::fail('Sadece http/https URL desteklenir');
		}

		if (!function_exists('curl_init')) {
			return self::fail('cURL eklentisi gerekli');
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 20,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_USERAGENT => 'FShop-WebAPI/1.0',
		]);

		$binary = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if ($binary === false || $binary === '') {
			return self::fail($curlError !== '' ? 'Görsel indirilemedi: ' . $curlError : 'Görsel indirilemedi');
		}

		if ($httpCode < 200 || $httpCode >= 300) {
			return self::fail('Görsel indirilemedi (HTTP ' . $httpCode . ')');
		}

		if (strlen($binary) > 5 * 1024 * 1024) {
			return self::fail('Görsel 5 MB sınırını aşıyor');
		}

		return self::importImageBinary($idProduct, $binary);
	}

	public static function patchQuick(int $id, array $data): array
	{
		$product = self::getByIdAdmin($id);

		if (!$product) {
			return self::fail('Ürün bulunamadı');
		}

		$row = [];

		if (array_key_exists('stock', $data)) {
			$row['stock'] = max(0, (int) $data['stock']);
		}

		if (array_key_exists('active', $data)) {
			$row['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
		}

		if (array_key_exists('price', $data) || array_key_exists('doviz_price', $data)) {
			$doviz = strtolower(trim((string) ($product['doviz'] ?? 'try')));
			$dovizPrice = array_key_exists('doviz_price', $data)
				? (float) str_replace(',', '.', (string) $data['doviz_price'])
				: (float) str_replace(',', '.', (string) $data['price']);

			$row['doviz_price'] = max(0, $dovizPrice);

			if ($doviz === 'try') {
				$row['price'] = $row['doviz_price'];
			} else {
				$row['price'] = max(0, self::kurPrice($dovizPrice, $doviz));
			}
		}

		if ($row === []) {
			return self::fail('Güncellenecek alan yok (price, stock veya active gönderin)');
		}

		$ok = DB::update('products', $row, 'id_product = :where_id', ['where_id' => $id]);

		return $ok !== false
			? ['success' => true, 'message' => 'Ürün hızlı güncellendi', 'id' => $id]
			: self::fail('Ürün güncellenemedi');
	}

	public static function setCover(int $idImage): array
	{
		$row = DB::getRowSafe('images', 'id_image = ?', [$idImage]);

		if (!$row) {
			return self::fail('Görsel bulunamadı');
		}

		$idProduct = (int) $row['id_product'];
		DB::update('images', ['cover' => 0], 'id_product = :where_pid', ['where_pid' => $idProduct]);
		DB::update('images', ['cover' => 1], 'id_image = :where_id', ['where_id' => $idImage]);

		return ['success' => true, 'message' => 'Kapak görseli güncellendi', 'id' => $idImage];
	}

	public static function deleteById(int $id): array
	{
		if ($id <= 0 || !self::getByIdAdmin($id)) {
			return self::fail('Ürün bulunamadı');
		}

		foreach (self::getImages($id) as $image) {
			self::deleteImage((int) $image['id_image']);
		}

		DB::execute('DELETE FROM products WHERE id_product = ?', [$id]);

		return ['success' => true, 'message' => 'Ürün silindi', 'id' => $id];
	}

	public static function deleteImage(int $idImage): array
	{
		$row = DB::getRowSafe('images', 'id_image = ?', [$idImage]);

		if (!$row) {
			return self::fail('Görsel bulunamadı');
		}

		$idProduct = (int) $row['id_product'];
		$wasCover = (int) $row['cover'] === 1;
		$file = dirname(__DIR__) . '/img/products/' . $idImage . '.jpg';

		DB::execute('DELETE FROM images WHERE id_image = ?', [$idImage]);

		if (is_file($file)) {
			@unlink($file);
		}

		if ($wasCover) {
			$next = DB::getRowSafe('images', 'id_product = ?', [$idProduct]);
			if ($next) {
				DB::update('images', ['cover' => 1], 'id_image = :where_id', ['where_id' => (int) $next['id_image']]);
			}
		}

		return ['success' => true, 'message' => 'Görsel silindi', 'id' => $idImage];
	}

	public static function importFromExcel(array $rows): array
	{
		if (count($rows) < 2) {
			return self::fail('Excel dosyasında veri satırı yok');
		}

		$headers = array_map([self::class, 'normalizeImportHeader'], $rows[0]);
		$map = self::buildImportColumnMap($headers);

		if (!isset($map['product_name'])) {
			return self::fail('Ürün adı sütunu bulunamadı');
		}

		$created = 0;
		$updated = 0;
		$categoriesCreated = 0;
		$brandsCreated = 0;
		$errors = [];
		$categoryCache = [];
		$brandCache = [];

		for ($i = 1, $count = count($rows); $i < $count; $i++) {
			if (self::isImportRowEmpty($rows[$i])) {
				continue;
			}

			$result = self::importExcelRow(
				$rows[$i],
				$map,
				$i + 1,
				$categoryCache,
				$brandCache,
				$categoriesCreated,
				$brandsCreated
			);

			if ($result['success']) {
				if (!empty($result['created'])) {
					$created++;
				} else {
					$updated++;
				}
				continue;
			}

			$errors[] = $result['message'];
		}

		if ($created === 0 && $updated === 0 && $errors) {
			return self::fail(implode(' ', array_slice($errors, 0, 3)));
		}

		$message = $created . ' ürün eklendi, ' . $updated . ' ürün güncellendi';

		if ($categoriesCreated > 0) {
			$message .= ', ' . $categoriesCreated . ' kategori oluşturuldu';
		}

		if ($brandsCreated > 0) {
			$message .= ', ' . $brandsCreated . ' marka oluşturuldu';
		}

		if ($errors) {
			$message .= '. Hatalar: ' . implode('; ', array_slice($errors, 0, 5));

			if (count($errors) > 5) {
				$message .= ' (+' . (count($errors) - 5) . ' hata daha)';
			}
		}

		return [
			'success' => true,
			'message' => $message,
			'created' => $created,
			'updated' => $updated,
			'categories_created' => $categoriesCreated,
			'brands_created' => $brandsCreated,
			'errors' => $errors,
			'id' => 0,
		];
	}

	private static function importExcelRow(
		array $row,
		array $map,
		int $lineNo,
		array &$categoryCache,
		array &$brandCache,
		int &$categoriesCreated,
		int &$brandsCreated
	): array {
		$barcode = self::importCell($row, $map, 'barcode');
		$stockCode = self::importCell($row, $map, 'stock_code');
		$id = self::findIdByBarcodeOrStockCode($barcode, $stockCode);

		$categoryName = self::importCell($row, $map, 'category_name');
		$brandName = self::importCell($row, $map, 'brand_name');

		$idCategory = self::resolveOrCreateCategoryId($categoryName, $categoryCache, $categoriesCreated);
		$idBrand = self::resolveOrCreateBrandId($brandName, $brandCache, $brandsCreated);

		if ($idCategory <= 0) {
			return self::fail('Satır ' . $lineNo . ': kategori oluşturulamadı');
		}

		if ($idBrand <= 0) {
			return self::fail('Satır ' . $lineNo . ': marka oluşturulamadı');
		}

		$data = [
			'product_name' => self::importCell($row, $map, 'product_name'),
			'barcode' => $barcode,
			'stock_code' => $stockCode,
			'desi' => self::importCell($row, $map, 'desi'),
			'price' => self::importCell($row, $map, 'price'),
			'old_price' => self::importCell($row, $map, 'old_price'),
			'vat' => self::importCell($row, $map, 'vat'),
			'stock' => self::importCell($row, $map, 'stock'),
			'short_description' => self::importCell($row, $map, 'short_description'),
			'description' => self::importCell($row, $map, 'description'),
			'meta_title' => self::importCell($row, $map, 'meta_title'),
			'meta_description' => self::importCell($row, $map, 'meta_description'),
			'product_link' => self::importCell($row, $map, 'slug'),
			'id_category' => $idCategory,
			'id_brand' => $idBrand,
			'active' => self::parseImportActive(self::importCell($row, $map, 'active')),
		];

		$result = self::save($data, $id);

		if (!$result['success']) {
			return self::fail('Satır ' . $lineNo . ': ' . $result['message']);
		}

		$result['created'] = $id <= 0;

		return $result;
	}

	private static function findIdByBarcodeOrStockCode(string $barcode, string $stockCode): int
	{
		if ($barcode !== '') {
			$id = (int) DB::getValue(
				'SELECT id_product FROM products WHERE barcode = ? LIMIT 1',
				[$barcode]
			);

			if ($id > 0) {
				return $id;
			}
		}

		if ($stockCode !== '') {
			return (int) DB::getValue(
				'SELECT id_product FROM products WHERE stock_code = ? LIMIT 1',
				[$stockCode]
			);
		}

		return 0;
	}

	private static function resolveOrCreateCategoryId(string $name, array &$cache, int &$createdCount): int
	{
		$name = trim($name);

		if ($name === '') {
			return 0;
		}

		$key = mb_strtolower($name);

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$id = (int) DB::getValue(
			'SELECT id_category FROM categories WHERE LOWER(category_name) = LOWER(?) LIMIT 1',
			[$name]
		);

		if ($id > 0) {
			$cache[$key] = $id;

			return $id;
		}

		$result = Category::save([
			'category_name' => $name,
			'id_parent' => self::getImportCategoryParentId(),
			'active' => 1,
		]);

		if (!$result['success']) {
			return 0;
		}

		$id = (int) $result['id'];
		$cache[$key] = $id;
		$createdCount++;

		return $id;
	}

	private static function resolveOrCreateBrandId(string $name, array &$cache, int &$createdCount): int
	{
		$name = trim($name);

		if ($name === '') {
			return 0;
		}

		$key = mb_strtolower($name);

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$id = (int) DB::getValue(
			'SELECT id_brand FROM brands WHERE LOWER(brand_name) = LOWER(?) LIMIT 1',
			[$name]
		);

		if ($id > 0) {
			$cache[$key] = $id;

			return $id;
		}

		$result = Brand::save([
			'brand_name' => $name,
			'active' => 1,
		]);

		if (!$result['success']) {
			return 0;
		}

		$id = (int) $result['id'];
		$cache[$key] = $id;
		$createdCount++;

		return $id;
	}

	private static function getImportCategoryParentId(): int
	{
		static $parentId = null;

		if ($parentId !== null) {
			return $parentId;
		}

		$parentId = (int) DB::getValue(
			'SELECT id_category FROM categories WHERE id_parent = 0 AND active = 1 ORDER BY id_category ASC LIMIT 1'
		);

		return max(0, $parentId);
	}

	private static function normalizeImportHeader($header): string
	{
		$header = strip_tags((string) $header);
		$header = html_entity_decode($header, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$header = strtolower(trim($header));
		$header = preg_replace('/\s+/', ' ', $header);

		return $header ?? '';
	}

	private static function buildImportColumnMap(array $headers): array
	{
		$aliases = [
			'product_id' => ['product id'],
			'product_name' => ['product name', 'ürün adı', 'urun adi'],
			'barcode' => ['barcode', 'barkod'],
			'stock_code' => ['stock code', 'stok kodu'],
			'desi' => ['desi'],
			'price' => ['price', 'fiyat'],
			'old_price' => ['old price', 'eski fiyat'],
			'vat' => ['vat', 'kdv'],
			'stock' => ['stock', 'stok'],
			'short_description' => ['short description', 'kısa açıklama', 'kisa aciklama'],
			'description' => ['description', 'açıklama', 'aciklama'],
			'meta_title' => ['meta title'],
			'meta_description' => ['meta description'],
			'slug' => ['slug', 'product link', 'url'],
			'category_name' => ['category name', 'kategori', 'kategori adı', 'kategori adi'],
			'brand_name' => ['brand name', 'marka', 'marka adı', 'marka adi'],
			'images' => ['images', 'görseller', 'gorseller'],
			'active' => ['active', 'durum', 'aktif'],
		];

		$map = [];

		foreach ($headers as $index => $header) {
			if ($header === '') {
				continue;
			}

			foreach ($aliases as $field => $names) {
				if (in_array($header, $names, true)) {
					$map[$field] = $index;
					break;
				}
			}
		}

		return $map;
	}

	private static function importCell(array $row, array $map, string $key): string
	{
		if (!isset($map[$key])) {
			return '';
		}

		$index = $map[$key];

		return isset($row[$index]) ? trim((string) $row[$index]) : '';
	}

	private static function isImportRowEmpty(array $row): bool
	{
		foreach ($row as $cell) {
			if (trim((string) $cell) !== '') {
				return false;
			}
		}

		return true;
	}

	private static function parseImportActive(string $value): int
	{
		$value = strtolower(trim($value));

		if ($value === '' || $value === 'aktif' || $value === '1' || $value === 'yes' || $value === 'evet') {
			return 1;
		}

		if ($value === 'pasif' || $value === '0' || $value === 'no' || $value === 'hayır' || $value === 'hayir') {
			return 0;
		}

		return (int) $value > 0 ? 1 : 0;
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message, 'id' => 0];
	}
	
	public static function refreshCurrencyPrices(): int
	{
		self::ensureSchema();

		$rows = DB::execute(
			"SELECT id_product, doviz, doviz_price, doviz_old_price
			FROM products
			WHERE doviz IS NOT NULL AND doviz != '' AND doviz != 'try'"
		) ?: [];

		$updated = 0;

		foreach ($rows as $row) {
			$id = (int) $row['id_product'];
			$currency = strtolower(trim((string) $row['doviz']));
			$price = self::kurPrice((float) $row['doviz_price'], $currency);
			$oldPrice = self::kurPrice((float) $row['doviz_old_price'], $currency);

			$ok = DB::update(
				'products',
				[
					'price' => max(0, $price),
					'old_price' => max(0, $oldPrice),
				],
				'id_product = :where_id',
				['where_id' => $id]
			);

			if ($ok !== false) {
				$updated++;
			}
		}

		return $updated;
	}

	private static function kurPrice(float $price, string $currency): float
	{
		if ($price <= 0) {
			return 0.0;
		}

		$currency = strtolower(trim($currency));

		if ($currency === '' || $currency === 'try') {
			return round($price, 2);
		}

		$symbols = [
			'usd' => 'USDTRY',
			'eur' => 'EURTRY',
			'xau' => 'GLDGR',
		];

		if (!isset($symbols[$currency])) {
			return round($price, 2);
		}

		$rate = self::fetchExchangeRate($symbols[$currency]);

		if ($rate <= 0) {
			return 0.0;
		}

		return round($price * $rate, 2);
	}

	private static function fetchExchangeRate(string $symbol): float
	{
		static $rates = [];

		if (isset($rates[$symbol])) {
			return $rates[$symbol];
		}

		$urls = [
			'https://api.bigpara.hurriyet.com.tr/doviz/headerlist/anasayfa',
			'http://api.bigpara.hurriyet.com.tr/doviz/headerlist/anasayfa',
		];

		$json = false;
		$context = stream_context_create(['http' => ['timeout' => 8]]);

		foreach ($urls as $url) {
			$json = @file_get_contents($url, false, $context);

			if ($json !== false && $json !== '') {
				break;
			}
		}

		if ($json === false || $json === '') {
			return 0.0;
		}

		$payload = json_decode($json);

		if (!isset($payload->data) || !is_array($payload->data)) {
			return 0.0;
		}

		foreach ($payload->data as $item) {
			if (!isset($item->SEMBOL) || $item->SEMBOL !== $symbol) {
				continue;
			}

			$rate = (float) ($item->ALIS ?? 0);
			$rates[$symbol] = $rate;

			return $rate;
		}

		return 0.0;
	}
}
