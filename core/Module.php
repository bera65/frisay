<?php

class Module
{
	private const HOOKS = [
		'smarty.assign',
		'head.assets',
		'footer.html',
		'admin.menu',
		'order.placed',
		'product.updated',
		'order.updated',
	];

	/** @var array<string, ModuleBase> */
	private static array $instances = [];

	/** @var array<string, array<int, callable>> */
	private static array $hookListeners = [];

	private static bool $booted = false;

	public static function rootPath(): string
	{
		return dirname(__DIR__) . '/modules';
	}

	public static function path(string $name): string
	{
		return self::rootPath() . '/' . $name;
	}

	public static function ensureSchema(): void
	{
		DB::execute(
			'CREATE TABLE IF NOT EXISTS `modules` (
			  `id_module` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(64) NOT NULL,
			  `version` varchar(16) NOT NULL DEFAULT \'1.0.0\',
			  `active` tinyint(1) NOT NULL DEFAULT 0,
			  `installed` tinyint(1) NOT NULL DEFAULT 0,
			  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id_module`),
			  UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
		);

		DB::execute(
			'CREATE TABLE IF NOT EXISTS `module_display_hooks` (
			  `id_hook` int(11) NOT NULL AUTO_INCREMENT,
			  `module_name` varchar(64) NOT NULL,
			  `hook_name` varchar(32) NOT NULL,
			  `position` int(11) NOT NULL DEFAULT 0,
			  PRIMARY KEY (`id_hook`),
			  UNIQUE KEY `module_hook` (`module_name`, `hook_name`),
			  KEY `hook_name` (`hook_name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
		);

		self::migrateDisplayHooks();
	}

	private static function migrateDisplayHooks(): void
	{
		if (!self::isInstalled('newsletter')) {
			return;
		}

		if (self::getAssignedDisplayHooks('newsletter') !== []) {
			return;
		}

		$module = self::loadInstance('newsletter', false);

		if ($module) {
			self::assignDefaultDisplayHooks($module);
		}
	}

	public static function bootstrap(string $context = 'front'): void
	{
		if (self::$booted) {
			return;
		}

		self::ensureSchema();
		self::$booted = true;
		self::$instances = [];
		self::$hookListeners = [];

		foreach (self::getEnabledNames() as $name) {
			$module = self::loadInstance($name);

			if ($module) {
				$module->boot();
			}
		}

		self::runHook('smarty.assign', [$GLOBALS['smarty'] ?? null]);
	}

	public static function discover(): array
	{
		$dir = self::rootPath();

		if (!is_dir($dir)) {
			return [];
		}

		$discovered = [];

		foreach (scandir($dir) ?: [] as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}

			$moduleFile = $dir . '/' . $entry . '/' . $entry . '.php';

			if (!is_file($moduleFile)) {
				continue;
			}

			$instance = self::loadInstance($entry, false);

			if ($instance) {
				$discovered[$entry] = array_merge($instance->toArray(), self::getDbRow($entry) ?: [
					'installed' => 0,
					'active' => 0,
				]);
			}
		}

