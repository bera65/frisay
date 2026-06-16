<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$slug = trim((string) Tools::getValue('slug'));
	$page = Cms::getBySlug($slug);
	$flash = '';

	if (!$page) {
		http_response_code(404);
		AdminPage::add('404', 'Sayfa Bulunamadı');
		return;
	}

	$content = Cms::getContent($slug) ?? '';
	$cmsSeo = Cms::getSeo($slug);

	if (Tools::isSubmit('saveCms')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($adminToken, $postToken)) {
			$flash = 'Geçersiz istek';
		} else {
			$result = Cms::saveContent($slug, (string) Tools::getValue('content'));
			$seoResult = Cms::saveSeo(
				$slug,
				(string) Tools::getValue('meta_title'),
				(string) Tools::getValue('meta_description')
			);

			if ($result['success'] && $seoResult['success']) {
				$flash = 'Sayfa kaydedildi';
				$content = Cms::getContent($slug) ?? '';
				$cmsSeo = Cms::getSeo($slug);
			} else {
				$flash = !$result['success'] ? $result['message'] : $seoResult['message'];
			}
		}
	}

	$smarty->assign([
		'cmsPage' => $page,
		'cmsContent' => $content,
		'cmsSeo' => $cmsSeo,
		'flash' => $flash,
		'adminUseEditor' => true,
	]);

	AdminPage::add('cms-edit', $page['title']);
