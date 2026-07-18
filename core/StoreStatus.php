<?php

class StoreStatus
{
	public static function isActive(): bool
	{
		return Settings::get('SHOP_ACTIVE') !== '0';
	}

	public static function getClientIp(): string
	{
		return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
	}

	/** @return array<int, string> */
	public static function getAllowedIps(): array
	{
		$raw = (string) Settings::get('SHOP_MAINTENANCE_IPS');
		$parts = preg_split('/[\r\n,;]+/', $raw) ?: [];
		$ips = [];

		foreach ($parts as $part) {
			$ip = trim((string) $part);

			if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
				$ips[] = $ip;
			}
		}

		return array_values(array_unique($ips));
	}

	public static function isIpAllowed(?string $ip = null): bool
	{
		$ip = $ip ?? self::getClientIp();

		if ($ip === '') {
			return false;
		}

		return in_array($ip, self::getAllowedIps(), true);
	}

	public static function shouldBlockFront(): bool
	{
		if (self::isActive()) {
			return false;
		}

		return !self::isIpAllowed();
	}

	public static function getDefaultMaintenanceMessage(): string
	{
		return '<p>Mağazamız şu anda bakım modundadır.</p>'
			. '<p>Lütfen kısa bir süre sonra tekrar ziyaret edin.</p>';
	}

	public static function getMaintenanceMessage(): string
	{
		$message = trim((string) Settings::get('SHOP_MAINTENANCE_MESSAGE'));

		return $message !== '' ? $message : self::getDefaultMaintenanceMessage();
	}

	public static function renderMaintenance(): void
	{
		global $smarty;

		if (!class_exists('SiteAssets', false)) {
			require_once dirname(__DIR__) . '/core/SiteAssets.php';
		}

		http_response_code(503);
		header('Retry-After: 3600');
		header('Cache-Control: no-store, no-cache, must-revalidate');

		$smarty->assign([
			'maintenanceMessage' => self::getMaintenanceMessage(),
			'siteName' => Settings::get('SITE_NAME') ?: 'FShop',
			'logoUrl' => SiteAssets::getLogoUrl('header'),
		]);

		$smarty->display('shared/maintenance.tpl');
		exit;
	}
}
