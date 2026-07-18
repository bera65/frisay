<?php

class ExchangeRate
{
	private const CACHE_KEY = 'FX_RATES_CACHE';
	private const CACHE_TTL = 3600;

	private const SYMBOL_MAP = [
		'usd' => 'USDTRY',
		'eur' => 'EURTRY',
		'gbp' => 'GBPTRY',
		'chf' => 'CHFTRY',
		'rub' => 'RUBTRY',
		'aed' => 'AEDTRY',
		'sar' => 'SARTRY',
	];

	/** @return array<string, float> Lowercase currency code => TRY per 1 unit */
	public static function getRates(bool $forceRefresh = false): array
	{
		if (!$forceRefresh) {
			$cached = self::readCache();

			if ($cached !== null) {
				return $cached;
			}
		}

		$fetched = self::fetchLiveRates();
		self::writeCache($fetched);

		return $fetched;
	}

	public static function getRate(string $currency): float
	{
		$currency = strtolower(trim($currency));

		if ($currency === '' || $currency === 'try') {
			return 1.0;
		}

		$rates = self::getRates();

		return (float) ($rates[$currency] ?? 0.0);
	}

	public static function toTry(float $amount, string $fromCurrency): float
	{
		if ($amount <= 0) {
			return 0.0;
		}

		$fromCurrency = strtolower(trim($fromCurrency));

		if ($fromCurrency === '' || $fromCurrency === 'try') {
			return round($amount, 2);
		}

		$rate = self::getRate($fromCurrency);

		if ($rate <= 0) {
			return 0.0;
		}

		return round($amount * $rate, 2);
	}

	public static function fromTry(float $tryAmount, string $toCurrency): float
	{
		if ($tryAmount <= 0) {
			return 0.0;
		}

		$toCurrency = strtolower(trim($toCurrency));

		if ($toCurrency === '' || $toCurrency === 'try') {
			return round($tryAmount, 2);
		}

		$rate = self::getRate($toCurrency);

		if ($rate <= 0) {
			return round($tryAmount, 2);
		}

		return round($tryAmount / $rate, 2);
	}

	public static function refreshProductPrices(): int
	{
		if (!class_exists('DB', false)) {
			return 0;
		}

		$shopCurrency = strtolower(trim(Currency::getShopCurrency()));
		$rows = DB::execute(
			'SELECT id_product, doviz, doviz_price, doviz_old_price, doviz_cost
			 FROM products
			 WHERE doviz_price > 0
			   AND LOWER(doviz) <> ?
			   AND LOWER(doviz) <> ?',
			['', $shopCurrency]
		) ?: [];

		$updated = 0;

		foreach ($rows as $row) {
			$id = (int) ($row['id_product'] ?? 0);
			$currency = strtolower(trim((string) ($row['doviz'] ?? '')));
			$fxPrice = (float) ($row['doviz_price'] ?? 0);

			if ($id <= 0 || $currency === '' || $fxPrice <= 0) {
				continue;
			}

			$newPrice = self::toTry($fxPrice, $currency);
			$newOld = 0.0;
			$fxOld = (float) ($row['doviz_old_price'] ?? 0);

			if ($fxOld > 0) {
				$newOld = self::toTry($fxOld, $currency);
			}

			$newCost = 0.0;
			$fxCost = (float) ($row['doviz_cost'] ?? 0);

			if ($fxCost > 0) {
				$newCost = self::toTry($fxCost, $currency);
			}

			$payload = [
				'price' => max(0, $newPrice),
				'old_price' => max(0, $newOld),
				'cost' => max(0, $newCost),
			];

			if (DB::update('products', $payload, 'id_product = :where_id', ['where_id' => $id]) !== false) {
				$updated++;
			}
		}

		return $updated;
	}

	/** @return array<string, float>|null */
	private static function readCache(): ?array
	{
		$raw = trim((string) Settings::get(self::CACHE_KEY));

		if ($raw === '') {
			return null;
		}

		$payload = json_decode($raw, true);

		if (!is_array($payload) || !isset($payload['rates'], $payload['updated_at'])) {
			return null;
		}

		if (!is_array($payload['rates'])) {
			return null;
		}

		$updatedAt = (int) $payload['updated_at'];

		if ($updatedAt <= 0 || (time() - $updatedAt) > self::CACHE_TTL) {
			return null;
		}

		$rates = [];

		foreach ($payload['rates'] as $code => $rate) {
			$code = strtolower(trim((string) $code));

			if ($code === '' || $code === 'try') {
				continue;
			}

			$rates[$code] = (float) $rate;
		}

		return $rates !== [] ? $rates : null;
	}

	/** @param array<string, float> $rates */
	private static function writeCache(array $rates): void
	{
		Settings::set(self::CACHE_KEY, json_encode([
			'updated_at' => time(),
			'rates' => $rates,
		], JSON_UNESCAPED_UNICODE));
	}

	/** @return array<string, float> */
	private static function fetchLiveRates(): array
	{
		$urls = [
			'https://api.bigpara.hurriyet.com.tr/doviz/headerlist/anasayfa',
			'http://api.bigpara.hurriyet.com.tr/doviz/headerlist/anasayfa',
		];

		$context = stream_context_create(['http' => ['timeout' => 8]]);
		$json = false;

		foreach ($urls as $url) {
			$json = @file_get_contents($url, false, $context);

			if ($json !== false && $json !== '') {
				break;
			}
		}

		if ($json === false || $json === '') {
			return self::readStaleCache();
		}

		$payload = json_decode($json);

		if (!isset($payload->data) || !is_array($payload->data)) {
			return self::readStaleCache();
		}

		$symbolToCode = array_flip(self::SYMBOL_MAP);
		$rates = [];

		foreach ($payload->data as $item) {
			$symbol = (string) ($item->SEMBOL ?? '');

			if ($symbol === '' || !isset($symbolToCode[$symbol])) {
				continue;
			}

			$rate = (float) ($item->ALIS ?? 0);

			if ($rate > 0) {
				$rates[$symbolToCode[$symbol]] = $rate;
			}
		}

		return $rates;
	}

	/** @return array<string, float> */
	private static function readStaleCache(): array
	{
		$raw = trim((string) Settings::get(self::CACHE_KEY));

		if ($raw === '') {
			return [];
		}

		$payload = json_decode($raw, true);

		if (!is_array($payload) || !is_array($payload['rates'] ?? null)) {
			return [];
		}

		$rates = [];

		foreach ($payload['rates'] as $code => $rate) {
			$code = strtolower(trim((string) $code));

			if ($code !== '') {
				$rates[$code] = (float) $rate;
			}
		}

		return $rates;
	}
}
