<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class QuestionModule extends ModuleBase
{
	public string $name = 'question';
	public string $title = 'Soru Sor';
	public string $version = '1.0.0';
	public string $description = 'Ürün hakkında soru alma ve cevaplama';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_tab' => 'Ürün Tabı',
		'product_tab_content' => 'Ürün sayfası',
	];

	public array $defaultDisplayHooks = ['product_tab', 'product_tab_content'];

	public array $frontStylesheets = ['question.css'];
	public array $frontScripts = ['question.js'];

	public array $apiActions = [
		'submit' => 'api/submit.php',
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
		$table = DB::execute("SHOW TABLES LIKE 'product_questions'");

		if (empty($table)) {
			$this->runSqlFile('install.sql');
		}
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';

		if (Tools::isSubmit('questionAction')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$id = (int) Tools::getValue('id_question');
				$action = (string) Tools::getValue('action');

				switch ($action) {
					case 'answer':
						$result = self::saveAnswer($id, (string) Tools::getValue('answer'));
						break;
					case 'hide':
						$result = self::setActive($id, false);
						break;
					case 'publish':
						$result = self::setActive($id, true);
						break;
					case 'delete':
						$result = self::delete($id);
						break;
					default:
						$result = ['success' => false, 'message' => 'Geçersiz işlem'];
				}

				$flash = $result['message'];
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
			Admin::url($this->getAdminSlug()) . '?filter=' . rawurlencode($filter)
		);

		$smarty->assign([
			'questions' => self::getAdminList($filter, $perPage, $pagination['offset']),
			'pagination' => $pagination,
			'filter' => $filter,
			'pendingCount' => self::countAdmin('pending'),
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if (!in_array($hook, ['product_tab', 'product_tab_content'], true)) {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return null;
		}

		global $domain;

		$questionCount = self::countPublished($idProduct);

		$html = $this->renderFrontTemplate($hook, [
			'id_product' => $idProduct,
			'questions' => self::getPublishedForProduct($idProduct),
			'questionCount' => $questionCount,
			'isLoggedIn' => Customer::isLoggedIn(),
			'questionApiUrl' => rtrim($domain, '/') . '/api/module.php?m=question&action=submit',
			'askerName' => Customer::isLoggedIn() ? (Customer::getCurrent()['user_full_name'] ?? '') : '',
		]);

		if ($hook === 'product_tab_content' && Customer::isLoggedIn()) {
			$_SESSION['question_form_started'] = time();
		}

		return $html !== '' ? $html : null;
	}

	public static function submit(int $idProduct, string $question, ?int $idUser = null, array $security = []): array
	{
		if (!$idUser || !Customer::isLoggedIn() || Customer::getId() !== $idUser) {
			return [
				'success' => false,
				'message' => 'Soru sormak için giriş yapmalısınız',
				'login_required' => true,
			];
		}

		$botCheck = self::validateAntiBot($security);
		if ($botCheck !== null) {
			return $botCheck;
		}

		$idProduct = (int) $idProduct;
		$question = trim(strip_tags($question));

		if ($idProduct <= 0 || !Product::getById($idProduct)) {
			return ['success' => false, 'message' => 'Ürün bulunamadı'];
		}

		if ($question === '' || Tools::strlen($question) < 10) {
			return ['success' => false, 'message' => 'Soru en az 10 karakter olmalı'];
		}

		if (Tools::strlen($question) > 1000) {
			return ['success' => false, 'message' => 'Soru en fazla 1000 karakter olabilir'];
		}

		if (self::looksLikeSpam($question)) {
			return ['success' => true, 'message' => 'Sorunuz alındı. Cevaplandığında burada yayınlanacak.'];
		}

		$user = Customer::getCurrent();

		if (!$user || (int) $user['id_user'] !== $idUser) {
			return ['success' => false, 'message' => 'Oturum geçersiz'];
		}

		$authorName = trim((string) ($user['user_full_name'] ?? ''));

		if ($authorName === '') {
			$authorName = 'Müşteri';
		}

		$recentCount = (int) DB::getValue(
			'SELECT COUNT(*) FROM product_questions WHERE id_user = ? AND date_add > DATE_SUB(NOW(), INTERVAL 1 HOUR)',
			[$idUser]
		);

		if ($recentCount >= 5) {
			return ['success' => false, 'message' => 'Saatte en fazla 5 soru gönderebilirsiniz'];
		}

		$id = DB::insert('product_questions', [
			'id_product' => $idProduct,
			'id_user' => $idUser,
			'author_name' => mb_substr($authorName, 0, 128),
			'question' => $question,
			'answer' => '',
			'active' => 0,
		]);

		if ($id) {
			$_SESSION['question_last_submit'] = time();
			unset($_SESSION['question_form_started']);
		}

		return $id
			? ['success' => true, 'message' => 'Sorunuz alındı. Cevaplandığında burada yayınlanacak.']
			: ['success' => false, 'message' => 'Soru kaydedilemedi'];
	}

	public static function saveAnswer(int $id, string $answer): array
	{
		$row = DB::getRowSafe('product_questions', 'id_question = ?', [$id]);

		if (!$row) {
			return ['success' => false, 'message' => 'Soru bulunamadı'];
		}

		$answer = trim(strip_tags($answer));

		if ($answer === '' || Tools::strlen($answer) < 3) {
			return ['success' => false, 'message' => 'Cevap en az 3 karakter olmalı'];
		}

		$updated = DB::update(
			'product_questions',
			[
				'answer' => $answer,
				'active' => 1,
				'date_answer' => date('Y-m-d H:i:s'),
			],
			'id_question = :id_question',
			['id_question' => $id]
		);

		if ($updated === false) {
			return ['success' => false, 'message' => 'Cevap kaydedilemedi'];
		}

		return ['success' => true, 'message' => 'Cevap kaydedildi ve yayınlandı'];
	}

	public static function getPublishedForProduct(int $idProduct, int $limit = 50): array
	{
		$rows = DB::execute(
			'SELECT * FROM product_questions
			 WHERE id_product = ? AND active = 1 AND answer <> \'\'
			 ORDER BY date_answer DESC, date_add DESC
			 LIMIT ' . (int) $limit,
			[$idProduct]
		) ?: [];

		return array_map([self::class, 'formatRow'], $rows);
	}

	public static function countPublished(int $idProduct): int
	{
		return (int) DB::getValue(
			'SELECT COUNT(*) FROM product_questions WHERE id_product = ? AND active = 1 AND answer <> \'\'',
			[$idProduct]
		);
	}

	public static function getAdminList(string $filter, int $limit, int $offset): array
	{
		$sql = 'SELECT q.*, p.product_name
			FROM product_questions q
			INNER JOIN products p ON p.id_product = q.id_product
			WHERE 1=1';
		$params = [];

		if ($filter === 'pending') {
			$sql .= ' AND (q.answer = \'\' OR q.active = 0)';
		} elseif ($filter === 'answered') {
			$sql .= ' AND q.answer <> \'\' AND q.active = 1';
		}

		$sql .= ' ORDER BY q.id_question DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params) ?: [];

		return array_map(static function (array $row) {
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
			$row['answer_formatted'] = !empty($row['date_answer']) ? Tools::formatDate3($row['date_answer']) : '';

			return $row;
		}, $rows);
	}

	public static function countAdmin(string $filter = 'all'): int
	{
		$sql = 'SELECT COUNT(*) FROM product_questions WHERE 1=1';

		if ($filter === 'pending') {
			$sql .= ' AND (answer = \'\' OR active = 0)';
		} elseif ($filter === 'answered') {
			$sql .= ' AND answer <> \'\' AND active = 1';
		}

		return (int) DB::getValue($sql);
	}

	public static function setActive(int $id, bool $active): array
	{
		$row = DB::getRowSafe('product_questions', 'id_question = ?', [$id]);

		if (!$row) {
			return ['success' => false, 'message' => 'Soru bulunamadı'];
		}

		if ($active && trim((string) $row['answer']) === '') {
			return ['success' => false, 'message' => 'Yayınlamak için önce cevap yazın'];
		}

		DB::update('product_questions', ['active' => $active ? 1 : 0], 'id_question = :id_question', [
			'id_question' => $id,
		]);

		return ['success' => true, 'message' => $active ? 'Soru yayında' : 'Soru gizlendi'];
	}

	public static function delete(int $id): array
	{
		DB::execute('DELETE FROM product_questions WHERE id_question = ?', [$id]);

		return ['success' => true, 'message' => 'Soru silindi'];
	}

	private static function formatRow(array $row): array
	{
		$row['date_formatted'] = Tools::formatDate3($row['date_add']);
		$row['answer_formatted'] = !empty($row['date_answer']) ? Tools::formatDate3($row['date_answer']) : '';
		$row['author_masked'] = Tools::maskName($row['author_name']);

		return $row;
	}

	private static function validateAntiBot(array $security): ?array
	{
		if (trim((string) ($security['website'] ?? '')) !== '') {
			return ['success' => true, 'message' => 'Sorunuz alındı. Cevaplandığında burada yayınlanacak.'];
		}

		$started = (int) ($_SESSION['question_form_started'] ?? 0);
		$elapsed = $started > 0 ? time() - $started : 0;

		if ($started <= 0 || $elapsed < 3) {
			return ['success' => false, 'message' => 'Lütfen formu doldurduktan birkaç saniye sonra gönderin'];
		}

		if ($elapsed > 7200) {
			return ['success' => false, 'message' => 'Form süresi doldu. Sayfayı yenileyip tekrar deneyin'];
		}

		$lastSubmit = (int) ($_SESSION['question_last_submit'] ?? 0);
		if ($lastSubmit > 0 && time() - $lastSubmit < 60) {
			return ['success' => false, 'message' => 'Çok sık soru gönderiyorsunuz. Lütfen bir dakika bekleyin'];
		}

		return null;
	}

	private static function looksLikeSpam(string $text): bool
	{
		if (preg_match_all('/https?:\/\//i', $text) >= 2) {
			return true;
		}

		if (preg_match('/\b(viagra|cialis|casino|porn|xxx|click here|buy now)\b/i', $text)) {
			return true;
		}

		return false;
	}
}
