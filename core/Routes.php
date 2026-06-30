<?php

class Routes
{
	/** Eski Türkçe slug → yeni İngilizce slug */
	public static function legacyMap(): array
	{
		return [
			'hesabim' => 'my-account',
			'siparislerim' => 'orders',
			'siparis' => 'order',
			'favoriler' => 'favorites',
			'sifremi-unuttum' => 'forgot-password',
			'sifre-sifirla' => 'reset-password',
			'odeme-paytr' => 'paytr-payment',
			'odeme-karti' => 'card-payment',
		];
	}

	public static function resolve(string $slug): string
	{
		$map = self::legacyMap();

		return $map[$slug] ?? $slug;
	}

	public static function protectedPages(): array
	{
		return [
			'my-account',
			'orders',
			'order',
			'favorites',
		];
	}

	public static function redirectLegacyIfNeeded(string $slug): void
	{
		$map = self::legacyMap();

		if (!isset($map[$slug])) {
			return;
		}

		global $domain;

		$newSlug = $map[$slug];
		$params = $_GET;
		unset($params['container']);

		$url = rtrim($domain, '/') . '/' . $newSlug;

		if ($params !== []) {
			$url .= '?' . http_build_query($params);
		}

		header('Location: ' . $url, true, 301);
		exit;
	}
}
