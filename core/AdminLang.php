<?php

class AdminLang
{
	private static ?array $strings = null;
	private static string $loadedLang = '';

	/** @return string[] */
	public static function getAvailable(): array
	{
		return ['tr', 'en'];
	}

	public static function isValid(string $code): bool
	{
		return in_array($code, self::getAvailable(), true);
	}

	public static function getDefault(): string
	{
		if (class_exists('Settings', false)) {
			$setting = trim((string) Settings::get('ADMIN_DEFAULT_LANG'));

			if (self::isValid($setting)) {
				return $setting;
			}
		}

		return 'en';
	}

	public static function current(): string
	{
		if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['adminLang'])) {
			$code = strtolower(trim((string) $_SESSION['adminLang']));

			if (self::isValid($code)) {
				return $code;
			}
		}

		return self::getDefault();
	}

	public static function handleSwitchRequest(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE || !isset($_GET['set_admin_lang'])) {
			return;
		}

		$code = strtolower(trim((string) $_GET['set_admin_lang']));

		if (self::isValid($code)) {
			$_SESSION['adminLang'] = $code;
		}

		$redirect = trim((string) ($_GET['redirect'] ?? ''));

		if ($redirect === '') {
			$redirect = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/', PHP_URL_PATH) ?: '/admin/';
		}

		if (strpos($redirect, '://') !== false || strncmp($redirect, '//', 2) === 0) {
			$redirect = '/admin/';
		}

		header('Location: ' . $redirect);
		exit;
	}

	public static function makeSwitchUrl(string $code): string
	{
		$code = strtolower(trim($code));
		$path = parse_url($_SERVER['REQUEST_URI'] ?? '/admin/', PHP_URL_PATH) ?: '/admin/';
		$query = $_GET;
		unset($query['set_admin_lang'], $query['redirect']);

		$query['set_admin_lang'] = $code;
		$query['redirect'] = $path;

		return $path . '?' . http_build_query($query);
	}

	public static function label(string $code): string
	{
		$labels = [
			'tr' => 'Türkçe',
			'en' => 'English',
		];

		return $labels[$code] ?? strtoupper($code);
	}

	/** @return array<int, array{code: string, label: string, url: string, active: bool}> */
	public static function getSwitcherList(): array
	{
		$current = self::current();
		$list = [];

		foreach (self::getAvailable() as $code) {
			$list[] = [
				'code' => $code,
				'label' => self::label($code),
				'url' => self::makeSwitchUrl($code),
				'active' => $code === $current,
			];
		}

		return $list;
	}

	public static function translate(string $text): string
	{
		$langCode = self::current();

		if (self::$strings === null || self::$loadedLang !== $langCode) {
			self::$loadedLang = $langCode;
			$path = dirname(__DIR__) . '/lang/admin/' . $langCode . '.php';

			if (!is_file($path)) {
				$path = dirname(__DIR__) . '/lang/admin/en.php';
			}

			self::$strings = is_file($path) ? require $path : [];

			if (!is_array(self::$strings)) {
				self::$strings = [];
			}
		}

		if (isset(self::$strings[$text])) {
			return (string) self::$strings[$text];
		}

		if ($langCode !== 'en') {
			static $enStrings = null;

			if ($enStrings === null) {
				$enPath = dirname(__DIR__) . '/lang/admin/en.php';
				$enStrings = is_file($enPath) ? require $enPath : [];

				if (!is_array($enStrings)) {
					$enStrings = [];
				}
			}

			if (isset($enStrings[$text])) {
				return (string) $enStrings[$text];
			}
		}

		return $text;
	}
}
