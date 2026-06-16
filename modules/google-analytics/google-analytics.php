<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class GoogleAnalyticsModule extends ModuleBase
{
	public string $name = 'google-analytics';
	public string $title = 'Google Analytics';
	public string $version = '1.0.0';
	public string $description = 'Google Analytics 4 (gtag.js) entegrasyonu';
	public string $author = 'FShop';

	public array $displayHooks = [
		'footer' => 'Sayfa alt kısmına GA4 tracking kodu ekler',
	];
	public array $defaultDisplayHooks = ['footer'];

	public array $frontStylesheets = [];
	public array $frontScripts = [];
	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = [];

	public function install(): bool
	{
		// Varsayılan olarak ayar boş bırakılır, admin panelinden doldurulacak
		return true;
	}

	public function uninstall(): bool
	{
		Settings::delete('GA_TRACKING_ID');
		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'info';

		if (Tools::isSubmit('saveGaSettings')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$trackingId = trim((string) Tools::getValue('tracking_id'));

				// GA4 Tracking ID format kontrolü: G-XXXXXXXXXX
				if ($trackingId !== '' && !preg_match('/^G-[A-Z0-9]{10,}$/i', $trackingId)) {
					$flash = 'Geçersiz Tracking ID formatı. Örnek: G-XXXXXXXXXX';
					$flashType = 'danger';
				} else {
					Settings::set('GA_TRACKING_ID', $trackingId);
					$flash = 'Ayarlar kaydedildi';
					$flashType = 'success';
				}
			} else {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			}
		}

		$smarty->assign([
			'trackingId' => Settings::get('GA_TRACKING_ID'),
			'flash' => $flash,
			'flashType' => $flashType,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'footer') {
			return null;
		}

		$trackingId = Settings::get('GA_TRACKING_ID');

		// Tracking ID boşsa hiçbir şey render etme
		if ($trackingId === '') {
			return null;
		}

		$html = $this->renderFrontTemplate('footer', [
			'trackingId' => $trackingId,
		]);

		return $html !== '' ? $html : null;
	}
}