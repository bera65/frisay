<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$flash = '';

	if (Tools::isSubmit('deleteCms')) {
		$postToken = (string) Tools::getValue('token');
		$idCms = (int) Tools::getValue('id');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
		} elseif ($idCms > 0) {
			$result = Cms::delete($idCms);
			$flash = $result['message'];
		}
	}

	$smarty->assign([
		'cmsPages' => Cms::getAdminList(),
		'flash' => $flash,
	]);

	AdminPage::add('cms', 'CMS Pages');
