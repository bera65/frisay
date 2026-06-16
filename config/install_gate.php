<?php

if (!function_exists('fshop_installed_lock_path')) {
	function fshop_installed_lock_path(): string
	{
		return __DIR__ . '/installed.lock';
	}

	function fshop_is_installed(): bool
	{
		return is_file(__DIR__ . '/env.php');
	}

	function fshop_app_base_path(): string
	{
		$script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

		if (preg_match('#/(admin|api)(/|$)#', $script)) {
			$script = dirname($script);
		}

		$base = rtrim(dirname($script), '/');

		return $base === '/' || $base === '.' ? '/' : $base . '/';
	}

	function fshop_redirect_to_installer(): void
	{
		$target = fshop_app_base_path() . 'install/';

		if (!headers_sent()) {
			header('Location: ' . $target);
		}

		exit;
	}
}
