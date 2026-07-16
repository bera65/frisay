<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

if (!class_exists('Admin')) {
	require_once dirname(__DIR__, 3) . '/core/Admin.php';
}

require_once dirname(__DIR__) . '/lib/AiClient.php';

header('Content-Type: application/json; charset=utf-8');

if (!Admin::isLoggedIn()) {
	echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim'], JSON_UNESCAPED_UNICODE);
	exit;
}

$token = (string) Tools::getValue('token');
$sessionToken = (string) ($_SESSION['admin_csrf_token'] ?? '');

if ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek'], JSON_UNESCAPED_UNICODE);
	exit;
}

if (!AiAssistantClient::isConfigured()) {
	echo json_encode([
		'success' => false,
		'message' => 'API anahtarı eksik. Modül ayarlarından ekleyin.',
	], JSON_UNESCAPED_UNICODE);
	exit;
}

$fields = [
	'product_name' => (string) Tools::getValue('product_name', ''),
	'short_description' => (string) Tools::getValue('short_description', ''),
	'description' => (string) Tools::getValue('description', ''),
	'meta_title' => (string) Tools::getValue('meta_title', ''),
	'meta_description' => (string) Tools::getValue('meta_description', ''),
];

$hasContent = false;

foreach ($fields as $value) {
	if (trim(strip_tags($value)) !== '') {
		$hasContent = true;
		break;
	}
}

if (!$hasContent) {
	echo json_encode([
		'success' => false,
		'message' => 'İyileştirilecek en az bir alan doldurun (ürün adı veya açıklama).',
	], JSON_UNESCAPED_UNICODE);
	exit;
}

$tone = (string) Tools::getValue('tone', Settings::get('AI_ASSISTANT_TONE') ?: 'professional');
$lang = (string) Tools::getValue('lang', Settings::get('AI_ASSISTANT_LANG') ?: 'tr');

$result = AiAssistantClient::improveProduct($fields, $tone, $lang);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
