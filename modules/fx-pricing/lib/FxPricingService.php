<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class FxPricingService
{
	public const SET_ENABLED = 'FX_PRICING_ENABLED';
	public const SET_ADMIN_CURRENCIES = 'FX_PRICING_ADMIN_CURRENCIES';

	private static function loadExchangeRate(): void
	{
		if (!class_exists('ExchangeRate', false)) {
			require_once dirname(__DIR__, 3) . '/core/ExchangeRate.php';
		}
	}

	public static function isEnabled(): bool
	{
		return Settings::get(self::SET_ENABLED, '1') === '1';
	}

	/** @return array<int, array{code: string, label: string, symbol: string}> */
	public static function getAdminCurrencyOptions(): array
	{
		$shop = Currency::getShopCurrency();
		$configured = trim((string) Settings::get(self::SET_ADMIN_CURRENCIES, 'usd,eur'));
		$codes = $configured !== '' ? explode(',', $configured) : ['usd', 'eur'];
		$meta = Currency::getMetaMap();
		$list = [];

		foreach ($codes as $code) {
			$code = strtolower(trim($code));

			if ($code === '' || $code === $shop || !isset($meta[$code])) {
				continue;
			}

			$list[] = [
				'code' => $code,
				'label' => $meta[$code]['label'] . ' (' . strtoupper($code) . ')',
				'symbol' => $meta[$code]['symbol'],
			];
		}

		if ($list === []) {
			foreach (['usd', 'eur'] as $code) {
				if ($code === $shop || !isset($meta[$code])) {
					continue;
				}

				$list[] = [
					'code' => $code,
					'label' => $meta[$code]['label'] . ' (' . strtoupper($code) . ')',
					'symbol' => $meta[$code]['symbol'],
				];
			}
		}

		return $list;
	}

	public static function handleProductSave(int $idProduct): void
	{
		if ($idProduct <= 0 || !self::isEnabled()) {
			return;
		}

		self::ensureSchema();

		$useFx = Tools::getValue('fx_use') === '1';
		$currency = strtolower(trim((string) Tools::getValue('fx_currency', '')));
		$fxCost = (float) str_replace(',', '.', (string) Tools::getValue('fx_cost', '0'));
		$fxPrice = (float) str_replace(',', '.', (string) Tools::getValue('fx_price', '0'));
		$fxOldPrice = (float) str_replace(',', '.', (string) Tools::getValue('fx_old_price', '0'));
		$shopCurrency = Currency::getShopCurrency();

		self::loadExchangeRate();

		if (!$useFx || $currency === '' || $currency === $shopCurrency || $fxPrice <= 0) {
			$product = Product::getByIdAdmin($idProduct);

			if (!$product) {
				return;
			}

			DB::update('products', [
				'doviz' => $shopCurrency,
				'doviz_cost' => (float) ($product['cost'] ?? 0),
				'doviz_price' => (float) ($product['price'] ?? 0),
				'doviz_old_price' => (float) ($product['old_price'] ?? 0),
			], 'id_product = :where_id', ['where_id' => $idProduct]);

			return;
		}

		$allowed = array_column(self::getAdminCurrencyOptions(), 'code');

		if ($allowed !== [] && !in_array($currency, $allowed, true)) {
			return;
		}

		$tryCost = $fxCost > 0 ? ExchangeRate::toTry($fxCost, $currency) : 0.0;
		$tryPrice = ExchangeRate::toTry($fxPrice, $currency);
		$tryOldPrice = $fxOldPrice > 0 ? ExchangeRate::toTry($fxOldPrice, $currency) : 0.0;

		DB::update('products', [
			'doviz' => $currency,
			'doviz_cost' => max(0, $fxCost),
			'doviz_price' => max(0, $fxPrice),
			'doviz_old_price' => max(0, $fxOldPrice),
			'cost' => max(0, $tryCost),
			'price' => max(0, $tryPrice),
			'old_price' => max(0, $tryOldPrice),
		], 'id_product = :where_id', ['where_id' => $idProduct]);
	}

	public static function ensureSchema(): void
	{
		Product::ensureSchema();
	}

	public static function resolveFxCost(array $product, string $currency, bool $useFx): float
	{
		if (!$useFx) {
			return 0.0;
		}

		$fxCost = (float) ($product['doviz_cost'] ?? 0);

		if ($fxCost > 0) {
			return $fxCost;
		}

		$cost = (float) ($product['cost'] ?? 0);

		if ($cost <= 0 || $currency === '') {
			return 0.0;
		}

		self::loadExchangeRate();

		return ExchangeRate::fromTry($cost, $currency);
	}

	/** @return array{products: int, rates: array<string, float>} */
	public static function refreshAll(bool $forceRates = false): array
	{
		self::loadExchangeRate();

		$rates = ExchangeRate::getRates($forceRates);

		return [
			'products' => ExchangeRate::refreshProductPrices(),
			'rates' => $rates,
		];
	}

	public static function countFxProducts(): int
	{
		$shop = Currency::getShopCurrency();
		$rows = DB::execute(
			'SELECT COUNT(*) AS cnt FROM products WHERE doviz_price > 0 AND LOWER(doviz) <> ? AND LOWER(doviz) <> ?',
			['', $shop]
		);

		return (int) ($rows[0]['cnt'] ?? 0);
	}

	/** @return array<string, float> */
	public static function getPublicRates(): array
	{
		self::loadExchangeRate();

		return ExchangeRate::getRates();
	}

	/** @return array<int, array{code: string, label: string, symbol: string, is_active: bool, url: string}> */
	public static function buildSwitcherOptions(?string $redirect = null): array
	{
		if ($redirect === null) {
			$redirect = Currency::normalizeRedirectPath(null, true);
		}

		$display = Currency::getDisplayCurrency();
		$options = [];

		foreach (Currency::getOptions() as $row) {
			$code = (string) ($row['code'] ?? '');

			if ($code === '') {
				continue;
			}

			$options[] = [
				'code' => $code,
				'label' => (string) ($row['label'] ?? strtoupper($code)),
				'symbol' => (string) ($row['symbol'] ?? strtoupper($code)),
				'is_active' => $code === $display,
				'url' => Currency::buildSwitchUrl($code, $redirect),
			];
		}

		return $options;
	}

	public static function currentRedirectPath(): string
	{
		return Currency::normalizeRedirectPath(null, true);
	}
}
