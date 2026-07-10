<?php

namespace Shopier;

class ShopierApi
{
	private const BASE_URL = 'https://api.shopier.com/v1/';

	private string $token;

	public function __construct(string $token)
	{
		$this->token = trim($token);
	}

	public function isConfigured(): bool
	{
		return $this->token !== '';
	}

	/**
	 * @param array<string, scalar|array|null> $query
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function listProducts(array $query = []): array
	{
		return $this->request('GET', 'products', null, $query);
	}

	/**
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function getProduct(string $id): array
	{
		return $this->request('GET', 'products/' . rawurlencode($id));
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function createProduct(array $payload): array
	{
		return $this->request('POST', 'products', $payload);
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function updateProduct(string $id, array $payload): array
	{
		return $this->request('PUT', 'products/' . rawurlencode($id), $payload);
	}

	/**
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function deleteProduct(string $id): array
	{
		return $this->request('DELETE', 'products/' . rawurlencode($id));
	}

	/**
	 * @param array<string, scalar|array|null> $query
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	public function listCategories(array $query = []): array
	{
		return $this->request('GET', 'categories', null, $query);
	}

	/**
	 * @param array<string, mixed>|null $body
	 * @param array<string, scalar|array|null> $query
	 * @return array{ok: bool, message: string, data?: mixed, http_code?: int}
	 */
	private function request(string $method, string $path, ?array $body = null, array $query = []): array
	{
		if (!$this->isConfigured()) {
			return ['ok' => false, 'message' => 'Shopier API anahtarı tanımlı değil'];
		}

		if (!function_exists('curl_init')) {
			return ['ok' => false, 'message' => 'cURL eklentisi yok'];
		}

		$url = self::BASE_URL . ltrim($path, '/');

		if ($query !== []) {
			$url .= '?' . http_build_query($query);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->token,
			'Accept: application/json',
			'Content-Type: application/json',
		]);

		if ($body !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}

		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno || $error !== '') {
			return ['ok' => false, 'message' => 'Bağlantı hatası: ' . $error, 'http_code' => $httpCode];
		}

		$response = is_string($response) ? trim($response) : '';

		if ($response === '') {
			if ($httpCode >= 200 && $httpCode < 300) {
				return ['ok' => true, 'message' => 'İşlem başarılı', 'data' => null, 'http_code' => $httpCode];
			}

			return ['ok' => false, 'message' => 'Boş yanıt (HTTP ' . $httpCode . ')', 'http_code' => $httpCode];
		}

		$decoded = json_decode($response, true);

		if (!is_array($decoded)) {
			return ['ok' => false, 'message' => 'HTTP ' . $httpCode . ' — geçersiz JSON yanıt', 'http_code' => $httpCode];
		}

		if ($httpCode >= 400) {
			$msg = self::extractErrorMessage($decoded, $httpCode);

			return ['ok' => false, 'message' => $msg, 'data' => $decoded, 'http_code' => $httpCode];
		}

		return ['ok' => true, 'message' => 'İşlem başarılı', 'data' => $decoded, 'http_code' => $httpCode];
	}

	/** @param array<string, mixed> $decoded */
	private static function extractErrorMessage(array $decoded, int $httpCode): string
	{
		foreach (['message', 'Message', 'error', 'msg', 'detail'] as $key) {
			if (!empty($decoded[$key]) && is_string($decoded[$key])) {
				return $decoded[$key];
			}
		}

		if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
			$parts = [];

			foreach ($decoded['errors'] as $err) {
				if (is_string($err)) {
					$parts[] = $err;
				} elseif (is_array($err)) {
					$parts[] = (string) ($err['message'] ?? $err['title'] ?? json_encode($err, JSON_UNESCAPED_UNICODE));
				}
			}

			if ($parts !== []) {
				return implode('; ', $parts);
			}
		}

		return 'HTTP ' . $httpCode;
	}
}
