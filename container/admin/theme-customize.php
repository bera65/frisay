<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	require_once dirname(__DIR__, 2) . '/core/Theme.php';

	$theme = (string) Tools::getValue('theme', '');
	$target = Admin::url('templates');

	if ($theme !== '' && Theme::isValidName($theme)) {
		$editModule = Theme::resolveEditModule($theme);

		if ($editModule !== null) {
			$target = Admin::url('module-' . $editModule);
		}
	}

	header('Location: ' . $target);
	exit;
