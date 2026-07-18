<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/ProductSetService.php';

class ProductSetModule extends ModuleBase
{
	public string $name = 'product-set';
	public string $title = 'Ürün Seti';
	public string $version = '1.0.0';
	public string $description = 'Birden fazla ürünü set olarak sat; stok en düşük bileşene göre, sepete eklenince parçalanır';
	public string $author = 'FShop';

	public array $displayHooks = [
		'product_detail' => 'Ürün sayfasında set içeriği',
		'admin_product_button' => 'Ürün formunda set bileşenleri',
	];

	public array $defaultDisplayHooks = ['product_detail', 'admin_product_button'];

	public array $frontStylesheets = ['front.css'];
	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public array $apiActions = [
		'search' => 'api/search.php',
	];

	public function install(): bool
	{
		ProductSetService::ensureSchema();
		Product::ensureSchema();

		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}

	public function boot(): void
	{
		ProductSetService::ensureSchema();

		Module::registerHook('product.updated', static function ($idProduct): void {
			$idProduct = (int) $idProduct;

			if ($idProduct <= 0 || !Tools::isSubmit('saveProduct')) {
				return;
			}

			$product = Product::getByIdAdmin($idProduct);
			if (!$product || ($product['product_type'] ?? '') !== 'pack') {
				return;
			}

			$raw = Tools::getValue('pack_items');
			$items = [];

			if (is_string($raw) && $raw !== '') {
				$decoded = json_decode($raw, true);
				if (is_array($decoded)) {
					$items = $decoded;
				}
			} elseif (is_array($raw)) {
				$items = $raw;
			}

			ProductSetService::saveItems($idProduct, $items);
		});

		Module::registerHook('smarty.assign', static function ($smarty): void {
			if (!$smarty || defined('IN_ADMIN')) {
				return;
			}

			$product = $smarty->getTemplateVars('product');
			if (!is_array($product) || ($product['product_type'] ?? '') !== 'pack') {
				return;
			}

			$id = (int) ($product['id_product'] ?? 0);
			if ($id <= 0) {
				return;
			}

			$smarty->assign([
				'productSetItems' => ProductSetService::getItems($id),
				'productSetPricing' => ProductSetService::getPricing($id, $product),
			]);
		});
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'product_detail') {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);
		if ($idProduct <= 0) {
			return null;
		}

		$product = Product::getById($idProduct);
		if (!$product || empty($product['is_pack'])) {
			return null;
		}

		$items = ProductSetService::getItems($idProduct);
		if ($items === []) {
			return null;
		}

		$pricing = ProductSetService::getPricing($idProduct, $product);

		$html = $this->renderFrontTemplate('product_detail', [
			'items' => $items,
			'pricing' => $pricing,
			'packStock' => ProductSetService::getAvailableStock($idProduct),
			'packPriceFormatted' => Tools::displayPrice((float) $pricing['price']),
			'packComponentsFormatted' => Tools::displayPrice((float) $pricing['components_total']),
		]);

		return $html !== '' ? $html : null;
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_product_button') {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? 0);
		$isNew = !empty($context['is_new']);
		$productType = (string) ($context['product_type'] ?? 'physical');

		if (!$isNew && $idProduct > 0) {
			$existing = Product::getByIdAdmin($idProduct);
			if ($existing) {
				$productType = (string) ($existing['product_type'] ?? $productType);
			}
		}

		$items = (!$isNew && $idProduct > 0) ? ProductSetService::getItems($idProduct) : [];
		$override = '';

		if (!$isNew && $idProduct > 0) {
			$row = Product::getByIdAdmin($idProduct);
			if ($row && $row['pack_price_override'] !== null && $row['pack_price_override'] !== '') {
				$override = (string) $row['pack_price_override'];
			}
		}

		global $domain, $adminToken;

		$html = $this->renderAdminTemplate('admin_product_button', [
			'id_product' => $idProduct,
			'is_new' => $isNew,
			'product_type' => $productType,
			'packItems' => $items,
			'packItemsJson' => json_encode(array_map(static function (array $row): array {
				return [
					'id_product' => (int) $row['id_product'],
					'product_name' => (string) $row['product_name'],
					'qty' => (int) $row['qty'],
					'price' => (float) $row['price'],
					'price_formatted' => (string) $row['price_formatted'],
					'stock' => (int) ($row['child_stock'] ?? $row['stock'] ?? 0),
				];
			}, $items), JSON_UNESCAPED_UNICODE),
			'pack_price_override' => $override,
			'searchApi' => rtrim((string) $domain, '/') . '/api/module.php?m=product-set&action=search',
			'adminToken' => $adminToken,
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty;

		$smarty->assign([
			'flash' => 'Set bileşenlerini ürün düzenleme ekranından yönetin. Ürün tipini “Set (paket)” seçin.',
			'flashType' => 'info',
		]);
	}
}
