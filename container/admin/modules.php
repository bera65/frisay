<?php

if (!defined('IN_ADMIN')) {
	exit;
}

$flash = '';

if (Tools::isSubmit('moduleAction')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
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
					'message' => adminT('Invalid action')
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

AdminPage::add('modules', 'Modules');
