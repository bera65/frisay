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



	foreach ($themes as $name => &$themeRow) {

		$themeRow['preview_url'] = Theme::getPreviewUrl($name, $domain);

		$themeRow['is_active'] = ($name === $activeTheme);

	}

	unset($themeRow);



	if (Tools::isSubmit('saveTheme')) {

		$postToken = (string) Tools::getValue('token');



		if (!hash_equals($adminToken, $postToken)) {

			$flash = 'Geçersiz istek';

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



	if (Tools::isSubmit('uploadThemeZip')) {

		$postToken = (string) Tools::getValue('token');



		if (!hash_equals($adminToken, $postToken)) {

			$flash = 'Geçersiz istek';

			$flashType = 'danger';

		} else {

			$themeName = (string) Tools::getValue('theme_name');

			$result = Theme::installFromZip($_FILES['theme_zip'] ?? [], $themeName);



			if ($result['success']) {

				$themes = Theme::discover();



				foreach ($themes as $name => &$themeRow) {

					$themeRow['preview_url'] = Theme::getPreviewUrl($name, $domain);

					$themeRow['is_active'] = ($name === $activeTheme);

				}

				unset($themeRow);



				$flash = $result['message'];

				$flashType = 'success';

			} else {

				$flash = $result['message'];

				$flashType = 'danger';

			}

		}

	}



	if (Tools::isSubmit('copyTheme')) {

		$postToken = (string) Tools::getValue('token');



		if (!hash_equals($adminToken, $postToken)) {

			$flash = 'Geçersiz istek';

			$flashType = 'danger';

		} else {

			$sourceTheme = (string) Tools::getValue('source_theme');

			$newThemeName = (string) Tools::getValue('new_theme_name');

			$newThemeLabel = trim((string) Tools::getValue('new_theme_label'));

			$result = Theme::copyTheme($sourceTheme, $newThemeName, $newThemeLabel);



			if ($result['success']) {

				$themes = Theme::discover();



				foreach ($themes as $name => &$themeRow) {

					$themeRow['preview_url'] = Theme::getPreviewUrl($name, $domain);

					$themeRow['is_active'] = ($name === $activeTheme);

				}

				unset($themeRow);



				$flash = $result['message'];

				$flashType = 'success';

			} else {

				$flash = $result['message'];

				$flashType = 'danger';

			}

		}

	}



	$smarty->assign([

		'themes' => $themes,

		'activeTheme' => $activeTheme,

		'flash' => $flash,

		'flashType' => $flashType,

	]);



	AdminPage::add('templates', 'Temalar');

