<?php

class AdminPage
{
	public static function add(string $pageName, string $pageTitle = '', bool $noLayout = false): void
	{
		global $smarty;

		self::assignPageMeta($pageName, $pageTitle);

		if ($noLayout) {
			$smarty->display(_ADMIN_THEME_DIR_ . $pageName . '.tpl');
			return;
		}

		$smarty->display(_ADMIN_THEME_DIR_ . 'layout/header.tpl');
		$smarty->display(_ADMIN_THEME_DIR_ . $pageName . '.tpl');
		$smarty->display(_ADMIN_THEME_DIR_ . 'layout/footer.tpl');
	}

	public static function addModule(ModuleBase $module): void
	{
		global $smarty;

		$pageName = $module->getAdminSlug();
		$pageTitle = $module->getAdminPageTitle();

		self::assignPageMeta($pageName, $pageTitle);

		$smarty->assign([
			'moduleName' => $module->name,
			'moduleDetailUrl' => Admin::url('module?name=' . rawurlencode($module->name)),
			'moduleConfigUrl' => Admin::url($module->getAdminSlug()),
			'moduleAdminAssets' => $module->getAdminAssets(),
		]);

		$smarty->display(_ADMIN_THEME_DIR_ . 'layout/header.tpl');

		if ($module->hasAdminTemplate()) {
			$smarty->display('file:' . $module->getAdminTemplatePath('admin'));
		} else {
			$smarty->display(_ADMIN_THEME_DIR_ . 'module-config-empty.tpl');
		}

		$smarty->display(_ADMIN_THEME_DIR_ . 'layout/footer.tpl');
	}

	private static function assignPageMeta(string $pageName, string $pageTitle): void
	{
		global $smarty;

		$smarty->assign([
			'pageName' => $pageName,
			'pageTitle' => $pageTitle !== '' && function_exists('adminT') ? adminT($pageTitle) : $pageTitle,
			'moduleNavActive' => $pageName === 'modules'
				|| $pageName === 'module'
				|| strpos($pageName, 'module-') === 0,
		]);
	}
}
