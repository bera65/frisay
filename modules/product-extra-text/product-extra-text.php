<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class ProductExtraTextModule extends ModuleBase
{
	public string $name = 'product-extra-text';
	public string $title = 'Ürün Ek Metin';
	public string $version = '1.1.0';
	public string $description = 'Tüm ürün sayfalarında gösterilen ortak metin/HTML alanı';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_detail' => 'Ürün sayfası — ortak metin alanı',
	];

	public array $defaultDisplayHooks = ['product_detail'];

	public array $frontStylesheets = ['front.css'];

	private const SETTING_CONTENT = 'PRODUCT_EXTRA_TEXT_CONTENT';
	private const SETTING_ACTIVE = 'PRODUCT_EXTRA_TEXT_ACTIVE';

	public function install(): bool
	{
		if (Settings::get(self::SETTING_CONTENT) === '') {
			Settings::set(self::SETTING_CONTENT, '');
		}

		if (Settings::get(self::SETTING_ACTIVE) === '') {
			Settings::set(self::SETTING_ACTIVE, '1');
		}

		return true;
	}

	public function uninstall(): bool
	{
		$table = DB::execute("SHOW TABLES LIKE 'product_extra_text'");
		if (!empty($table)) {
			DB::execute('DROP TABLE IF EXISTS `product_extra_text`');
		}

		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';

		if (Tools::isSubmit('saveProductExtraText')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$content = (string) Tools::getValue('content', '');
				$active = (int) Tools::getValue('active', 0) === 1 ? '1' : '0';

				Settings::set(self::SETTING_CONTENT, $content);
				Settings::set(self::SETTING_ACTIVE, $active);

				$flash = 'Ayarlar kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'content' => Settings::get(self::SETTING_CONTENT),
			'active' => Settings::get(self::SETTING_ACTIVE) !== '0',
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'product_detail') {
			return null;
		}

		if (Settings::get(self::SETTING_ACTIVE) === '0') {
			return null;
		}

		$content = trim((string) Settings::get(self::SETTING_CONTENT));

		if ($content === '') {
			return null;
		}

		$html = $this->renderFrontTemplate('product_detail', [
			'content' => $content,
		]);

		return $html !== '' ? $html : null;
	}
}
