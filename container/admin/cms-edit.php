<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$idCms = (int) Tools::getValue('id');
	$isNew = $idCms <= 0;
	$page = $isNew ? null : Cms::getById($idCms, Lang::getDefault());
	$flash = '';
	$flashType = 'success';

	if (!$isNew && !$page) {
		http_response_code(404);
		AdminPage::add('404', 'Page not found');
		return;
	}

	if (Tools::isSubmit('saveCms')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = adminT('Invalid request');
			$flashType = 'danger';
		} else {
			$result = Cms::save($idCms, [
				'slug' => (string) Tools::getValue('slug'),
				'active' => (int) Tools::getValue('active', 1),
				'show_footer' => (int) Tools::getValue('show_footer', 1),
				'position' => (int) Tools::getValue('position', 0),
				'langs' => (array) Tools::getValue('langs', []),
			]);

			$flash = $result['message'];
			$flashType = !empty($result['success']) ? 'success' : 'danger';

			if (!empty($result['success'])) {
				header('Location: ' . Admin::url('cms-edit?id=' . (int) $result['id_cms'] . '&saved=1'));
				exit;
			}
		}
	}

	if (Tools::getValue('saved')) {
		$flash = 'Sayfa kaydedildi';
		$page = Cms::getById($idCms, Lang::getDefault());
	}

	$langRows = $isNew ? [] : Cms::getLangRows($idCms);
	$langForms = [];

	foreach (Lang::getAvailable() as $langCode) {
		$row = $langRows[$langCode] ?? [];
		$slug = (string) ($row['slug'] ?? '');
		if ($slug === '' && $page) {
			$slug = (string) ($page['slug'] ?? '');
		}
		$langForms[$langCode] = [
			'code' => $langCode,
			'label' => Lang::label($langCode),
			'slug' => $slug,
			'title' => (string) ($row['title'] ?? ''),
			'summary' => (string) ($row['summary'] ?? ''),
			'content' => (string) ($row['content'] ?? ''),
			'meta_title' => (string) ($row['meta_title'] ?? ''),
			'meta_description' => (string) ($row['meta_description'] ?? ''),
		];
	}

	$form = $page ?: [
		'id_cms' => 0,
		'slug' => '',
		'active' => 1,
		'show_footer' => 1,
		'position' => 0,
		'url' => '',
	];

	$smarty->assign([
		'cmsPage' => $form,
		'cmsLangForms' => $langForms,
		'shopLanguages' => Lang::getAvailable(),
		'isNewCms' => $isNew,
		'flash' => $flash,
		'flashType' => $flashType,
		'adminUseEditor' => true,
	]);

	AdminPage::add('cms-edit', $isNew ? 'New CMS page' : 'CMS: ' . ($form['title'] ?? $form['slug']));
