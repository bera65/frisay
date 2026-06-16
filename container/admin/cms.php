<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$smarty->assign([
		'cmsPages' => Cms::getAdminList(),
	]);

	AdminPage::add('cms', 'CMS Sayfaları');
