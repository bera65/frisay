<?php

class MediaLibrary
{
	private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
	private const HIDDEN_DIRS = ['contact', 'returns'];
	private const UPLOAD_ROOT = 'media';

	public static function baseDir(): string
	{
		return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img';
	}

	public static function list(string $path = ''): array
	{
		$relative = self::normalizePath($path);
		$dir = self::resolveDir($relative);

		if ($dir === null || !is_dir($dir)) {
			return [
				'success' => false,
				'message' => 'Klasör bulunamadı',
				'path' => $relative,
				'items' => [],
				'breadcrumbs' => self::breadcrumbs($relative),
				'can_upload' => false,
				'can_mkdir' => false,
			];
		}

		$items = [];
		$entries = @scandir($dir) ?: [];

		foreach ($entries as $name) {
			if ($name === '.' || $name === '..' || $name === 'index.php' || $name === '.htaccess') {
				continue;
			}

			if ($name[0] === '.') {
				continue;
			}

			$full = $dir . DIRECTORY_SEPARATOR . $name;
			$itemPath = $relative === '' ? $name : $relative . '/' . $name;

			if (is_dir($full)) {
				if ($relative === '' && in_array(strtolower($name), self::HIDDEN_DIRS, true)) {
					continue;
				}

				$items[] = [
					'type' => 'dir',
					'name' => $name,
					'path' => $itemPath,
					'url' => '',
					'mtime' => (int) @filemtime($full),
				];
				continue;
			}

			if (!is_file($full) || !self::isAllowedImage($name)) {
				continue;
			}

			$items[] = [
				'type' => 'file',
				'name' => $name,
				'path' => $itemPath,
				'url' => self::publicUrl($itemPath),
				'size' => (int) @filesize($full),
				'mtime' => (int) @filemtime($full),
			];
		}

		usort($items, static function (array $a, array $b): int {
			if ($a['type'] !== $b['type']) {
				return $a['type'] === 'dir' ? -1 : 1;
			}

			return strcasecmp($a['name'], $b['name']);
		});

		return [
			'success' => true,
			'message' => '',
			'path' => $relative,
			'items' => $items,
			'breadcrumbs' => self::breadcrumbs($relative),
			'can_upload' => self::canWrite($relative),
			'can_mkdir' => self::canWrite($relative),
		];
	}

	public static function upload(array $file, string $path = ''): array
	{
		$relative = self::normalizePath($path);

		if (!self::canWrite($relative)) {
			return self::fail('Bu klasöre yükleme yapılamaz. Medya klasörünü kullanın.');
		}

		$dir = self::resolveDir($relative);

		if ($dir === null) {
			return self::fail('Klasör bulunamadı');
		}

		if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			return self::fail('Geçerli bir görsel seçin');
		}

		$original = (string) ($file['name'] ?? 'image.jpg');
		$ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

		if (!in_array($ext, self::ALLOWED_EXT, true)) {
			return self::fail('Sadece JPG, PNG, WEBP veya GIF yükleyebilirsiniz');
		}

		$binary = file_get_contents($file['tmp_name']);

		if (!is_string($binary) || $binary === '' || !@getimagesizefromstring($binary)) {
			return self::fail('Dosya bir görsel değil');
		}

		$base = self::sanitizeFilename(pathinfo($original, PATHINFO_FILENAME));
		if ($base === '') {
			$base = 'image';
		}

		$filename = $base . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
		$dest = $dir . DIRECTORY_SEPARATOR . $filename;
		$i = 1;

		while (is_file($dest)) {
			$filename = $base . '-' . $i . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
			$dest = $dir . DIRECTORY_SEPARATOR . $filename;
			$i++;
		}

		if (!move_uploaded_file($file['tmp_name'], $dest)) {
			if (@file_put_contents($dest, $binary) === false) {
				return self::fail('Dosya kaydedilemedi');
			}
		}

		$itemPath = $relative === '' ? $filename : $relative . '/' . $filename;

