<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';
	$flashType = 'success';

	if (Tools::isSubmit('langAction')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$action = trim((string) Tools::getValue('action'));

			switch ($action) {
				case 'add':
					$result = Lang::addLanguage(
						(string) Tools::getValue('code'),
						(string) Tools::getValue('label')
					);
					break;

				case 'remove':
					$result = Lang::removeLanguage((string) Tools::getValue('code'));
					break;

				case 'default':
					$result = Lang::setDefaultLanguage((string) Tools::getValue('code'));
					break;

				case 'rename':
					$result = Lang::updateLabel(
						(string) Tools::getValue('code'),
						(string) Tools::getValue('label')
					);
					break;

				case 'admin_default':
					$adminCode = strtolower(trim((string) Tools::getValue('code')));

					if (!AdminLang::isValid($adminCode)) {
						$result = ['success' => false, 'message' => 'Geçersiz admin dili'];
					} else {
						Settings::set('ADMIN_DEFAULT_LANG', $adminCode);
						$result = ['success' => true, 'message' => 'Admin panel varsayılan dili güncellendi'];
					}
					break;

				default:
					$result = ['success' => false, 'message' => 'Geçersiz işlem'];
					break;
			}

			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';
		}
	}

	$smarty->assign([
		'shopLanguages' => Lang::getAdminList(),
		'defaultLang' => Lang::getDefault(),
		'adminDefaultLang' => AdminLang::getDefault(),
		'adminLangOptions' => AdminLang::getAvailable(),
		'flash' => $flash,
		'flashType' => $flashType,
	]);

	AdminPage::add('languages', 'Diller');
