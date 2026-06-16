<?php

if (!defined('IN_ADMIN')) {
	exit;
}

$flash = '';

if (Tools::isSubmit('moduleAction')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = 'Geçersiz istek';
	} else {
		$name = trim((string) Tools::getValue('name'));
		$action = trim((string) Tools::getValue('action'));

		switch ($action) {
			case 'install':
				$result = Module::install($name);
				break;

			case 'uninstall':
				$result = Module::uninstall($name);
				break;

			case 'enable':
				$result = Module::setActive($name, true);
				break;

			case 'disable':
				$result = Module::setActive($name, false);
				break;

			default:
				$result = [
					'success' => false,
					'message' => 'Geçersiz işlem'
				];
				break;
		}

		if ($result['success'] && $action === 'install') {
			header('Location: ' . Admin::url('modules'));
			exit;
		}

		$flash = $result['message'];
	}
}

$smarty->assign([
	'modules' => Module::getAdminList(),
	'flash' => $flash,
]);

AdminPage::add('modules', 'Modüller');