		return $discovered;
	}

	public static function getHookCatalog(): array
	{
		return [
			'smarty.assign' => 'Mağaza şablonlarına değişken ekler (footer, header vb.)',
			'head.assets' => 'Sayfaya ek CSS/JS yükler',
			'footer.html' => 'Footer alanına HTML ekler (eski; tercih: display hook)',
			'admin.menu' => 'Admin menüsüne öğe ekler (varsayılan: kapalı, detay sayfası kullanın)',
			'order.placed' => 'Sipariş oluşturulunca tetiklenir',
			'product.updated' => 'Ürün admin veya API üzerinden kaydedilince tetiklenir',
			'order.updated' => 'Sipariş admin veya API üzerinden güncellenince tetiklenir',
		];
	}

	/** @return array<string, string> Şablonda {$hooks.footer} ile kullanılır */
	public static function getDisplayHookCatalog(): array
	{
		return [
			'footer' 			=> 'Footer — {$hooks.footer}',
			'header' 			=> 'Üst bar — {$hooks.header}',
			'home' 				=> 'Ana sayfa — {$hooks.home}',
			'home_slider' 		=> 'Ana sayfa üst slayt — {$hooks.home_slider}',
			'home_promo_slider' => 'Ana sayfa kampanya slaytı — {$hooks.home_promo_slider}',
			'product' 			=> 'Ürün sayfası — {$hooks.product}',
			'product_detail' 	=> 'Ürün detay sayfası — {$hooks.product_detail}',
			'product_tab' 		=> 'Ürün Tabı (sekme butonu) — {$hooks.product_tab}',
			'product_tab_content' => 'Ürün Tabı (sekme içeriği) — {$hooks.product_tab_content}',
			'product_inf' 		=> 'Ürün detay — {$hooks.product_inf}',
			'order_payment' 	=> 'Ödeme Modülü — {$hooks.order_payment}',
			'order_confirmation' => 'Sipariş onay sayfası — {$hooks.order_confirmation}',
			'auth_social'       => 'Giriş / kayıt — sosyal butonlar — {$hooks.auth_social}',
			'admin_product_button' => 'Admin ürün düzenleme — Kaydet butonu yanı — {$adminHooks.admin_product_button}',
			'admin_order_detail' => 'Admin sipariş detay — {$adminHooks.admin_order_detail}',
			'admin_dashboard_top' => 'Admin gösterge paneli — üst alan — {$adminHooks.admin_dashboard_top}',
			'admin_dashboard_kpi' => 'Admin gösterge paneli — KPI kartları altı — {$adminHooks.admin_dashboard_kpi}',
			'admin_dashboard_main_left' => 'Admin gösterge paneli — sol sütun — {$adminHooks.admin_dashboard_main_left}',
			'admin_dashboard_main_right' => 'Admin gösterge paneli — sağ sütun — {$adminHooks.admin_dashboard_main_right}',
			'admin_dashboard_bottom' => 'Admin gösterge paneli — sayfa altı — {$adminHooks.admin_dashboard_bottom}',
		];
	}

	/**
	 * @param string[] $hookNames
	 * @param array<string, mixed> $context
	 * @return array<string, string>
	 */
	public static function renderAdminHooks(array $hookNames, array $context = []): array
	{
		$hooks = [];

		foreach ($hookNames as $hookName) {
			$hookName = trim((string) $hookName);

			if ($hookName === '') {
				continue;
			}

			$hooks[$hookName] = self::renderDisplayHook($hookName, $context);
		}

		return $hooks;
	}

	/** Sayfa bağlamlı hook'ları günceller (ör. ürün sayfası) */
	public static function refreshHook($smarty, string $hookName, array $context = []): void
	{
		$hooks = $smarty->getTemplateVars('hooks');

		if (!is_array($hooks)) {
			$hooks = [];
		}

		$hooks[$hookName] = self::renderDisplayHook($hookName, $context);
		$smarty->assign('hooks', $hooks);
	}

	public static function getAssignedDisplayHooks(string $name): array
	{
		$rows = DB::execute(
			'SELECT hook_name FROM module_display_hooks WHERE module_name = ? ORDER BY position ASC, hook_name ASC',
			[$name]
		) ?: [];

		return array_column($rows, 'hook_name');
	}

	public static function setDisplayHooks(string $name, array $hookNames): array
	{
		$module = self::loadInstance($name, false);

		if (!$module) {
			return self::fail('Modül bulunamadı');
		}

		if (!self::isInstalled($name)) {
			return self::fail('Önce modülü kurun');
		}

		$allowed = $module->getSupportedDisplayHooks();
		$catalog = array_keys(self::getDisplayHookCatalog());
		$valid = [];

		foreach ($hookNames as $hook) {
			$hook = trim((string) $hook);

			if ($hook !== '' && in_array($hook, $allowed, true) && in_array($hook, $catalog, true)) {
				$valid[] = $hook;
			}
		}

		$valid = array_values(array_unique($valid));

		DB::execute('DELETE FROM module_display_hooks WHERE module_name = ?', [$name]);

		foreach ($valid as $position => $hook) {
			DB::insert('module_display_hooks', [
				'module_name' => $name,
				'hook_name' => $hook,
				'position' => $position,
			]);
		}

		return self::ok('Hook atamaları kaydedildi');
	}

	public static function renderDisplayHook(string $hookName, array $context = []): string
	{
		if (!isset(self::getDisplayHookCatalog()[$hookName])) {
			return '';
		}

		if ($hookName === 'product' && empty($context['id_product'])) {
			return '';
		}

		$rows = DB::execute(
			'SELECT mh.module_name
			 FROM module_display_hooks mh
			 INNER JOIN modules m ON m.name = mh.module_name
			 WHERE mh.hook_name = ? AND m.installed = 1 AND m.active = 1
			 ORDER BY mh.position ASC, mh.module_name ASC',
			[$hookName]
		) ?: [];

		$html = '';

		foreach ($rows as $row) {
			$module = self::loadInstance($row['module_name']);

			if (!$module) {
				continue;
			}

			if (strpos($hookName, 'admin_') === 0) {
				$chunk = $module->renderAdminDisplayHook($hookName, $context);
			} else {
				$chunk = $module->renderDisplayHook($hookName, $context);
			}

			if ($chunk !== null && $chunk !== '') {
				$html .= $chunk;
			}
		}

		return $html;
	}

	/** @return array<string, string> */
	public static function getRenderedDisplayHooks(): array
	{
		$hooks = [];
		$deferred = [
			'product',
			'product_tab',
			'product_tab_content',
			'product_inf',
			'order_payment',
			'order_confirmation',
			'auth_social',
			'admin_product_button',
			'admin_order_detail',
			'admin_dashboard_top',
			'admin_dashboard_kpi',
			'admin_dashboard_main_left',
			'admin_dashboard_main_right',
			'admin_dashboard_bottom',
		];

		foreach (array_keys(self::getDisplayHookCatalog()) as $hookName) {
			$hooks[$hookName] = in_array($hookName, $deferred, true)
				? ''
				: self::renderDisplayHook($hookName);
		}

		return $hooks;
	}

	public static function getAdminList(): array
	{
		$list = [];
		$domain = rtrim(Settings::get('DOMAIN'), '/');

		foreach (self::discover() as $name => $meta) {
			$instance = self::loadInstance($name, false);
			$row = self::getDbRow($name) ?: ['installed' => 0, 'active' => 0, 'version' => $meta['version']];
			$installed = (int) $row['installed'] === 1;
			$active = $installed && (int) $row['active'] === 1;
			$list[] = array_merge($meta, [
				'installed' => $installed,
				'active' => $active,
				'db_version' => $row['version'] ?? $meta['version'],
				'detail_url' => Admin::url('module?name=' . rawurlencode($name)),
				'configure_url' => Admin::url('module-' . $name),
				'has_configure' => $installed,
				'assigned_hooks' => self::getAssignedDisplayHooks($name),
				'icon_url' => $instance ? $instance->getLogoUrl() : '',
				'icon_letter' => mb_strtoupper(mb_substr($meta['title'], 0, 1)),
			]);
		}

		usort($list, static fn($a, $b) => strcmp($a['title'], $b['title']));

		return $list;
	}

	public static function getDetail(string $name): ?array
	{
		$instance = self::loadInstance($name, false);

		if (!$instance) {
			return null;
		}

		$row = self::getDbRow($name) ?: ['installed' => 0, 'active' => 0, 'version' => $instance->version];
		$installed = (int) $row['installed'] === 1;
		$active = $installed && (int) $row['active'] === 1;

		$adminPages = [];

		foreach ($instance->getAdminPageDefinitions() as $page) {
			$adminPages[] = [
				'slug' => $page['slug'],
				'title' => $page['title'],
				'description' => $page['description'],
				'url' => Admin::url($page['slug']),
				'usable' => $active,
			];
		}

		$apiActions = [];

		foreach ($instance->apiActions as $action => $file) {
			$apiActions[] = [
				'action' => $action,
				'endpoint' => rtrim(Settings::get('DOMAIN'), '/') . '/api/module.php?m='
					. rawurlencode($name) . '&action=' . rawurlencode($action),
			];
		}

		$displayHooks = $instance->displayHooks !== []
			? $instance->displayHooks
			: $instance->positions;

		return array_merge($instance->toArray(), [
			'installed' => $installed,
			'active' => $active,
			'db_version' => $row['version'] ?? $instance->version,
			'detail_url' => Admin::url('module?name=' . rawurlencode($name)),
			'configure_url' => Admin::url('module-' . $name),
			'admin_pages' => $adminPages,
			'api_actions' => $apiActions,
			'display_hooks' => $displayHooks,
			'assigned_hooks' => self::getAssignedDisplayHooks($name),
			'assigned_hooks_map' => array_fill_keys(self::getAssignedDisplayHooks($name), true),
			'hooks_meta' => $instance->hooksMeta,
			'has_admin_ui' => true,
			'logo_url' => $instance->getLogoUrl(),
		]);
	}

	public static function install(string $name): array
	{
		$module = self::loadInstance($name, false);

		if (!$module) {
			return self::fail('Modül bulunamadı');
		}

		if (self::isInstalled($name)) {
			return self::fail('Modül zaten kurulu');
		}

		if (!$module->install()) {
			return self::fail('Kurulum SQL hatası');
		}

		$ok = DB::insert('modules', [
			'name' => $name,
			'version' => $module->version,
			'active' => 1,
			'installed' => 1,
		]);

		if (!$ok) {
			return self::fail('Veritabanı kaydı oluşturulamadı');
		}

		self::assignDefaultDisplayHooks($module);
		self::$booted = false;

		return self::ok('Modül kuruldu ve etkinleştirildi');
	}

	public static function uninstall(string $name): array
	{
		if (!self::isInstalled($name)) {
			return self::fail('Modül kurulu değil');
		}

		$module = self::loadInstance($name, false);

		if ($module && !$module->uninstall()) {
			return self::fail('Kaldırma işlemi başarısız');
		}

		DB::execute('DELETE FROM modules WHERE name = ?', [$name]);
		DB::execute('DELETE FROM module_display_hooks WHERE module_name = ?', [$name]);
		unset(self::$instances[$name]);
		self::$booted = false;

		return self::ok('Modül kaldırıldı');
	}

	public static function setActive(string $name, bool $active): array
	{
		if (!self::isInstalled($name)) {
			return self::fail('Önce modülü kurun');
		}

		DB::update('modules', ['active' => $active ? 1 : 0], 'name = :where_name', ['where_name' => $name]);
		self::$booted = false;

		return self::ok($active ? 'Modül etkinleştirildi' : 'Modül devre dışı bırakıldı');
	}

	public static function isInstalled(string $name): bool
	{
		$row = self::getDbRow($name);

		return $row && (int) $row['installed'] === 1;
	}

	public static function isEnabled(string $name): bool
	{
		$row = self::getDbRow($name);

		return $row && (int) $row['installed'] === 1 && (int) $row['active'] === 1;
	}

	public static function resolveFrontRoute(string $slug): ?string
	{
		foreach (self::getEnabledInstances() as $module) {
			if (!isset($module->routes[$slug])) {
				continue;
			}

			$file = $module->getPath() . '/' . ltrim($module->routes[$slug], '/');

			return is_file($file) ? $file : null;
		}

		return null;
	}

	public static function resolveAdminModuleName(string $slug): ?string
	{
		if (strpos($slug, 'module-') !== 0) {
			return null;
		}

		$name = substr($slug, 7);

		if ($name === '' || !preg_match('/^[a-z0-9\-]+$/', $name)) {
			return null;
		}

		if (!is_file(self::path($name) . '/' . $name . '.php')) {
			return null;
		}

		return $name;
	}

	public static function dispatchAdminPage(string $name): void
	{
		if (!self::isInstalled($name)) {
			http_response_code(404);
			AdminPage::add('404', 'Modül kurulu değil');

			return;
		}

		$module = self::loadInstance($name, false);

		if (!$module) {
			http_response_code(404);
			AdminPage::add('404', 'Modül bulunamadı');

			return;
		}

		$module->adminPage();
		AdminPage::addModule($module);
	}

	/** @deprecated Eski modüller için dosya tabanlı admin route */
	public static function resolveAdminRoute(string $slug): ?string
	{
		foreach (self::getEnabledInstances() as $module) {
			foreach ($module->adminPages as $page) {
				if (($page['slug'] ?? '') !== $slug || empty($page['file'])) {
					continue;
				}

				$file = $module->getPath() . '/' . ltrim($page['file'], '/');

				return is_file($file) ? $file : null;
			}

			foreach ($module->adminRoutes as $pageSlug => $file) {
				if ($pageSlug !== $slug) {
					continue;
				}

				$path = $module->getPath() . '/' . ltrim($file, '/');

				return is_file($path) ? $path : null;
			}
		}

		return null;
	}

	public static function dispatchApi(string $moduleName, string $action): void
	{
		if (!self::isEnabled($moduleName)) {
			self::apiResponse(['success' => false, 'message' => 'Modül aktif değil'], 404);
		}

		$module = self::loadInstance($moduleName);

		if (!$module || !isset($module->apiActions[$action])) {
			self::apiResponse(['success' => false, 'message' => 'Geçersiz işlem'], 404);
		}

		$file = $module->getPath() . '/' . ltrim($module->apiActions[$action], '/');

		if (!is_file($file)) {
			self::apiResponse(['success' => false, 'message' => 'Endpoint bulunamadı'], 404);
		}

		require $file;
		exit;
	}

	/**
	 * Aktif ödeme modüllerinin yöntemleri.
	 * @return array<string, array{id: string, label: string, module: string}>
	 */
	public static function getPaymentMethods(): array
	{
		$methods = [];

		foreach (self::getEnabledInstances() as $module) {
			if (!$module->isPayment || $module->paymentMethodId === '') {
				continue;
			}

			$methods[$module->paymentMethodId] = [
				'id' => $module->paymentMethodId,
				'label' => $module->getPaymentMethodLabel(),
				'module' => $module->name,
			];
		}

		return $methods;
	}

	/** Ödeme yöntemi kimliğinden modül örneğini döndürür */
	public static function getPaymentModule(string $methodId): ?ModuleBase
	{
		foreach (self::getEnabledInstances() as $module) {
			if ($module->isPayment && $module->paymentMethodId === $methodId) {
				return $module;
			}
		}

		return null;
	}

	public static function registerHook(string $hook, callable $listener): void
	{
		if (!in_array($hook, self::HOOKS, true)) {
			return;
		}

		self::$hookListeners[$hook][] = $listener;
	}

	public static function runHook(string $hook, array $args = []): array
	{
		$results = [];

		foreach (self::$hookListeners[$hook] ?? [] as $listener) {
			$result = $listener(...$args);

			if ($result !== null && $result !== '') {
				$results[] = $result;
			}
		}

		return $results;
	}

	public static function getHeadAssets(): array
	{
		$assets = ['css' => [], 'js' => []];

		foreach (self::getEnabledInstances() as $module) {
			$assets['css'] = array_merge($assets['css'], $module->getFrontStylesheets());
			$assets['js'] = array_merge($assets['js'], $module->getFrontScripts());
		}

		self::runHook('head.assets', [&$assets]);

		return $assets;
	}

	private static function loadInstance(string $name, bool $cache = true): ?ModuleBase
	{
		if ($cache && isset(self::$instances[$name])) {
			return self::$instances[$name];
		}

		$file = self::path($name) . '/' . $name . '.php';

		if (!is_file($file)) {
			return null;
		}

		require_once dirname(__DIR__) . '/core/ModuleBase.php';
		require_once $file;

		$class = self::classNameFromName($name);

		if (!class_exists($class)) {
			return null;
		}

		$instance = new $class();

		if (!$instance instanceof ModuleBase) {
			return null;
		}

		if ($cache) {
			self::$instances[$name] = $instance;
		}

		return $instance;
	}

	private static function classNameFromName(string $name): string
	{
		$parts = explode('-', $name);
		$parts = array_map(static fn($p) => ucfirst($p), $parts);

		return implode('', $parts) . 'Module';
	}

	/** @return string[] */
	private static function getEnabledNames(): array
	{
		$rows = DB::execute('SELECT name FROM modules WHERE installed = 1 AND active = 1 ORDER BY name ASC') ?: [];

		return array_column($rows, 'name');
	}

	/** @return ModuleBase[] */
	private static function getEnabledInstances(): array
	{
		$modules = [];

		foreach (self::getEnabledNames() as $name) {
			$instance = self::loadInstance($name);

			if ($instance) {
				$modules[] = $instance;
			}
		}

		return $modules;
	}

	private static function getDbRow(string $name): ?array
	{
		$row = DB::getRowSafe('modules', 'name = ?', [$name]);

		return $row ?: null;
	}

	private static function assignDefaultDisplayHooks(ModuleBase $module): void
	{
		$defaults = $module->getDefaultDisplayHookNames();

		if ($defaults === []) {
			return;
		}

		self::setDisplayHooks($module->name, $defaults);
	}

	private static function ok(string $message): array
	{
		return ['success' => true, 'message' => $message];
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message];
	}

	private static function apiResponse(array $data, int $code = 200): void
	{
		http_response_code($code);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}
}
