<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	require_once dirname(__DIR__, 2) . '/core/Theme.php';

	$flash = '';
	$flashType = 'info';
	$themes = Theme::discover();
	$activeTheme = Settings::get('THEME') ?: 'default';

	if (!Theme::isValidName($activeTheme)) {
		$activeTheme = 'default';
	}

	$requestedTheme = (string) Tools::getValue('theme', '');

	if ($requestedTheme !== '' && Theme::isValidName($requestedTheme)) {
		$editModule = Theme::resolveEditModule($requestedTheme);

		if ($editModule !== null) {
			header('Location: ' . Admin::url('module-' . $editModule));
			exit;
		}
	}

	if (Tools::isSubmit('saveTheme')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$newTheme = (string) Tools::getValue('active_theme');
			$result = Theme::setActiveTheme($newTheme);

			if ($result['success']) {
				$activeTheme = $newTheme;
				$flash = $result['message'];
				$flashType = 'success';
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('createTheme')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$themeName = (string) Tools::getValue('theme_name');
			$themeLabel = (string) Tools::getValue('theme_label');
			$cloneFrom = (string) Tools::getValue('clone_from');

			$result = Theme::addTheme($themeName, $themeLabel, $cloneFrom);
			if ($result['success']) {
				$flash = $result['message'];
				$flashType = 'success';
				$themes = Theme::discover();
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('uploadTheme')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$themeName = (string) Tools::getValue('theme_name');
			$themeLabel = (string) Tools::getValue('theme_label');
			$zipFile = $_FILES['theme_zip'] ?? null;

			$result = Theme::addTheme($themeName, $themeLabel, null, $zipFile);
			if ($result['success']) {
				$flash = $result['message'];
				$flashType = 'success';
				$themes = Theme::discover();
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('deleteTheme')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$delTheme = (string) Tools::getValue('theme');
			$result = Theme::deleteTheme($delTheme);
			if ($result['success']) {
				$flash = $result['message'];
				$flashType = 'success';
				$themes = Theme::discover();
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	if (!isset($themes[$activeTheme])) {
		$activeTheme = array_key_first($themes) ?: 'default';
	}

	$smarty->assign([
		'themes' => $themes,
		'activeTheme' => $activeTheme,
		'flash' => $flash,
		'flashType' => $flashType,
	]);

	AdminPage::add('templates', 'Themes');
