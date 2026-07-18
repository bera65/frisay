<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/ReviewsInviteService.php';

class ReviewsModule extends ModuleBase
{
	public string $name = 'reviews';
	public string $title = 'Ürün Yorumları';
	public string $version = '1.1.0';
	public string $description = 'Ürün yorumları, puanlama ve teslim sonrası yorum davet e-postası';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_tab' => 'Ürün Tabı',
		'product_tab_content' => 'Ürün sayfası',
		'product_inf' => 'Yıldız gösterme',
	];

	public array $defaultDisplayHooks = ['product_tab', 'product_tab_content', 'product_inf'];

	public array $frontStylesheets = ['reviews.css'];
	public array $frontScripts = ['reviews.js'];

	public array $apiActions = [
		'submit' => 'api/submit.php',
		'cron' => 'api/cron.php',
	];

	public function install(): bool
	{
		$ok = $this->runSqlFile('install.sql');
		ReviewsInviteService::ensureSchema();
		ReviewsInviteService::ensureDefaultSettings();

		return $ok;
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		ReviewsInviteService::ensureSchema();
		ReviewsInviteService::ensureDefaultSettings();

		Module::registerHook('order.updated', function ($order, $oldStatus): void {
			if (!is_array($order)) {
				return;
			}

			ReviewsInviteService::handleOrderStatusChange(
				$order,
				(int) $oldStatus,
				(int) ($order['status'] ?? 0)
			);
		});
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken, $domain;

		$flash = '';
		$flashType = 'success';
		$tab = (string) Tools::getValue('tab', 'reviews');

		if (!in_array($tab, ['reviews', 'invite', 'queue'], true)) {
			$tab = 'reviews';
		}

		if (Tools::isSubmit('reviewAction')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$id = (int) Tools::getValue('id_review');
				$action = (string) Tools::getValue('action');

				switch ($action) {
					case 'approve':
						$result = self::setActive($id, true);
						break;
					case 'reject':
						$result = self::setActive($id, false);
						break;
					case 'delete':
						$result = self::delete($id);
						break;
					default:
						$result = ['success' => false, 'message' => 'Geçersiz işlem'];
				}

				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		if (Tools::isSubmit('saveReviewInviteSettings')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$result = ReviewsInviteService::saveSettings([
					'enabled' => Tools::getValue('invite_enabled'),
					'delay_days' => Tools::getValue('delay_days'),
					'subject' => Tools::getValue('email_subject'),
					'body' => Tools::getValue('email_body'),
					'coupon_enabled' => Tools::getValue('coupon_enabled'),
					'coupon_type' => Tools::getValue('coupon_type'),
					'coupon_value' => Tools::getValue('coupon_value'),
					'coupon_min_cart' => Tools::getValue('coupon_min_cart'),
					'coupon_valid_days' => Tools::getValue('coupon_valid_days'),
					'coupon_prefix' => Tools::getValue('coupon_prefix'),
				]);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
				$tab = 'invite';
			}
		}

		if (Tools::isSubmit('runReviewInviteCron')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$batch = ReviewsInviteService::processPendingBatch(50);
				$flash = $batch['processed'] . ' kayıt işlendi — '
					. $batch['sent'] . ' gönderildi, '
					. $batch['failed'] . ' hata, '
					. $batch['skipped'] . ' atlandı';
				$tab = 'queue';
			} else {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			}
		}

		$filter = (string) Tools::getValue('filter', 'pending');
		$currentPage = max(1, (int) Tools::getValue('page'));
		$perPage = 30;
		$total = self::countAdmin($filter);
		$pagination = Pagination::build(
			$total,
			$currentPage,
			$perPage,
			Admin::url($this->getAdminSlug()) . '?tab=reviews&filter=' . rawurlencode($filter)
		);

		$shopToken = (string) Settings::get('SHOP_TOKEN');
		$inviteSettings = ReviewsInviteService::getSettings();

		$smarty->assign([
			'tab' => $tab,
			'reviews' => self::getAdminList($filter, $perPage, $pagination['offset']),
			'pagination' => $pagination,
			'filter' => $filter,
			'pendingCount' => self::countAdmin('pending'),
			'flash' => $flash,
			'flashType' => $flashType,
			'inviteSettings' => $inviteSettings,
			'queueRows' => ReviewsInviteService::getQueueList(150),
			'queueStats' => ReviewsInviteService::getQueueStats(),
			'cronUrl' => rtrim($domain, '/') . '/api/module.php?m=reviews&action=cron&token=' . rawurlencode($shopToken),
			'adminUseEditor' => $tab === 'invite',
			'placeholders' => [
				'{customer_name}',
				'{customer_email}',
				'{order_reference}',
				'{products_list}',
				'{coupon_code}',
				'{coupon_info}',
				'{site_name}',
			],
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if (!in_array($hook, ['product_tab', 'product_tab_content', 'product_inf'], true)) {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return null;
		}

		global $domain;

		$averageRating = self::getAverageRating($idProduct);
		$reviewCount = self::countApproved($idProduct);

		$html = $this->renderFrontTemplate($hook, [
			'id_product' => $idProduct,
			'reviews' => self::getApprovedForProduct($idProduct),
			'averageRating' => $averageRating,
			'averageRatingLabel' => number_format($averageRating, 1, ',', ''),
			'reviewCount' => $reviewCount,
			'ratingBars' => self::getRatingDistribution($idProduct, $reviewCount),
			'isLoggedIn' => Customer::isLoggedIn(),
			'reviewApiUrl' => rtrim($domain, '/') . '/api/module.php?m=reviews&action=submit',
			'reviewerName' => Customer::isLoggedIn() ? (Customer::getCurrent()['user_full_name'] ?? '') : '',
		]);

		if ($hook === 'product_tab_content' && Customer::isLoggedIn()) {
			$_SESSION['review_form_started'] = time();
		}

		return $html !== '' ? $html : null;
	}

	public static function submit(int $idProduct, int $rating, string $title, string $comment, ?int $idUser = null, string $authorName = '', array $security = []): array
	{
		if (!$idUser || !Customer::isLoggedIn() || Customer::getId() !== $idUser) {
			return [
				'success' => false,
				'message' => 'Yorum yazmak için giriş yapmalısınız',
				'login_required' => true,
			];
		}

		$botCheck = self::validateAntiBot($security);
		if ($botCheck !== null) {
			return $botCheck;
		}

		$idProduct = (int) $idProduct;
		$rating = max(1, min(5, $rating));
		$title = trim(strip_tags($title));
		$comment = trim(strip_tags($comment));

		if ($idProduct <= 0 || !Product::getById($idProduct)) {
			return ['success' => false, 'message' => 'Ürün bulunamadı'];
		}

		if ($comment === '' || Tools::strlen($comment) < 10) {
			return ['success' => false, 'message' => 'Yorum en az 10 karakter olmalı'];
		}

		if (Tools::strlen($comment) > 2000) {
			return ['success' => false, 'message' => 'Yorum en fazla 2000 karakter olabilir'];
		}

		if (self::looksLikeSpam($comment, $title)) {
			return ['success' => true, 'message' => 'Yorumunuz alındı. Onaylandıktan sonra yayınlanacak.'];
		}

		$user = Customer::getCurrent();

		if (!$user || (int) $user['id_user'] !== $idUser) {
			return ['success' => false, 'message' => 'Oturum geçersiz'];
		}

		$authorName = trim((string) ($user['user_full_name'] ?? ''));

		if ($authorName === '') {
			$authorName = 'Müşteri';
		}

		$existing = DB::getValue(
			'SELECT id_review FROM product_reviews WHERE id_product = ? AND id_user = ? LIMIT 1',
			[$idProduct, $idUser]
		);

		if ($existing) {
			return ['success' => false, 'message' => 'Bu ürün için zaten yorum yaptınız'];
		}

		$recentCount = (int) DB::getValue(
			'SELECT COUNT(*) FROM product_reviews WHERE id_user = ? AND date_add > DATE_SUB(NOW(), INTERVAL 1 HOUR)',
			[$idUser]
		);

		if ($recentCount >= 5) {
			return ['success' => false, 'message' => 'Saatte en fazla 5 yorum gönderebilirsiniz'];
		}

		$id = DB::insert('product_reviews', [
			'id_product' => $idProduct,
			'id_user' => $idUser,
			'author_name' => mb_substr($authorName, 0, 128),
			'rating' => $rating,
			'title' => mb_substr($title, 0, 255),
			'comment' => $comment,
			'active' => 0,
		]);

		if ($id) {
			$_SESSION['review_last_submit'] = time();
			unset($_SESSION['review_form_started']);
		}

		return $id
			? ['success' => true, 'message' => 'Yorumunuz alındı. Onaylandıktan sonra yayınlanacak.']
			: ['success' => false, 'message' => 'Yorum kaydedilemedi'];
	}

	private static function validateAntiBot(array $security): ?array
	{
		if (trim((string) ($security['website'] ?? '')) !== '') {
			return ['success' => true, 'message' => 'Yorumunuz alındı. Onaylandıktan sonra yayınlanacak.'];
		}

		$started = (int) ($_SESSION['review_form_started'] ?? 0);
		$elapsed = $started > 0 ? time() - $started : 0;

		if ($started <= 0 || $elapsed < 3) {
			return ['success' => false, 'message' => 'Lütfen formu doldurduktan birkaç saniye sonra gönderin'];
		}

		if ($elapsed > 7200) {
			return ['success' => false, 'message' => 'Form süresi doldu. Sayfayı yenileyip tekrar deneyin'];
		}

		$lastSubmit = (int) ($_SESSION['review_last_submit'] ?? 0);
		if ($lastSubmit > 0 && time() - $lastSubmit < 60) {
			return ['success' => false, 'message' => 'Çok sık yorum gönderiyorsunuz. Lütfen bir dakika bekleyin'];
		}

		return null;
	}

	private static function looksLikeSpam(string $comment, string $title): bool
	{
		$text = $comment . ' ' . $title;

		if (preg_match_all('/https?:\/\//i', $text) >= 2) {
			return true;
		}

		if (preg_match('/\b(viagra|cialis|casino|porn|xxx|click here|buy now)\b/i', $text)) {
			return true;
		}

		if (preg_match('/(.)\1{8,}/u', $comment)) {
			return true;
		}

		$letters = preg_replace('/[^a-zA-ZÇĞİÖŞÜçğıöşü]/u', '', $comment);
		if ($letters !== '' && mb_strlen($letters) >= 20) {
			$upper = preg_replace('/[^A-ZÇĞİÖŞÜ]/u', '', $comment);
			if (mb_strlen($upper) / mb_strlen($letters) > 0.7) {
				return true;
			}
		}

		return false;
	}

	public static function getApprovedForProduct(int $idProduct, int $limit = 20): array
	{
		$rows = DB::execute(
			'SELECT * FROM product_reviews
			 WHERE id_product = ? AND active = 1
			 ORDER BY date_add DESC
			 LIMIT ' . (int) $limit,
			[$idProduct]
		) ?: [];

		return array_map([self::class, 'formatRow'], $rows);
	}

	public static function getAverageRating(int $idProduct): float
	{
		return round((float) DB::getValue(
			'SELECT AVG(rating) FROM product_reviews WHERE id_product = ? AND active = 1',
			[$idProduct]
		), 1);
	}

	public static function countApproved(int $idProduct): int
	{
		return (int) DB::getValue(
			'SELECT COUNT(*) FROM product_reviews WHERE id_product = ? AND active = 1',
			[$idProduct]
		);
	}

	public static function getAdminList(string $filter, int $limit, int $offset): array
	{
		$sql = 'SELECT r.*, p.product_name
			FROM product_reviews r
			INNER JOIN products p ON p.id_product = r.id_product
			WHERE 1=1';
		$params = [];

		if ($filter === 'pending') {
			$sql .= ' AND r.active = 0';
		} elseif ($filter === 'approved') {
			$sql .= ' AND r.active = 1';
		}

		$sql .= ' ORDER BY r.id_review DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params) ?: [];

		return array_map(static function (array $row) {
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);

			return $row;
		}, $rows);
	}

	public static function countAdmin(string $filter = 'all'): int
	{
		$sql = 'SELECT COUNT(*) FROM product_reviews WHERE 1=1';

		if ($filter === 'pending') {
			$sql .= ' AND active = 0';
		} elseif ($filter === 'approved') {
			$sql .= ' AND active = 1';
		}

		return (int) DB::getValue($sql);
	}

	public static function setActive(int $id, bool $active): array
	{
		$row = DB::getRowSafe('product_reviews', 'id_review = ?', [$id]);

		if (!$row) {
			return ['success' => false, 'message' => 'Yorum bulunamadı'];
		}

		DB::update('product_reviews', ['active' => $active ? 1 : 0], 'id_review = :where_id', [
			'where_id' => $id,
		]);

		return ['success' => true, 'message' => $active ? 'Yorum onaylandı' : 'Yorum pasifleştirildi'];
	}

	public static function delete(int $id): array
	{
		DB::execute('DELETE FROM product_reviews WHERE id_review = ?', [$id]);

		return ['success' => true, 'message' => 'Yorum silindi'];
	}

	public static function getRatingDistribution(int $idProduct, int $total = 0): array
	{
		$counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
		$rows = DB::execute(
			'SELECT rating, COUNT(*) AS cnt FROM product_reviews
			 WHERE id_product = ? AND active = 1 GROUP BY rating',
			[$idProduct]
		) ?: [];

		foreach ($rows as $row) {
			$star = (int) $row['rating'];

			if (isset($counts[$star])) {
				$counts[$star] = (int) $row['cnt'];
			}
		}

		if ($total <= 0) {
			$total = array_sum($counts);
		}

		$bars = [];

		foreach ([5, 4, 3, 2, 1] as $star) {
			$count = $counts[$star];
			$bars[] = [
				'star' => $star,
				'count' => $count,
				'percent' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
			];
		}

		return $bars;
	}

	private static function formatRow(array $row): array
	{
		$row['date_formatted'] = self::formatReviewDate($row['date_add']);
		$row['rating'] = (int) $row['rating'];
		$row['author_initials'] = self::getInitials($row['author_name']);
		$row['author_masked'] = Tools::maskName($row['author_name']);

		return $row;
	}

	private static function formatReviewDate(string $date): string
	{
		$ts = strtotime($date);

		if (!$ts) {
			return Tools::formatDate3($date);
		}

		$months = [
			1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan',
			5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
			9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
		];
		$days = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];

		$month = (int) date('n', $ts);
		$dayIndex = (int) date('w', $ts);

		return date('d', $ts) . ' ' . ($months[$month] ?? '') . ', ' . ($days[$dayIndex] ?? '');
	}

	private static function getInitials(string $name): string
	{
		$parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
		$initials = '';

		foreach (array_slice($parts, 0, 2) as $part) {
			$initials .= mb_strtoupper(mb_substr($part, 0, 1));
		}

		return $initials !== '' ? $initials : 'M';
	}
}
