<?php

define('IN_SCRIPT', true);

require_once dirname(__DIR__) . '/config/install_gate.php';

if (!fshop_is_installed()) {
	http_response_code(503);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(['success' => false, 'message' => 'Kurulum tamamlanmamış']);
	exit;
}

require_once dirname(__DIR__) . '/config/admin_bootstrap.php';
require_once dirname(__DIR__) . '/core/MediaLibrary.php';

while (ob_get_level() > 0) {
	ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');

if (!Admin::isLoggedIn()) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Oturum gerekli']);
	exit;
}

$action = (string) Tools::getValue('action', 'list');
$token = (string) Tools::getValue('token');

if ($token === '' || !hash_equals((string) $adminToken, $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
	exit;
}

MediaLibrary::ensureMediaDir();

if ($action === 'list') {
	echo json_encode(MediaLibrary::list((string) Tools::getValue('path', '')));
	exit;
}

if ($action === 'mkdir') {
	echo json_encode(MediaLibrary::mkdir(
		(string) Tools::getValue('path', ''),
		(string) Tools::getValue('name', '')
	));
	exit;
}

if ($action === 'upload') {
	$path = (string) Tools::getValue('path', 'media');
	$files = [];

	if (!empty($_FILES['files']) && is_array($_FILES['files']['name'] ?? null)) {
		$count = count($_FILES['files']['name']);

		for ($i = 0; $i < $count; $i++) {
			$files[] = [
				'name' => $_FILES['files']['name'][$i] ?? '',
				'type' => $_FILES['files']['type'][$i] ?? '',
				'tmp_name' => $_FILES['files']['tmp_name'][$i] ?? '',
				'error' => $_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
				'size' => $_FILES['files']['size'][$i] ?? 0,
			];
		}
	} elseif (!empty($_FILES['file'])) {
		$files[] = $_FILES['file'];
	}

	$uploaded = [];
	$errors = [];

	foreach ($files as $file) {
		if (empty($file['tmp_name'])) {
			continue;
		}

		$result = MediaLibrary::upload($file, $path);

		if (!empty($result['success'])) {
			$uploaded[] = $result['item'] ?? null;
		} else {
			$errors[] = (string) ($result['message'] ?? 'Yükleme hatası');
		}
	}

	$list = MediaLibrary::list($path);

	echo json_encode([
		'success' => $uploaded !== [],
		'message' => $uploaded !== []
			? count($uploaded) . ' dosya yüklendi'
			: ($errors[0] ?? 'Yükleme başarısız'),
		'uploaded' => array_values(array_filter($uploaded)),
		'errors' => $errors,
		'path' => $list['path'] ?? $path,
		'items' => $list['items'] ?? [],
		'can_upload' => !empty($list['can_upload']),
		'can_mkdir' => !empty($list['can_mkdir']),
		'breadcrumbs' => $list['breadcrumbs'] ?? [],
	]);
	exit;
}

if ($action === 'attach') {
	$idProduct = (int) Tools::getValue('id_product');
	$paths = Tools::getValue('paths');

	if (!is_array($paths)) {
		$raw = (string) Tools::getValue('paths_json', '[]');
		$decoded = json_decode($raw, true);
		$paths = is_array($decoded) ? $decoded : [];
	}

	echo json_encode(MediaLibrary::attachToProduct($idProduct, $paths));
	exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
