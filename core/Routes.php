<?php

class Routes
{
	/** Eski Türkçe slug → yeni İngilizce slug */
	public static function legacyMap(): array
	{
		return [
			'hesabim' => 'my-account',
			'siparislerim' => 'my-account',
			'siparis' => 'my-account',
			'iadeler' => 'returns',
			'iade-talebi' => 'return-request',
			'iptal-talebi' => 'cancel-request',
			'favoriler' => 'favorites',
			'sifremi-unuttum' => 'forgot-password',
			'sifre-sifirla' => 'reset-password',
			'odeme-paytr' => 'paytr-payment',
			'odeme-nkolaypay' => 'nkolaypay-payment',
			'odeme-esnekpos' => 'esnekpos-payment',
			'odeme-parampos' => 'parampos-payment',
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
			'returns',
			'return-request',
			'cancel-request',
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

		if (isset($params['id']) && !isset($params['order']) && in_array($newSlug, ['my-account', 'order'], true)) {
			$params['order'] = $params['id'];
			unset($params['id']);
		}

		$url = rtrim($domain, '/') . '/' . $newSlug;

		if ($params !== []) {
			$url .= '?' . http_build_query($params);
		}

		header('Location: ' . $url, true, 301);
		exit;
	}
}
