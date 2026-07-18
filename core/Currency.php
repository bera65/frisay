<?php

class Currency
{
	private static bool $defaultsReady = false;

	private const BUILTIN = [
		'try' => ['label' => 'Türk Lirası', 'symbol' => '₺'],
		'usd' => ['label' => 'Amerikan Doları', 'symbol' => '$'],
		'eur' => ['label' => 'Euro', 'symbol' => '€'],
		'gbp' => ['label' => 'İngiliz Sterlini', 'symbol' => '£'],
		'chf' => ['label' => 'İsviçre Frangı', 'symbol' => 'CHF'],
		'rub' => ['label' => 'Rus Rublesi', 'symbol' => '₽'],
		'aed' => ['label' => 'BAE Dirhemi', 'symbol' => 'د.إ'],
		'sar' => ['label' => 'Suudi Riyali', 'symbol' => '﷼'],
	];

	public static function ensureDefaults(): void
	{
		if (self::$defaultsReady) {
			return;
		}

		self::$defaultsReady = true;

		$list = trim((string) Settings::get('SHOP_CURRENCIES'));

		if ($list === '') {
			Settings::set('SHOP_CURRENCIES', 'try,usd,eur');
		}

		$meta = trim((string) Settings::get('CURRENCY_META'));

		if ($meta === '') {
			Settings::set('CURRENCY_META', json_encode(self::defaultMeta(), JSON_UNESCAPED_UNICODE));
		}

		$codes = self::parseCurrencyList(trim((string) Settings::get('SHOP_CURRENCIES')));
		$shop = strtolower(trim((string) Settings::get('SHOP_CURRENCY')));

		if ($shop === '' || !in_array($shop, $codes, true)) {
			Settings::set('SHOP_CURRENCY', $codes[0] ?? 'try');
		}
	}

	/** @return string[] */
	private static function parseCurrencyList(string $configured): array
	{
		$codes = [];

		if ($configured !== '') {
			foreach (explode(',', $configured) as $code) {
				$code = strtolower(trim($code));
				if (self::isValid($code)) {
					$codes[] = $code;
				}
			}
		}

		if ($codes === []) {
			$codes = ['try'];
		}

		return array_values(array_unique($codes));
	}

	/** @return string[] */
	public static function getAvailable(): array
	{
		self::ensureDefaults();

		return self::parseCurrencyList(trim((string) Settings::get('SHOP_CURRENCIES')));
	}

	public static function getShopCurrency(): string
	{
		$available = self::getAvailable();
		$code = strtolower(trim((string) Settings::get('SHOP_CURRENCY')));

		if (in_array($code, $available, true)) {
			return $code;
		}

		return $available[0];
	}

	public static function handleSwitchRequest(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			return;
		}

		if (!isset($_GET['set_currency'])) {
			return;
		}

		$code = strtolower(trim((string) $_GET['set_currency']));

		if ($code === '' || $code === 'reset') {
			unset($_SESSION['displayCurrency']);
		} elseif (in_array($code, self::getAvailable(), true)) {
			$_SESSION['displayCurrency'] = $code;
		}

		$redirect = trim((string) ($_GET['redirect'] ?? ''));

		if ($redirect === '') {
			$redirect = self::normalizeRedirectPath(null, true);
		} else {
			$redirect = self::normalizeRedirectPath($redirect, false);
		}

