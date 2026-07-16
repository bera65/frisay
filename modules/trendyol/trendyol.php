<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/lib/TrendyolApi.php';
require_once __DIR__ . '/lib/ProductSyncService.php';
require_once __DIR__ . '/lib/OrderService.php';
require_once __DIR__ . '/lib/QuestionService.php';

use Trendyol\OrderService;
use Trendyol\ProductSyncService;
use Trendyol\QuestionService;

class TrendyolModule extends ModuleBase
{
	public string $name = 'trendyol';
	public string $title = 'Trendyol Entegrasyonu';
	public string $version = '1.0.0';
	public string $description = 'Ürün aktarımı, fiyat/stok güncelleme, sipariş ve ürün soruları';
	public string $author = 'FShop';

	public array $displayHooks = [
		'admin_product_button' => 'Ürün düzenleme — Trendyol aktar / fiyat güncelle',
	];

	public array $defaultDisplayHooks = ['admin_product_button'];

	public array $adminStylesheets = ['admin.css'];
	public array $adminScripts = ['admin.js'];

	public array $apiActions = [
		'cron' => 'api/cron.php',
		'sync' => 'api/sync.php',
		'update-price' => 'api/update-price.php',
		'refresh' => 'api/refresh.php',
		'brands' => 'api/brands.php',
		'categories' => 'api/categories.php',
		'attributes' => 'api/attributes.php',
		'fetch-orders' => 'api/fetch-orders.php',
		'fetch-questions' => 'api/fetch-questions.php',
		'answer-question' => 'api/answer-question.php',
	];

	public function install(): bool
	{
		ProductSyncService::ensureSchema();
		Settings::set('TRENDYOL_MERCHANT_ID', Settings::get('TRENDYOL_MERCHANT_ID') ?: '');
		Settings::set('TRENDYOL_API_KEY', Settings::get('TRENDYOL_API_KEY') ?: '');
		Settings::set('TRENDYOL_API_SECRET', Settings::get('TRENDYOL_API_SECRET') ?: '');
		Settings::set('TRENDYOL_DEFAULT_BRAND_ID', Settings::get('TRENDYOL_DEFAULT_BRAND_ID') ?: '');
		Settings::set('TRENDYOL_DEFAULT_CATEGORY_ID', Settings::get('TRENDYOL_DEFAULT_CATEGORY_ID') ?: '');
		Settings::set('TRENDYOL_DELIVERY_DURATION', Settings::get('TRENDYOL_DELIVERY_DURATION') ?: '1');
		Settings::set('TRENDYOL_CARGO_COMPANY_ID', Settings::get('TRENDYOL_CARGO_COMPANY_ID') ?: '');
		Settings::set('TRENDYOL_SHIPMENT_ADDRESS_ID', Settings::get('TRENDYOL_SHIPMENT_ADDRESS_ID') ?: '');
		Settings::set('TRENDYOL_RETURNING_ADDRESS_ID', Settings::get('TRENDYOL_RETURNING_ADDRESS_ID') ?: '');

		return true;
	}

	public function uninstall(): bool
	{
		\DB::execute('DROP TABLE IF EXISTS trendyol_questions');
		\DB::execute('DROP TABLE IF EXISTS trendyol_orders');
		\DB::execute('DROP TABLE IF EXISTS trendyol_category_map');
		\DB::execute('DROP TABLE IF EXISTS trendyol_products');

		return true;
	}

	public function boot(): void
	{
		ProductSyncService::ensureSchema();
	}

