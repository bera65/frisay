<?php

if (!defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ApiKey.php';

ApiKey::ensureSchema();

$flash = '';
$flashType = 'success';
$newApiKey = '';

if (Tools::isSubmit('saveApiEnabled')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		Settings::set('WEBAPI_ENABLED', Tools::getValue('WEBAPI_ENABLED') ? '1' : '0');
		$flash = 'API durumu kaydedildi';
	}
}

if (Tools::isSubmit('createApiKey')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$perms = Tools::getValue('permissions');
		$result = ApiKey::create(
			(string) Tools::getValue('name'),
			is_array($perms) ? $perms : [],
			(bool) Tools::getValue('active', 1)
		);
		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';

		if ($result['ok'] && !empty($result['api_key'])) {
			$newApiKey = (string) $result['api_key'];
		}
	}
}

if (Tools::isSubmit('updateApiKey')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$perms = Tools::getValue('permissions');
		$result = ApiKey::update(
			(int) Tools::getValue('id_api_key'),
			(string) Tools::getValue('name'),
			is_array($perms) ? $perms : [],
			(bool) Tools::getValue('active')
		);
		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';
	}
}

if (Tools::isSubmit('regenApiKey')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$result = ApiKey::regenerate((int) Tools::getValue('id_api_key'));
		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';

		if ($result['ok'] && !empty($result['api_key'])) {
			$newApiKey = (string) $result['api_key'];
		}
	}
}

if (Tools::isSubmit('deleteApiKey')) {
	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		$flash = adminT('Invalid request');
		$flashType = 'danger';
	} else {
		$result = ApiKey::delete((int) Tools::getValue('id_api_key'));
		$flash = $result['message'];
		$flashType = $result['ok'] ? 'success' : 'danger';
	}
}

$editId = (int) Tools::getValue('edit', 0);
$editKey = $editId > 0 ? ApiKey::getById($editId) : null;

$apiDocsBase = 'https://frisay.com/developer/';
$apiDocs = [
	[
		'group' => 'Başlangıç',
		'items' => [
			['title' => 'Giriş / Genel bilgi', 'url' => $apiDocsBase, 'desc' => 'API entegrasyonuna genel bakış'],
			['title' => 'Kimlik doğrulama', 'url' => $apiDocsBase . '?page=authentication', 'desc' => 'X-API-Key / Bearer kullanımı'],
		],
	],
	[
		'group' => 'Products',
		'items' => [
			['title' => 'Ürün listesi', 'url' => $apiDocsBase . '?page=list-products', 'desc' => 'GET /products'],
			['title' => 'Ürün ekleme', 'url' => $apiDocsBase . '?page=add-product', 'desc' => 'POST /products'],
			['title' => 'Ürün güncelleme', 'url' => $apiDocsBase . '?page=update-product', 'desc' => 'PATCH /products/{id}'],
			['title' => 'Ürün silme', 'url' => $apiDocsBase . '?page=delete-product', 'desc' => 'DELETE /products/{id}'],
		],
	],
	[
		'group' => 'Orders',
		'items' => [
			['title' => 'Sipariş listesi', 'url' => $apiDocsBase . '?page=list-orders', 'desc' => 'GET /orders'],
			['title' => 'Sipariş durumu güncelle', 'url' => $apiDocsBase . '?page=update-order-status', 'desc' => 'PATCH /orders/{id}'],
		],
	],
];

$smarty->assign([
	'flash' => $flash,
	'flashType' => $flashType,
	'newApiKey' => $newApiKey,
	'apiEnabled' => Settings::get('WEBAPI_ENABLED') === '1',
	'apiKeys' => ApiKey::getList(),
	'permissionCatalog' => ApiKey::permissionCatalog(),
	'editKey' => $editKey,
	'webApiUrl' => rtrim($domain, '/') . '/api/v1/',
	'apiDocs' => $apiDocs,
	'apiDocsPortalUrl' => $apiDocsBase,
]);

AdminPage::add('api', 'API Keys');
