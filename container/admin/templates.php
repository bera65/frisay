<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	require_once dirname(__DIR__, 2) . '/core/Theme.php';

	require_once dirname(__DIR__, 2) . '/core/SiteAssets.php';

	$flash = '';
	$flashType = 'info';
	$themes = Theme::discover();
	$activeTheme = Settings::get('THEME') ?: 'default';

	if (!Theme::isValidName($activeTheme)) {
		$activeTheme = 'default';
	}

	$editTheme = (string) Tools::getValue('theme', $activeTheme);

	if (!Theme::isValidName($editTheme)) {
		$editTheme = $activeTheme;
	}

	Theme::ensureColorsFile($editTheme);
	Theme::ensureCustomCss($editTheme);
	$themeColors = Theme::getColors($editTheme);
	$colorDefs = Theme::getColorDefinitions();
	$themeOptionDefs = Theme::getOptionDefinitions($editTheme);
	$themeOptions = Theme::getOptions($editTheme);
	$headerVariants = Theme::discoverHeaderVariants($editTheme);

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
				$editTheme = $newTheme;
				$flash = $result['message'];
				$flashType = 'success';
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	if (Tools::isSubmit('saveThemeColors')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$saveTheme = (string) Tools::getValue('edit_theme');

			if (!Theme::isValidName($saveTheme)) {
				$flash = 'Geçersiz tema';
				$flashType = 'danger';
			} else {
				$inputColors = [];

				foreach (array_keys($colorDefs) as $key) {
					$inputColors[$key] = (string) Tools::getValue('color_' . $key);
				}

				$result = Theme::saveColors($saveTheme, $inputColors);

				if ($result['success']) {
					$editTheme = $saveTheme;
					$themeColors = Theme::getColors($editTheme);
					$flash = $result['message'];
					$flashType = 'success';
				} else {
					$flash = $result['message'];
					$flashType = 'danger';
				}
			}
		}
	}

	if (Tools::isSubmit('saveThemeOptions')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$saveTheme = (string) Tools::getValue('edit_theme');

			if (!Theme::isValidName($saveTheme)) {
				$flash = 'Geçersiz tema';
				$flashType = 'danger';
			} else {
				$inputOptions = [];

				foreach (array_keys(Theme::getOptionDefinitions($saveTheme)) as $optKey) {
					$inputOptions[$optKey] = (string) Tools::getValue('opt_' . $optKey);
				}

				$result = Theme::saveOptions($saveTheme, $inputOptions);

				if ($result['success']) {
					$editTheme = $saveTheme;
					$themeOptions = Theme::getOptions($editTheme);
					$themeOptionDefs = Theme::getOptionDefinitions($editTheme);
					$flash = $result['message'];
					$flashType = 'success';
				} else {
					$flash = $result['message'];
					$flashType = 'danger';
				}
			}
		}
	}

	if (Tools::isSubmit('uploadLogo')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$logoKey = (string) Tools::getValue('logo_key');
			$result = SiteAssets::uploadLogo($logoKey, $_FILES['logo_file'] ?? []);

			if ($result['success']) {
				$flash = $result['message'];
				$flashType = 'success';
			} else {
				$flash = $result['message'];
				$flashType = 'danger';
			}
		}
	}

	$colorGroups = [
		'marka' => 'Marka',
		'yuzey' => 'Yüzey',
		'metin' => 'Metin',
		'ek' => 'Buton & Header',
		'footer' => 'Footer',
	];

	$colorPickerValues = [];

	foreach ($themeColors as $key => $value) {
		$colorPickerValues[$key] = preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : '#000000';
	}

	$smarty->assign([
		'themes' => $themes,
		'activeTheme' => $activeTheme,
		'editTheme' => $editTheme,
		'themeColors' => $themeColors,
		'colorDefs' => $colorDefs,
		'colorGroups' => $colorGroups,
		'colorPickerValues' => $colorPickerValues,
		'themeOptionDefs' => $themeOptionDefs,
		'themeOptions' => $themeOptions,
		'headerVariants' => $headerVariants,
		'siteLogos' => SiteAssets::getLogos(),
		'flash' => $flash,
		'flashType' => $flashType,
	]);

	AdminPage::add('templates', 'Temalar');
