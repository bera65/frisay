<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once dirname(__DIR__, 2) . '/core/Theme.php';
require_once __DIR__ . '/lib/FthemeCss.php';

class FthemeEditModule extends ModuleBase
{
	public string $name = 'ftheme-edit';
	public string $title = 'Theme Edit';
	public string $version = '1.0.0';
	public string $description = 'F Yazilim tema ayarları';
	public string $author = 'FShop';

	public array $apiActions = [
		'callback' => 'api/callback.php',
	];

	/** @var array<string, string> */
	private const DEFAULTS = [
		'HEADER' => '1',
		'FOOTER' => '1',
		'LOADING' => '0',
		'DEFAULT-COLOR' => '2563EB',
		'THEME-FONT' => 'Poppins',
		'FEATURE-TITLE' => 'Öne Çıkan Modüller',
		'FEATURE-DESC' => 'Frisay uyumlu, kuruluma hazır e-ticaret modülleri',
		'FOOTER-TEXT' => 'Frisay açık kaynak e-ticaret altyapısı ile güçlendirilmiş modern mağaza deneyimi.',
		'GOTO-TOP' => '1',
		'SHOW-COOKIE' => '0',
		'COOKIE-TEXT' => 'Deneyiminizi iyileştirmek için çerezler kullanıyoruz. Siteyi kullanmaya devam ederek çerez politikamızı kabul etmiş olursunuz.',
		'SHOW-TOP-BAR' => '1',
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
		if (!Module::isInstalled($this->name)) {
			return;
		}

		$this->ensureDefaultSettings();

		Module::registerHook('smarty.assign', function ($smarty): void {
			if (!$smarty || defined('IN_ADMIN')) {
				return;
			}

			$smarty->assign([
				'fheader' => (int) $this->getSettings('HEADER', '1'),
				'ffoter' => (int) $this->getSettings('FOOTER', '1'),
				'loading' => (int) $this->getSettings('LOADING', '0'),
				'dcolor' => $this->getSettings('DEFAULT-COLOR', '2563EB'),
				'themeFont' => $this->getSettings('THEME-FONT', 'Poppins'),
				'featureTitle' => $this->getSettings('FEATURE-TITLE', self::DEFAULTS['FEATURE-TITLE']),
				'featureDesc' => $this->getSettings('FEATURE-DESC', self::DEFAULTS['FEATURE-DESC']),
				'fText' => $this->getSettings('FOOTER-TEXT', self::DEFAULTS['FOOTER-TEXT']),
				'gotoTop' => (int) $this->getSettings('GOTO-TOP', '1'),
				'showCookie' => (int) $this->getSettings('SHOW-COOKIE', '0'),
				'cookieText' => $this->getSettings('COOKIE-TEXT', self::DEFAULTS['COOKIE-TEXT']),
				'showTopBar' => (int) $this->getSettings('SHOW-TOP-BAR', '1'),
			]);
		});
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'info';
		$tab = (string) Tools::getValue('tab', 'settings');

		if (!in_array($tab, ['settings', 'colors', 'css'], true)) {
			$tab = 'settings';
		}

		$this->ensureDefaultSettings();
		$theme = FthemeCss::getTargetTheme();

		if (Tools::isSubmit('saveSettings')) {
			$result = $this->handleSaveSettings($adminToken);
			$flash = $result['message'];
			$flashType = $result['success'] ? 'success' : 'danger';
			$tab = 'settings';
		}

		if (Tools::isSubmit('saveThemeColors')) {
			$result = $this->handleSaveColors($adminToken);
			$flash = $result['message'];
			$flashType = $result['success'] ? 'success' : 'danger';
			$tab = 'colors';
		}

		if (Tools::isSubmit('saveCustomCss')) {
			$result = $this->handleSaveCustomCss($adminToken);
			$flash = $result['message'];
			$flashType = $result['success'] ? 'success' : 'danger';
			$tab = 'css';
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'activeTab' => $tab,
			'targetTheme' => $theme,
			'colorsPath' => FthemeCss::colorsPath($theme),
			'customCssPath' => FthemeCss::customCssPath($theme),
			'fthemeSettings' => $this->getAllSettings(),
			'fthemeColors' => FthemeCss::readColors($theme),
			'fthemeColorGroups' => FthemeCss::COLOR_GROUPS,
			'customCssContent' => FthemeCss::readCustomCss($theme),
			'headerVariants' => $this->discoverLayoutVariants('header'),
			'footerVariants' => $this->discoverLayoutVariants('footer'),
			'fontSuggestions' => ['Poppins', 'Inter', 'Roboto', 'Open Sans', 'Montserrat', 'Nunito'],
		]);
	}

	/** @return array{success: bool, message: string} */
	private function handleSaveSettings(string $adminToken): array
	{
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			return ['success' => false, 'message' => 'Geçersiz istek'];
		}

		if (!$this->saveSettingsFromPost()) {
			return ['success' => false, 'message' => 'Tema ayarları kaydedilemedi'];
		}

		return ['success' => true, 'message' => 'Tema ayarları kaydedildi'];
	}

	/** @return array{success: bool, message: string} */
	private function handleSaveColors(string $adminToken): array
	{
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			return ['success' => false, 'message' => 'Geçersiz istek'];
		}

		$input = [];

		foreach (array_keys(FthemeCss::DEFAULT_COLORS) as $key) {
			$input[$key] = (string) Tools::getValue('color_' . $key, '');
		}

		return FthemeCss::writeColors($input);
	}

	/** @return array{success: bool, message: string} */
	private function handleSaveCustomCss(string $adminToken): array
	{
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			return ['success' => false, 'message' => 'Geçersiz istek'];
		}

		return FthemeCss::writeCustomCss((string) Tools::getValue('custom_css', ''));
	}

	public function getSettings(string $title, string $default = ''): string
	{
		if (!Validate::isGenericName($title)) {
			return $default;
		}

		$row = DB::getRowSafe('ftheme_settings', 'title = ?', [$title]);

		if (!$row || !isset($row['detail'])) {
			return $default !== '' ? $default : (string) (self::DEFAULTS[$title] ?? '');
		}

		return (string) $row['detail'];
	}

	/** @return array<string, string> */
	public function getAllSettings(): array
	{
		$settings = self::DEFAULTS;

		$rows = DB::execute('SELECT title, detail FROM ftheme_settings') ?: [];

		foreach ($rows as $row) {
			$key = (string) ($row['title'] ?? '');

			if ($key !== '') {
				$settings[$key] = (string) ($row['detail'] ?? '');
			}
		}

		return $settings;
	}

	public function setSettings(string $title, string $detail): bool
	{
		if (!Validate::isGenericName($title)) {
			return false;
		}

		$detail = mb_substr(trim($detail), 0, 480, 'UTF-8');
		$row = DB::getRowSafe('ftheme_settings', 'title = ?', [$title]);

		if ($row) {
			return DB::update('ftheme_settings', ['detail' => $detail], 'title = :where_title', ['where_title' => $title]) !== false;
		}

		return DB::insert('ftheme_settings', [
			'title' => $title,
			'detail' => $detail,
		]) !== false;
	}

	private function saveSettingsFromPost(): bool
	{
		$header = max(1, (int) Tools::getValue('header', 1));
		$footer = max(1, (int) Tools::getValue('footer', 1));
		$loading = Tools::getValue('loading') ? '1' : '0';
		$gotoTop = Tools::getValue('goto_top') ? '1' : '0';
		$showCookie = Tools::getValue('show_cookie') ? '1' : '0';
		$showTopBar = Tools::getValue('show_top_bar') ? '1' : '0';

		$dcolor = preg_replace('/[^0-9a-fA-F]/', '', (string) Tools::getValue('default_color', '2563EB'));
		if (strlen($dcolor) !== 6) {
			$dcolor = '2563EB';
		}

		$themeFont = preg_replace('/[^A-Za-z0-9\s+\-]/', '', (string) Tools::getValue('theme_font', 'Poppins'));
		$themeFont = trim($themeFont) !== '' ? trim($themeFont) : 'Poppins';

		$map = [
			'HEADER' => (string) $header,
			'FOOTER' => (string) $footer,
			'LOADING' => $loading,
			'DEFAULT-COLOR' => strtoupper($dcolor),
			'THEME-FONT' => $themeFont,
			'FEATURE-TITLE' => trim((string) Tools::getValue('feature_title', '')),
			'FEATURE-DESC' => trim((string) Tools::getValue('feature_desc', '')),
			'FOOTER-TEXT' => trim((string) Tools::getValue('footer_text', '')),
			'GOTO-TOP' => $gotoTop,
			'SHOW-COOKIE' => $showCookie,
			'COOKIE-TEXT' => trim((string) Tools::getValue('cookie_text', '')),
			'SHOW-TOP-BAR' => $showTopBar,
		];

		foreach ($map as $title => $detail) {
			if (!$this->setSettings($title, $detail)) {
				return false;
			}
		}

		return true;
	}

	private function ensureDefaultSettings(): void
	{
		foreach (self::DEFAULTS as $title => $detail) {
			$row = DB::getRowSafe('ftheme_settings', 'title = ?', [$title]);

			if (!$row) {
				$this->setSettings($title, $detail);
			}
		}
	}

	/** @return array<string, string> */
	private function discoverLayoutVariants(string $prefix): array
	{
		$theme = FthemeCss::getTargetTheme();
		$dir = Theme::templatesPath() . '/' . $theme . '/_mini';
		$variants = [];

		if (!is_dir($dir)) {
			return ['1' => ucfirst($prefix) . ' 1'];
		}

		foreach (scandir($dir) ?: [] as $entry) {
			if (!preg_match('/^' . preg_quote($prefix, '/') . '(\d+)\.tpl$/', $entry, $matches)) {
				continue;
			}

			$key = $matches[1];
			$variants[$key] = ucfirst($prefix) . ' ' . $key;
		}

		if ($variants === []) {
			$variants['1'] = ucfirst($prefix) . ' 1';
		}

		ksort($variants, SORT_NUMERIC);

		return $variants;
	}
}
