<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$slug = defined('CMS_SLUG') ? CMS_SLUG : (string) Tools::getValue('slug');
	$cmsPage = Cms::getBySlug($slug);

	if (!$cmsPage) {
		http_response_code(404);
		$skipPageRender = true;
		$page->add('404', 'Sayfa Bulunamadı');
		return;
	}

	$pageTitle = trim((string) ($cmsPage['meta_title'] ?? ''));
	if ($pageTitle === '') {
		$pageTitle = $cmsPage['title'];
	}

	$pageDesc = trim((string) ($cmsPage['meta_description'] ?? ''));
	if ($pageDesc === '') {
		$pageDesc = $cmsPage['desc'];
	}

	$smarty->assign([
		'cmsPage' => $cmsPage,
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => $cmsPage['title'], 'url' => ''],
		],
	]);
