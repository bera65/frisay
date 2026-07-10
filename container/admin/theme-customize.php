<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	require_once dirname(__DIR__, 2) . '/core/Theme.php';
	require_once dirname(__DIR__, 2) . '/core/SiteAssets.php';

	$flash = '';
	$flashType = 'info';
	$activeTheme = Settings::get('THEME') ?: 'default';

	if (!Theme::isValidName($activeTheme)) {
		$activeTheme = 'default';
	}

	$editTheme = (string) Tools::getValue('theme', $activeTheme);

	if (!Theme::isValidName($editTheme)) {
		header('Location: ' . Admin::url('templates'));
		exit;
	}

	$themeMeta = Theme::getMeta($editTheme);
	Theme::ensureColorsFile($editTheme);
	Theme::ensureCustomCss($editTheme);

	$themeColors = Theme::getColors($editTheme);
	$colorDefs = Theme::getColorDefinitions($editTheme);
	$colorGroups = Theme::getColorGroups($editTheme);
	$themeOptionDefs = Theme::getOptionDefinitions($editTheme);
	$themeOptions = Theme::getOptions($editTheme);
	$headerVariants = Theme::discoverHeaderVariants($editTheme);
	$previewUrl = Theme::getPreviewUrl($editTheme, $domain);

	if (Tools::isSubmit('saveThemeCustomize')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
			$flashType = 'danger';
		} else {
			$saveTheme = (string) Tools::getValue('edit_theme');

			if (!Theme::isValidName($saveTheme) || $saveTheme !== $editTheme) {
				$flash = 'Geçersiz tema';
				$flashType = 'danger';
			} else {
				$messages = [];
				$hasError = false;

				if ($themeOptionDefs !== []) {
					$inputOptions = [];

					foreach (array_keys($themeOptionDefs) as $optKey) {
						$inputOptions[$optKey] = (string) Tools::getValue('opt_' . $optKey);
					}

					$result = Theme::saveOptions($saveTheme, $inputOptions);

					if ($result['success']) {
						$messages[] = $result['message'];
						$themeOptions = Theme::getOptions($editTheme);
					} else {
						$flash = $result['message'];
						$flashType = 'danger';
						$hasError = true;
					}
				}

				if (!$hasError && $colorDefs !== []) {
					$inputColors = [];

					foreach (array_keys($colorDefs) as $key) {
						$inputColors[$key] = (string) Tools::getValue('color_' . $key);
					}

					$result = Theme::saveColors($saveTheme, $inputColors);

					if ($result['success']) {
						$messages[] = $result['message'];
						$themeColors = Theme::getColors($editTheme);
					} else {
						$flash = $result['message'];
						$flashType = 'danger';
						$hasError = true;
					}
				}

				if (!$hasError) {
					$flash = $messages !== [] ? implode(' ', $messages) : 'Değişiklikler kaydedildi';
					$flashType = 'success';
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

	$colorPickerValues = [];

	foreach ($themeColors as $key => $value) {
		$colorPickerValues[$key] = preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : '#000000';
	}

	$smarty->assign([
		'activeTheme' => $activeTheme,
		'editTheme' => $editTheme,
		'themeMeta' => $themeMeta,
		'themeColors' => $themeColors,
		'colorDefs' => $colorDefs,
		'colorGroups' => $colorGroups,
		'colorPickerValues' => $colorPickerValues,
		'themeOptionDefs' => $themeOptionDefs,
		'themeOptions' => $themeOptions,
		'headerVariants' => $headerVariants,
		'previewUrl' => $previewUrl,
		'siteLogos' => SiteAssets::getLogos(),
		'flash' => $flash,
		'flashType' => $flashType,
	]);

	AdminPage::add('theme-customize', 'Tema: ' . $themeMeta['label']);
