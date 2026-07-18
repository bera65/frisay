<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/BulkPricingService.php';

class BulkPricingModule extends ModuleBase
{
	public string $name = 'bulk-pricing';
	public string $title = 'Toplu Fiyat Güncelleme';
	public string $version = '1.0.0';
	public string $description = 'Kategori ve markaya göre alış / satış / eski fiyatlara toplu zam veya indirim uygular';
	public string $author = 'FShop';

	public array $frontStylesheets = ['.no-global-assets'];
	public array $frontScripts = ['.no-global-assets'];
	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public function install(): bool
	{
		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$flashType = 'info';
		$previewRows = [];
		$matchCount = 0;
		$adjustmentLabel = '';

		$filters = BulkPricingService::parseFilters($_POST);
		$adjustment = BulkPricingService::parseAdjustment($_POST);

		if (
			$adjustment['fields'] === []
			&& !Tools::isSubmit('bulkPricingPreview')
			&& !Tools::isSubmit('bulkPricingApply')
		) {
			$adjustment['fields'] = [BulkPricingService::FIELD_PRICE];
			$adjustment['value'] = 10.0;
		}

		if (Tools::isSubmit('bulkPricingPreview') || Tools::isSubmit('bulkPricingApply')) {
			$postToken = (string) Tools::getValue('token');

			if (!hash_equals($adminToken, $postToken)) {
				$flash = 'Geçersiz istek';
				$flashType = 'danger';
			} else {
				$error = BulkPricingService::validateAdjustment($adjustment);

				if ($error !== null) {
					$flash = $error;
					$flashType = 'danger';
				} else {
					$matchCount = BulkPricingService::countMatching($filters);
					$adjustmentLabel = BulkPricingService::formatAdjustmentLabel($adjustment);

					if (Tools::isSubmit('bulkPricingPreview')) {
						$previewRows = BulkPricingService::buildPreviewRows($filters, $adjustment);
						$flash = $matchCount . ' ürün bulundu. Aşağıda örnek önizleme gösteriliyor.';
						$flashType = 'info';
					}

					if (Tools::isSubmit('bulkPricingApply')) {
						$result = BulkPricingService::apply($filters, $adjustment);
						$flash = $result['updated'] . ' ürün güncellendi';

						if ($result['skipped'] > 0) {
							$flash .= ', ' . $result['skipped'] . ' ürün atlandı';
						}

						$flashType = 'success';
						$previewRows = BulkPricingService::buildPreviewRows($filters, $adjustment);
					}
				}
			}
		}

		$smarty->assign([
			'flash' => $flash,
			'flashType' => $flashType,
			'adminToken' => $adminToken,
			'filters' => $filters,
			'adjustment' => $adjustment,
			'adjustmentLabel' => $adjustmentLabel,
			'matchCount' => $matchCount,
			'previewRows' => $previewRows,
			'categoryOptions' => BulkPricingService::getCategoryOptions(),
			'brandOptions' => Brand::getOptions(),
			'shopCurrency' => Currency::getShopCurrency(),
		]);
	}
}
