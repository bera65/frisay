<?php

class App
{
	private static array $env = [];
	private static bool $booted = false;

	public static function boot(): void
	{
		if (self::$booted) {
			return;
		}

		self::$booted = true;

		$envFile = __DIR__ . '/env.php';

		if (!is_file($envFile)) {
			$envFile = __DIR__ . '/env.example.php';
		}

		$config = require $envFile;
		self::$env = is_array($config) ? $config : [];

		self::configureErrors();
	}

	public static function env(string $key, $default = null)
	{
		return self::$env[$key] ?? $default;
	}

	public static function isProduction(): bool
	{
		return self::env('APP_ENV', 'local') === 'production';
	}

	public static function isDebug(): bool
	{
		if (self::isProduction()) {
			return (bool) self::env('APP_DEBUG', false);
		}

		return (bool) self::env('APP_DEBUG', true);
	}

	public static function configureErrors(): void
	{
		$logDir = dirname(__DIR__) . '/logs';

		if (!is_dir($logDir)) {
			@mkdir($logDir, 0755, true);
		}

		$logFile = $logDir . '/php-error.log';

		if (self::isDebug()) {
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			error_reporting(E_ALL);
		} else {
			ini_set('display_errors', '0');
			ini_set('display_startup_errors', '0');
			error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
			ini_set('log_errors', '1');
			ini_set('error_log', $logFile);
		}
	}

	public static function configureSession(): void
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			return;
		}

		ini_set('session.use_strict_mode', '1');
		ini_set('session.cookie_httponly', '1');
		ini_set('session.use_only_cookies', '1');

		if (self::isProduction()) {
			ini_set('session.cookie_samesite', 'Lax');
		}

		$domain = Settings::get('DOMAIN');

		if (self::isProduction() && is_string($domain) && strpos($domain, 'https://') === 0) {
			ini_set('session.cookie_secure', '1');
		}
	}

	public static function sendSecurityHeaders(): void
	{
		if (headers_sent()) {
			return;
		}

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('X-XSS-Protection: 1; mode=block');

		if (self::isProduction()) {
			header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
		}
	}
}
