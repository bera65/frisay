<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/DiscountTimerService.php';

class DiscountTimerModule extends ModuleBase
{
	public string $name = 'discount-timer';
	public string $title = 'İndirim Sayacı';
	public string $version = '1.1.0';
	public string $description = 'Ürün indirimlerinin kalan süresini geri sayım ile gösterir; tarih aralığında geçerli indirim';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_inf' => 'Ürün başlığı altında indirim sayacı',
		'admin_product_button' => 'Admin ürün formunda indirim tarihleri',
	];

	public array $defaultDisplayHooks = ['product_inf', 'admin_product_button'];

	public array $frontStylesheets = ['front.css'];
	public array $frontScripts = ['front.js'];
	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public array $apiActions = [
		'cron' => 'api/cron.php',
	];

	public function install(): bool
	{
		DiscountTimerService::ensureSchema();

		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		DiscountTimerService::ensureSchema();

		Module::registerHook('smarty.assign', static function ($smarty): void {
			if (defined('IN_ADMIN')) {
				return;
			}

			DiscountTimerService::patchSmartyProductVars($smarty);
		});

		Module::registerHook('product.updated', static function ($idProduct): void {
			$idProduct = (int) $idProduct;

			if ($idProduct <= 0) {
				return;
			}

			if (!Tools::isSubmit('saveProduct')
				&& !array_key_exists('discount_timer_end', $_POST)
				&& !array_key_exists('discount_timer_start', $_POST)) {
				return;
			}

			$start = trim((string) Tools::getValue('discount_timer_start', ''));
			$end = trim((string) Tools::getValue('discount_timer_end', ''));

			DiscountTimerService::saveSchedule(
				$idProduct,
				$start !== '' ? $start : null,
				$end !== '' ? $end : null
			);
		});
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'product_inf') {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return null;
		}

		$product = Product::getById($idProduct);

		if (!$product) {
			return null;
		}

		$timer = DiscountTimerService::getActiveForProduct($idProduct, $product);

		if ($timer === null) {
			return null;
		}

		$html = $this->renderFrontTemplate('product_inf', [
			'id_product' => $idProduct,
			'ends_ts' => $timer['ends_ts'],
			'ends_iso' => date('c', $timer['ends_ts']),
			'discount_pct' => $timer['discount_pct'],
			'title' => DiscountTimerService::getTitle(),
			'subtitle' => DiscountTimerService::getSubtitle(),
			'position' => DiscountTimerService::getPosition(),
		]);

		return $html !== '' ? $html : null;
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_product_button') {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);
		$isNew = !empty($context['is_new']);

		if ($isNew || $idProduct <= 0) {
			return null;
		}

		$row = DiscountTimerService::getByProduct($idProduct);

		$html = $this->renderAdminTemplate('admin_product_button', [
			'starts_at_input' => DiscountTimerService::toInputValue($row['starts_at'] ?? null),
			'ends_at_input' => DiscountTimerService::toInputValue($row['ends_at'] ?? null),
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$domain = rtrim((string) Settings::get('DOMAIN'), '/');

		if (Tools::isSubmit('saveDiscountTimer')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('DISCOUNT_TIMER_TITLE', trim((string) Tools::getValue('timer_title')));
				Settings::set('DISCOUNT_TIMER_SUBTITLE', trim((string) Tools::getValue('timer_subtitle')));
				Settings::set('DISCOUNT_TIMER_POSITION', Tools::getValue('timer_position') === 'inf' ? 'inf' : 'top');
				$flash = 'İndirim sayacı ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'flash' => $flash,
			'timerTitle' => DiscountTimerService::getTitle(),
			'timerSubtitle' => DiscountTimerService::getSubtitle(),
			'timerPosition' => DiscountTimerService::getPosition(),
			'cronUrl' => $domain . '/api/module.php?m=discount-timer&action=cron&token=SHOP_TOKEN',
			'adminToken' => $adminToken,
		]);
	}
}
