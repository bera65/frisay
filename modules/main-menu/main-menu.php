<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/MenuService.php';

class MainMenuModule extends ModuleBase
{
	public string $name = 'main-menu';
	public string $title = 'Ana Menü';
	public string $version = '1.2.0';
	public string $description = 'Üst menü; tam genişlik alt kategori mega menü, mobil tıklayınca açılır';
	public string $author = 'FShop';

	public array $displayHooks = [
		'main_menu' => 'Ana navigasyon menüsü',
	];

	public array $defaultDisplayHooks = ['main_menu'];

	public array $frontStylesheets = ['main-menu.css'];
	public array $frontScripts = ['main-menu.js'];
	public array $adminStylesheets = ['admin.css'];

	public function install(): bool
	{
		MainMenuService::ensureSchema();

		return true;
	}

	public function uninstall(): bool
	{
		DB::execute('DROP TABLE IF EXISTS `main_menu_items`');

		return true;
	}

	public function boot(): void
	{
		MainMenuService::ensureSchema();

		Module::registerHook('smarty.assign', static function (): void {
			global $smarty;

			if (!$smarty) {
				return;
			}

			$items = MainMenuService::getActiveItems();
			$smarty->assign([
				'mainMenuItems' => $items,
				'mainMenuActive' => $items !== [],
			]);
		});
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'main_menu') {
			return null;
		}

		$items = MainMenuService::getActiveItems();

		if ($items === []) {
			return null;
		}

		$html = $this->renderFrontTemplate('main_menu', [
			'items' => $items,
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		MainMenuService::ensureSchema();
		$flash = '';
		$flashType = 'success';
		$edit = null;

		if (Tools::isSubmit('saveMenuItem')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$id = (int) Tools::getValue('id_menu_item');
				$result = MainMenuService::saveItem([
					'label' => (string) Tools::getValue('label'),
					'link_type' => (string) Tools::getValue('link_type'),
					'link_value' => (string) Tools::getValue('link_value'),
					'target' => (string) Tools::getValue('target'),
					'position' => (int) Tools::getValue('position'),
					'active' => (int) Tools::getValue('active') === 1,
				], $id);
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		if (Tools::isSubmit('deleteMenuItem')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = MainMenuService::deleteItem((int) Tools::getValue('id_menu_item'));
				$flash = $result['message'];
				$flashType = !empty($result['success']) ? 'success' : 'danger';
			}
		}

		$editId = (int) Tools::getValue('edit');

		if ($editId > 0) {
			foreach (MainMenuService::getAllAdmin() as $row) {
				if ((int) $row['id_menu_item'] === $editId) {
					$edit = $row;
					break;
				}
			}
		}

		$cmsOptions = DB::execute('SELECT id_cms, slug FROM cms_pages WHERE active = 1 ORDER BY id_cms ASC') ?: [];

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'menuItems' => MainMenuService::getAllAdmin(),
			'editItem' => $edit,
			'categoryOptions' => Category::getMenuList(),
			'cmsOptions' => $cmsOptions,
		]);
	}
}
