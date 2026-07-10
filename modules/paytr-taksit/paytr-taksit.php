<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class PaytrTaksitModule extends ModuleBase
{
	public string $name = 'paytr-taksit';
	public string $title = 'Taksit Tablosu';
	public string $version = '1.0.0';
	public string $description = 'Ürüne ait taksitleri gösterir';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_tab' => 'Ürün Tabı',
		'product_tab_content' => 'Ürün sayfası',
	];

	public array $defaultDisplayHooks = ['product_tab', 'product_tab_content'];

	public array $frontStylesheets = ['taksit.css'];

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

		if (Tools::isSubmit('saveForm')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$paytrToken 	= (string) Tools::getValue('paytrToken');
				$paytrMerchant 	= (int) Tools::getValue('paytrMerchant');
				
				Settings::set('PAYTR_TAKSIT_TOKEN', trim((string) $paytrToken));
				Settings::set('PAYTR_TAKSIT_MERCHANT', trim((int) $paytrMerchant));
				$flash = 'Kayıt Başarılı';
			}
		}

		$smarty->assign([
			'paytrToken' 	=> Settings::get('PAYTR_TAKSIT_TOKEN'),
			'paytrMerchant'	=> Settings::get('PAYTR_TAKSIT_MERCHANT'),
			'flash' 		=> $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if (!in_array($hook, ['product_tab', 'product_tab_content'], true)) {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);

		if ($idProduct <= 0) {
			return null;
		}

		global $domain;

		$price = (float)DB::getRow('products', 'id_product = '.(int)$idProduct.'', 'price');

		$html = $this->renderFrontTemplate($hook, [
			'productPrice' 	=> $price,
			'paytrToken' 	=> Settings::get('PAYTR_TAKSIT_TOKEN'),
			'paytrMerchant'	=> Settings::get('PAYTR_TAKSIT_MERCHANT'),
		]);

		return $html !== '' ? $html : null;
	}
}