	public function renderAdminDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'admin_product_button') {
			return null;
		}

		$idProduct = (int) ($context['id_product'] ?? Tools::getValue('id'));
		$isNew = !empty($context['is_new']);

		if ($isNew || $idProduct <= 0) {
			return null;
		}

		$product = is_array($context['product'] ?? null) ? $context['product'] : \Product::getByIdAdmin($idProduct);
		$mapping = ProductSyncService::findMapping($idProduct);
		$domain = rtrim((string) Settings::get('DOMAIN'), '/');

		$attrs = [];

		if ($mapping && !empty($mapping['attributes_json'])) {
			$decoded = json_decode((string) $mapping['attributes_json'], true);
			$attrs = is_array($decoded) ? $decoded : [];
		}

		$defaultBrand = (int) (Settings::get('TRENDYOL_DEFAULT_BRAND_ID') ?: 0);
		$defaultCategory = 0;
		$defaultCategoryName = (string) (Settings::get('TRENDYOL_DEFAULT_CATEGORY_NAME') ?: '');

		if ($product) {
			$maps = ProductSyncService::getCategoryMaps();

			foreach ($maps as $map) {
				if ((int) ($map['id_category'] ?? 0) === (int) ($product['id_category'] ?? 0)) {
					$defaultCategory = (int) ($map['trendyol_category_id'] ?? 0);
					$defaultCategoryName = (string) ($map['trendyol_category_name'] ?? $defaultCategoryName);

					if ($attrs === [] && !empty($map['attributes_json'])) {
						$decoded = json_decode((string) $map['attributes_json'], true);

						if (is_array($decoded)) {
							$attrs = $decoded;
						}
					}

					break;
				}
			}
		}

		if ($defaultCategory <= 0) {
			$defaultCategory = (int) (Settings::get('TRENDYOL_DEFAULT_CATEGORY_ID') ?: 0);
		}

		$html = $this->renderAdminTemplate('admin_product_button', [
			'id_product' => $idProduct,
			'mapping' => $mapping,
			'configured' => ProductSyncService::isConfigured(),
			'product_barcode' => (string) ($product['barcode'] ?? ''),
			'product_price' => (float) ($product['price'] ?? 0),
			'product_old_price' => (float) ($product['old_price'] ?? 0),
			'product_stock' => $product ? (int) \Product::getStock($product) : 0,
			'ty_sale_price' => (float) ($mapping['sale_price'] ?? ($product['price'] ?? 0)),
			'ty_list_price' => (float) ($mapping['list_price'] ?? (
				((float) ($product['old_price'] ?? 0) > (float) ($product['price'] ?? 0))
					? ($product['old_price'] ?? 0)
					: ($product['price'] ?? 0)
			)),
			'ty_brand_id' => (int) ($mapping['brand_id'] ?? $defaultBrand),
			'ty_brand_name' => (string) (Settings::get('TRENDYOL_DEFAULT_BRAND_NAME') ?: ''),
			'ty_category_id' => (int) ($mapping['category_id'] ?? $defaultCategory),
			'ty_category_name' => $defaultCategoryName,
			'ty_attributes' => $attrs,
			'ty_attributes_json' => json_encode($attrs, JSON_UNESCAPED_UNICODE),
			'syncUrl' => $domain . '/api/module.php?m=trendyol&action=sync',
			'priceUrl' => $domain . '/api/module.php?m=trendyol&action=update-price',
			'refreshUrl' => $domain . '/api/module.php?m=trendyol&action=refresh',
			'brandsUrl' => $domain . '/api/module.php?m=trendyol&action=brands',
			'categoriesUrl' => $domain . '/api/module.php?m=trendyol&action=categories',
			'attributesUrl' => $domain . '/api/module.php?m=trendyol&action=attributes',
			'assetsJsUrl' => $domain . '/modules/trendyol/assets/js/admin.js',
			'assetsCssUrl' => $domain . '/modules/trendyol/assets/css/admin.css',
			'settingsUrl' => $domain . '/admin/module-trendyol',
		]);

		return $html !== '' ? $html : null;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';
		$tab = (string) Tools::getValue('tab', 'settings');

		if (Tools::isSubmit('saveTrendyol')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('TRENDYOL_MERCHANT_ID', trim((string) Tools::getValue('merchant_id')));
				Settings::set('TRENDYOL_API_KEY', trim((string) Tools::getValue('api_key')));
				Settings::set('TRENDYOL_API_SECRET', trim((string) Tools::getValue('api_secret')));
				Settings::set('TRENDYOL_DEFAULT_BRAND_ID', trim((string) Tools::getValue('default_brand_id')));
				Settings::set('TRENDYOL_DEFAULT_BRAND_NAME', trim((string) Tools::getValue('default_brand_name')));
				Settings::set('TRENDYOL_DEFAULT_CATEGORY_ID', trim((string) Tools::getValue('default_category_id')));
				Settings::set('TRENDYOL_DEFAULT_CATEGORY_NAME', trim((string) Tools::getValue('default_category_name')));
				Settings::set('TRENDYOL_DELIVERY_DURATION', (string) max(1, min(3, (int) Tools::getValue('delivery_duration', 1))));
				Settings::set('TRENDYOL_CARGO_COMPANY_ID', trim((string) Tools::getValue('cargo_company_id')));
				Settings::set('TRENDYOL_SHIPMENT_ADDRESS_ID', trim((string) Tools::getValue('shipment_address_id')));
				Settings::set('TRENDYOL_RETURNING_ADDRESS_ID', trim((string) Tools::getValue('returning_address_id')));
				$flash = 'Trendyol ayarları kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		if (Tools::isSubmit('saveTrendyolCategories')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$maps = Tools::getValue('ty_category');
				$names = Tools::getValue('ty_category_name');
				$attrs = Tools::getValue('ty_attributes');

				if (is_array($maps)) {
					foreach ($maps as $idCategory => $tyCategoryId) {
						$attrJson = '';
						$catName = '';

						if (is_array($attrs) && isset($attrs[$idCategory])) {
							$attrJson = trim((string) $attrs[$idCategory]);
						}

						if (is_array($names) && isset($names[$idCategory])) {
							$catName = trim((string) $names[$idCategory]);
						}

						ProductSyncService::saveCategoryMap((int) $idCategory, (int) $tyCategoryId, $attrJson, $catName);
					}
				}

				$flash = 'Kategori eşlemeleri kaydedildi';
				$tab = 'categories';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		if (Tools::isSubmit('syncTrendyolOrders')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$start = trim((string) Tools::getValue('start_date'));
				$end = trim((string) Tools::getValue('end_date'));
				$result = OrderService::syncOrders($start !== '' ? $start : null, $end !== '' ? $end : null);
				$flash = $result['message'];
				$tab = 'orders';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		if (Tools::isSubmit('syncTrendyolQuestions')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = QuestionService::syncQuestions(0, 50);
				$flash = $result['message'];
				$tab = 'questions';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		if (Tools::isSubmit('answerTrendyolQuestion')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				$result = QuestionService::answer(
					(int) Tools::getValue('question_id'),
					(string) Tools::getValue('answer_text')
				);
				$flash = $result['message'];
				$tab = 'questions';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$categoryOptions = Category::getMenuList();
		$categoryMaps = [];
		$categoryMapNames = [];
		$categoryAttrs = [];

		foreach (ProductSyncService::getCategoryMaps() as $row) {
			$categoryMaps[(int) $row['id_category']] = (int) $row['trendyol_category_id'];
			$categoryMapNames[(int) $row['id_category']] = (string) ($row['trendyol_category_name'] ?? '');
			$categoryAttrs[(int) $row['id_category']] = (string) ($row['attributes_json'] ?? '');
		}

		$domain = rtrim((string) Settings::get('DOMAIN'), '/');

		$smarty->assign([
			'flash' => $flash,
			'tab' => $tab,
			'tyMerchantId' => Settings::get('TRENDYOL_MERCHANT_ID'),
			'tyApiKey' => Settings::get('TRENDYOL_API_KEY'),
			'tyApiSecret' => Settings::get('TRENDYOL_API_SECRET'),
			'tyDefaultBrandId' => Settings::get('TRENDYOL_DEFAULT_BRAND_ID'),
			'tyDefaultBrandName' => Settings::get('TRENDYOL_DEFAULT_BRAND_NAME'),
			'tyDefaultCategoryId' => Settings::get('TRENDYOL_DEFAULT_CATEGORY_ID'),
			'tyDefaultCategoryName' => Settings::get('TRENDYOL_DEFAULT_CATEGORY_NAME'),
			'tyDeliveryDuration' => (int) (Settings::get('TRENDYOL_DELIVERY_DURATION') ?: 1),
			'tyCargoCompanyId' => Settings::get('TRENDYOL_CARGO_COMPANY_ID'),
			'tyShipmentAddressId' => Settings::get('TRENDYOL_SHIPMENT_ADDRESS_ID'),
			'tyReturningAddressId' => Settings::get('TRENDYOL_RETURNING_ADDRESS_ID'),
			'tyConfigured' => ProductSyncService::isConfigured(),
			'categoryOptions' => $categoryOptions,
			'categoryMaps' => $categoryMaps,
			'categoryMapNames' => $categoryMapNames,
			'categoryAttrs' => $categoryAttrs,
			'recentSyncs' => ProductSyncService::getRecentSyncs(40),
			'tyOrders' => OrderService::getRecent(50),
			'tyQuestions' => QuestionService::getRecent(50),
			'brandsApiUrl' => $domain . '/api/module.php?m=trendyol&action=brands',
			'categoriesApiUrl' => $domain . '/api/module.php?m=trendyol&action=categories',
			'attributesApiUrl' => $domain . '/api/module.php?m=trendyol&action=attributes',
			'cronOrdersUrl' => $domain . '/api/module.php?m=trendyol&action=cron&type=orders&token=' . urlencode((string) Settings::get('SHOP_TOKEN')),
			'cronQuestionsUrl' => $domain . '/api/module.php?m=trendyol&action=cron&type=questions&token=' . urlencode((string) Settings::get('SHOP_TOKEN')),
			'adminToken' => $adminToken,
		]);
	}
}