		header('Location: ' . self::getSiteBaseUrl() . $redirect);
		exit;
	}

	public static function getSiteBaseUrl(): string
	{
		self::ensureDefaults();

		$domain = rtrim((string) Settings::get('DOMAIN'), '/');
		$folder = rtrim((string) Settings::get('FOLDER'), '/');

		if ($folder === '' || $folder === '/') {
			return $domain;
		}

		$domainPath = parse_url($domain, PHP_URL_PATH);

		if (is_string($domainPath) && $domainPath !== '') {
			$domainPath = rtrim($domainPath, '/');

			if ($domainPath === $folder || substr($domainPath, -strlen($folder)) === $folder) {
				return $domain;
			}
		}

		return $domain . $folder;
	}

	public static function normalizeRedirectPath(?string $uri = null, bool $withQuery = true): string
	{
		if ($uri === null) {
			$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
		} else {
			$uri = trim($uri);
		}

		if ($uri === '') {
			return '/';
		}

		if (strpos($uri, '://') !== false || strncmp($uri, '//', 2) === 0) {
			return '/';
		}

		$path = (string) (parse_url($uri, PHP_URL_PATH) ?: '/');
		$folder = rtrim((string) Settings::get('FOLDER'), '/');

		if ($folder !== '' && $folder !== '/' && strpos($path, $folder) === 0) {
			$path = substr($path, strlen($folder)) ?: '/';
		}

		if ($path === '' || $path[0] !== '/') {
			$path = '/' . ltrim($path, '/');
		}

		if (!$withQuery) {
			return $path;
		}

		$query = parse_url($uri, PHP_URL_QUERY);

		if (!is_string($query) || $query === '') {
			return $path;
		}

		parse_str($query, $params);
		unset($params['set_currency'], $params['redirect'], $params['set_lang']);

		if ($params === []) {
			return $path;
		}

		return $path . '?' . http_build_query($params);
	}

	public static function buildSwitchUrl(string $code, ?string $redirect = null): string
	{
		$code = strtolower(trim($code));

		if ($redirect === null) {
			$redirect = self::normalizeRedirectPath(null, true);
		} else {
			$redirect = self::normalizeRedirectPath($redirect, false);
		}

		return self::getSiteBaseUrl() . '/?' . http_build_query([
			'set_currency' => $code,
			'redirect' => $redirect,
		]);
	}

	public static function getDisplayCurrency(): string
	{
		self::ensureDefaults();

		$display = strtolower(trim((string) ($_SESSION['displayCurrency'] ?? '')));

		if ($display !== '' && in_array($display, self::getAvailable(), true)) {
			return $display;
		}

		return self::getShopCurrency();
	}

	public static function isDisplayCurrencyActive(): bool
	{
		return self::getDisplayCurrency() !== self::getShopCurrency();
	}

	/** @return array<string, array{label: string, symbol: string}> */
	public static function getMetaMap(): array
	{
		self::ensureDefaults();

		$raw = trim((string) Settings::get('CURRENCY_META'));
		$decoded = $raw !== '' ? json_decode($raw, true) : [];

		if (!is_array($decoded)) {
			$decoded = [];
		}

		$map = [];

		foreach (self::getAvailable() as $code) {
			$entry = is_array($decoded[$code] ?? null) ? $decoded[$code] : [];
			$builtin = self::BUILTIN[$code] ?? [];

			$label = trim((string) ($entry['label'] ?? ''));
			$symbol = trim((string) ($entry['symbol'] ?? ''));

			if ($label === '') {
				$label = (string) ($builtin['label'] ?? strtoupper($code));
			}

			if ($symbol === '') {
				$symbol = (string) ($builtin['symbol'] ?? strtoupper($code));
			}

			$map[$code] = [
				'label' => $label,
				'symbol' => $symbol,
			];
		}

		return $map;
	}

	public static function symbol(?string $currency = null): string
	{
		$currency = strtolower(trim($currency ?: self::getShopCurrency()));
		$meta = self::getMetaMap();

		return $meta[$currency]['symbol'] ?? strtoupper($currency);
	}

	public static function label(?string $currency = null): string
	{
		$currency = strtolower(trim($currency ?: self::getShopCurrency()));
		$meta = self::getMetaMap();
		$name = $meta[$currency]['label'] ?? strtoupper($currency);

		return $name . ' (' . strtoupper($currency) . ')';
	}

	public static function shortLabel(?string $currency = null): string
	{
		$currency = strtolower(trim($currency ?: self::getShopCurrency()));
		$meta = self::getMetaMap();

		return $meta[$currency]['label'] ?? strtoupper($currency);
	}

	/** @return array<int, array{code: string, label: string, symbol: string, is_active: bool}> */
	public static function getOptions(): array
	{
		$active = self::getShopCurrency();
		$list = [];

		foreach (self::getMetaMap() as $code => $meta) {
			$list[] = [
				'code' => $code,
				'label' => $meta['label'] . ' (' . strtoupper($code) . ')',
				'symbol' => $meta['symbol'],
				'is_active' => $code === $active,
			];
		}

		return $list;
	}

	/** @return array<int, array{code: string, label: string, symbol: string, is_active: bool}> */
	public static function getAdminList(): array
	{
		$active = self::getShopCurrency();
		$list = [];

		foreach (self::getMetaMap() as $code => $meta) {
			$list[] = [
				'code' => $code,
				'label' => $meta['label'],
				'symbol' => $meta['symbol'],
				'is_active' => $code === $active,
			];
		}

		return $list;
	}

	public static function addCurrency(string $code, string $label = '', string $symbol = ''): array
	{
		$code = strtolower(trim($code));

		if (!self::isValid($code)) {
			return self::fail('Geçersiz para birimi kodu (ör. try, usd, gbp — 3 harf)');
		}

		$codes = self::getAvailable();

		if (in_array($code, $codes, true)) {
			return self::fail('Bu para birimi zaten tanımlı');
		}

		$codes[] = $code;
		self::persistCurrencies($codes);

		$meta = self::getMetaMap();
		$builtin = self::BUILTIN[$code] ?? [];

		$meta[$code] = [
			'label' => trim($label) !== '' ? trim($label) : (string) ($builtin['label'] ?? strtoupper($code)),
			'symbol' => trim($symbol) !== '' ? trim($symbol) : (string) ($builtin['symbol'] ?? strtoupper($code)),
		];

		self::persistMeta($meta);

		return self::ok('Para birimi eklendi');
	}

	public static function removeCurrency(string $code): array
	{
		$code = strtolower(trim($code));
		$codes = self::getAvailable();

		if (!in_array($code, $codes, true)) {
			return self::fail('Para birimi bulunamadı');
		}

		if (count($codes) <= 1) {
			return self::fail('Son para birimi silinemez');
		}

		if ($code === self::getShopCurrency()) {
			return self::fail('Aktif para birimini silmeden önce başka birini mağaza birimi yapın');
		}

		$codes = array_values(array_filter($codes, static fn(string $c): bool => $c !== $code));
		self::persistCurrencies($codes);

		$meta = self::getMetaMap();
		unset($meta[$code]);
		self::persistMeta($meta);

		return self::ok('Para birimi kaldırıldı');
	}

	public static function setShopCurrency(string $code): array
	{
		$code = strtolower(trim($code));

		if (!in_array($code, self::getAvailable(), true)) {
			return self::fail('Para birimi bulunamadı');
		}

		Settings::set('SHOP_CURRENCY', $code);

		return self::ok('Mağaza para birimi güncellendi');
	}

	public static function updateCurrency(string $code, string $label, string $symbol): array
	{
		$code = strtolower(trim($code));

		if (!in_array($code, self::getAvailable(), true)) {
			return self::fail('Para birimi bulunamadı');
		}

		$meta = self::getMetaMap();
		$meta[$code] = [
			'label' => trim($label) !== '' ? trim($label) : strtoupper($code),
			'symbol' => trim($symbol) !== '' ? trim($symbol) : strtoupper($code),
		];

		self::persistMeta($meta);

		return self::ok('Para birimi güncellendi');
	}

	public static function format(float $amount, ?string $lang = null, ?string $currency = null): string
	{
		$amount = max(0, $amount);
		$symbol = self::symbol($currency);
		$lang = self::resolveLang($lang);

		if ($lang === 'tr') {
			return $symbol . number_format($amount, 2, ',', '.');
		}

		return $symbol . number_format($amount, 2, '.', ',');
	}

	public static function formatFromShop(float $shopAmount, ?string $lang = null): string
	{
		$shopAmount = max(0, $shopAmount);
		$shopCurrency = self::getShopCurrency();
		$displayCurrency = self::getDisplayCurrency();

		if ($displayCurrency !== $shopCurrency) {
			if (!class_exists('ExchangeRate', false)) {
				require_once dirname(__DIR__) . '/core/ExchangeRate.php';
			}

			$shopAmount = ExchangeRate::fromTry($shopAmount, $displayCurrency);
		}

		return self::format($shopAmount, $lang, $displayCurrency);
	}

	private static function defaultMeta(): array
	{
		$meta = [];

		foreach (['try', 'usd', 'eur'] as $code) {
			$meta[$code] = self::BUILTIN[$code];
		}

		return $meta;
	}

	private static function persistCurrencies(array $codes): void
	{
		$codes = array_values(array_unique(array_filter($codes, static fn(string $c): bool => self::isValid($c))));
		Settings::set('SHOP_CURRENCIES', implode(',', $codes));
	}

	/** @param array<string, array{label: string, symbol: string}> $meta */
	private static function persistMeta(array $meta): void
	{
		Settings::set('CURRENCY_META', json_encode($meta, JSON_UNESCAPED_UNICODE));
	}

	private static function isValid(string $code): bool
	{
		return $code !== '' && preg_match('/^[a-z]{3}$/', $code) === 1;
	}

	private static function resolveLang(?string $lang): string
	{
		if ($lang !== null && $lang !== '') {
			return strtolower(trim($lang));
		}

		if (class_exists('Lang', false)) {
			return Lang::current();
		}

		$default = strtolower(trim((string) Settings::get('DEFAULT_LANG')));

		return $default !== '' ? $default : 'en';
	}

	private static function ok(string $message): array
	{
		return ['success' => true, 'message' => $message];
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message];
	}
}