		return [
			'success' => true,
			'message' => 'Dosya yüklendi',
			'item' => [
				'type' => 'file',
				'name' => $filename,
				'path' => $itemPath,
				'url' => self::publicUrl($itemPath),
				'size' => (int) @filesize($dest),
				'mtime' => time(),
			],
		];
	}

	public static function mkdir(string $path, string $name): array
	{
		$relative = self::normalizePath($path);

		if (!self::canWrite($relative)) {
			return self::fail('Bu konumda klasör oluşturulamaz');
		}

		$parent = self::resolveDir($relative);

		if ($parent === null) {
			return self::fail('Üst klasör bulunamadı');
		}

		$folder = self::sanitizeFilename($name);

		if ($folder === '') {
			return self::fail('Geçerli bir klasör adı girin');
		}

		$dest = $parent . DIRECTORY_SEPARATOR . $folder;

		if (is_dir($dest)) {
			return self::fail('Bu klasör zaten var');
		}

		if (!mkdir($dest, 0755, true) && !is_dir($dest)) {
			return self::fail('Klasör oluşturulamadı');
		}

		@file_put_contents($dest . DIRECTORY_SEPARATOR . 'index.php', "<?php\nheader('Location: ../');\nexit;\n");

		$itemPath = $relative === '' ? $folder : $relative . '/' . $folder;

		return [
			'success' => true,
			'message' => 'Klasör oluşturuldu',
			'path' => $itemPath,
		];
	}

	public static function attachToProduct(int $idProduct, array $paths): array
	{
		if ($idProduct <= 0 || !Product::getByIdAdmin($idProduct)) {
			return self::fail('Ürün bulunamadı');
		}

		$attached = [];
		$errors = [];

		foreach ($paths as $path) {
			$path = self::normalizePath((string) $path);
			$file = self::resolveFile($path);

			if ($file === null) {
				$errors[] = $path . ': dosya bulunamadı';
				continue;
			}

			$binary = file_get_contents($file);

			if (!is_string($binary) || $binary === '') {
				$errors[] = $path . ': okunamadı';
				continue;
			}

			$result = Product::importImageBinary($idProduct, $binary);

			if (!empty($result['success'])) {
				$attached[] = [
					'id_image' => (int) ($result['id_image'] ?? $result['id'] ?? 0),
					'url' => (string) ($result['url'] ?? ''),
					'cover' => (int) ($result['cover'] ?? 0),
					'source' => $path,
				];
			} else {
				$errors[] = $path . ': ' . (string) ($result['message'] ?? 'eklenemedi');
			}
		}

		return [
			'success' => $attached !== [],
			'message' => $attached !== []
				? count($attached) . ' görsel ürüne eklendi'
				: ($errors[0] ?? 'Görsel eklenemedi'),
			'images' => Product::getImages($idProduct),
			'attached' => $attached,
			'errors' => $errors,
		];
	}

	public static function ensureMediaDir(): void
	{
		$dir = self::baseDir() . DIRECTORY_SEPARATOR . self::UPLOAD_ROOT;

		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
			@file_put_contents($dir . DIRECTORY_SEPARATOR . 'index.php', "<?php\nheader('Location: ../');\nexit;\n");
		}
	}

	private static function canWrite(string $relative): bool
	{
		if ($relative === self::UPLOAD_ROOT) {
			return true;
		}

		return strpos($relative, self::UPLOAD_ROOT . '/') === 0;
	}

	private static function isAllowedImage(string $name): bool
	{
		$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

		return in_array($ext, self::ALLOWED_EXT, true);
	}

	private static function sanitizeFilename(string $name): string
	{
		$name = preg_replace('/[^a-zA-Z0-9_\-\.\p{L}\p{N}]+/u', '-', $name) ?? '';
		$name = trim($name, '.-_');

		return mb_substr($name, 0, 120);
	}

	private static function normalizePath(string $path): string
	{
		$path = str_replace('\\', '/', trim($path));
		$path = trim($path, '/');

		if ($path === '' || $path === '.') {
			return '';
		}

		$parts = [];

		foreach (explode('/', $path) as $part) {
			$part = trim($part);
			if ($part === '' || $part === '.' || $part === '..') {
				continue;
			}
			if (!preg_match('/^[\w\-\.\p{L}\p{N}]+$/u', $part)) {
				continue;
			}
			$parts[] = $part;
		}

		return implode('/', $parts);
	}

	private static function resolveDir(string $relative): ?string
	{
		$base = realpath(self::baseDir());

		if ($base === false) {
			return null;
		}

		$target = $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

		if ($relative !== '' && !is_dir($target)) {
			if ($relative === self::UPLOAD_ROOT) {
				self::ensureMediaDir();
			} else {
				return null;
			}
			$target = $base . DIRECTORY_SEPARATOR . self::UPLOAD_ROOT;
		}

		$real = realpath($target);

		if ($real === false || strpos($real, $base) !== 0) {
			return null;
		}

		$relCheck = ltrim(str_replace('\\', '/', substr($real, strlen($base))), '/');
		$first = $relCheck === '' ? '' : explode('/', $relCheck)[0];

		if ($first !== '' && in_array(strtolower($first), self::HIDDEN_DIRS, true)) {
			return null;
		}

		return $real;
	}

	private static function resolveFile(string $relative): ?string
	{
		if ($relative === '' || !self::isAllowedImage($relative)) {
			return null;
		}

		$base = realpath(self::baseDir());

		if ($base === false) {
			return null;
		}

		$target = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
		$real = realpath($target);

		if ($real === false || !is_file($real) || strpos($real, $base) !== 0) {
			return null;
		}

		$relCheck = ltrim(str_replace('\\', '/', substr($real, strlen($base))), '/');
		$first = explode('/', $relCheck)[0] ?? '';

		if ($first !== '' && in_array(strtolower($first), self::HIDDEN_DIRS, true)) {
			return null;
		}

		return $real;
	}

	private static function publicUrl(string $relative): string
	{
		global $domain;

		$base = rtrim((string) ($domain ?? ''), '/');

		return $base . '/img/' . ltrim(str_replace('\\', '/', $relative), '/');
	}

	private static function breadcrumbs(string $relative): array
	{
		$crumbs = [['label' => '/', 'path' => '']];

		if ($relative === '') {
			return $crumbs;
		}

		$built = '';

		foreach (explode('/', $relative) as $part) {
			$built = $built === '' ? $part : $built . '/' . $part;
			$crumbs[] = ['label' => $part, 'path' => $built];
		}

		return $crumbs;
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
		];
	}
}
