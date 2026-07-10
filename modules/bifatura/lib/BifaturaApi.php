<?php

namespace Bifatura;

/**
 * BirFatura / e-Dönüştür OutEBelgeV2 API
 * https://uygulama.edonustur.com/api/OutEBelgeV2/
 */
class BifaturaApi
{
	private string $apiKey;
	private string $secretKey;
	private string $integrationKey;
	private string $baseUri;

	public const DEFAULT_BASE_URI = 'https://uygulama.edonustur.com/api/OutEBelgeV2/';

	/** @var array{url?: string, http_code?: int, body?: string, error?: string, content_type?: string} */
	private static array $lastDebug = [];

	public function __construct(string $apiKey, string $secretKey, string $integrationKey, string $baseUri = '')
	{
		$this->apiKey = $apiKey;
		$this->secretKey = $secretKey;
		$this->integrationKey = $integrationKey;
		$uri = $baseUri !== '' ? $baseUri : self::DEFAULT_BASE_URI;
		$this->baseUri = rtrim($uri, '/') . '/';
	}

	/** @return array{url?: string, http_code?: int, body?: string, error?: string, content_type?: string} */
	public static function getLastDebug(): array
	{
		return self::$lastDebug;
	}

	public static function fromSettings(): ?self
	{
		$apiKey = trim((string) \Settings::get('BIFATURA_API_KEY'));
		$scKey = trim((string) \Settings::get('BIFATURA_SC_KEY'));
		$inKey = trim((string) \Settings::get('BIFATURA_IN_KEY'));
		$baseUri = trim((string) \Settings::get('BIFATURA_API_URL'));

		// Eski yanlış varsayılanı yok say
		if ($baseUri === '' || stripos($baseUri, 'api.bifatura.com') !== false) {
			$baseUri = self::DEFAULT_BASE_URI;
		}

		if ($apiKey === '' || $scKey === '' || $inKey === '') {
			return null;
		}

		return new self($apiKey, $scKey, $inKey, $baseUri);
	}

	/** 10 haneli VKN → EFATURA, 11 haneli TCKN → EARSIV */
	public static function resolveSystemType(string $taxNo): string
	{
		$digits = preg_replace('/\D/', '', $taxNo);

		return strlen($digits) === 10 ? 'EFATURA' : 'EARSIV';
	}

	/** @param array<string, mixed> $invoice */
	public function sendBasicInvoice(array $invoice): ?array
	{
		return $this->request('SendBasicInvoiceFromModel', ['invoice' => $invoice]);
	}

	public function getPdfLinkByUuid(array $uuids, string $systemType = 'EARSIV'): ?array
	{
		return $this->request('GetPDFLinkByUUID', [
			'uuids' => array_values($uuids),
			'systemType' => $systemType,
		]);
	}

	public function getInBoxDocuments(
		string $systemType,
		string $startDateTime,
		string $endDateTime,
		int $pageNumber = 0,
		string $documentType = 'INVOICE',
		string $readUnReadStatus = 'ALL'
	): ?array {
		return $this->request('GetInBoxDocuments', [
			'systemType' => $systemType,
			'startDateTime' => $startDateTime,
			'endDateTime' => $endDateTime,
			'documentType' => $documentType,
			'readUnReadStatus' => $readUnReadStatus,
			'pageNumber' => (int) $pageNumber,
		]);
	}

	public function getOutBoxDocuments(
		string $systemType,
		string $startDateTime,
		string $endDateTime,
		int $pageNumber = 0,
		string $documentType = 'INVOICE',
		string $readUnReadStatus = 'ALL'
	): ?array {
		return $this->request('GetOutBoxDocuments', [
			'systemType' => $systemType,
			'startDateTime' => $startDateTime,
			'endDateTime' => $endDateTime,
			'documentType' => $documentType,
			'readUnReadStatus' => $readUnReadStatus,
			'pageNumber' => (int) $pageNumber,
		]);
	}

	public function getInBoxDocumentByUuid(string $uuid, string $systemType = 'EFATURA'): ?array
	{
		return $this->request('GetInBoxDocumentByUUID', [
			'uuid' => $uuid,
			'systemType' => $systemType,
		]);
	}

	/** @param array<string, mixed> $data */
	private function request(string $endpoint, array $data = []): ?array
	{
		self::$lastDebug = [];

		if (!function_exists('curl_init')) {
			return ['Success' => false, 'Message' => 'cURL eklentisi yok'];
		}

		$url = $this->baseUri . $endpoint;
		$payload = json_encode($data, JSON_UNESCAPED_UNICODE);

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $payload,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Accept: application/json',
				'X-Api-Key: ' . $this->apiKey,
				'X-Secret-Key: ' . $this->secretKey,
				'X-Integration-Key: ' . $this->integrationKey,
			],
			CURLOPT_TIMEOUT => 60,
		]);

		$result = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		$bodyPreview = is_string($result) ? substr($result, 0, 500) : '';
		self::$lastDebug = [
			'url' => $url,
			'http_code' => $httpCode,
			'content_type' => $contentType,
			'body' => $bodyPreview,
			'error' => $error,
		];

		if ($errno || $error !== '') {
			error_log('Bifatura curl: ' . $error . ' url=' . $url);

			return ['Success' => false, 'Message' => 'Bağlantı hatası: ' . $error . ' | URL: ' . $url];
		}

		if (!is_string($result) || $result === '') {
			return [
				'Success' => false,
				'Message' => 'Boş yanıt (HTTP ' . $httpCode . ') | URL: ' . $url,
			];
		}

		$data = json_decode($result, true);

		if (!is_array($data)) {
			return [
				'Success' => false,
				'Message' => 'HTTP ' . $httpCode . ' — geçersiz yanıt | URL: ' . $url
					. ' | Yanıt: ' . self::safePreview($bodyPreview),
			];
		}

		if (!array_key_exists('Success', $data) && array_key_exists('success', $data)) {
			$data['Success'] = $data['success'];
		}
		if (!array_key_exists('Message', $data) && array_key_exists('message', $data)) {
			$data['Message'] = $data['message'];
		}
		if (!array_key_exists('Result', $data) && array_key_exists('result', $data)) {
			$data['Result'] = $data['result'];
		}

		if ($httpCode >= 200 && $httpCode < 300) {
			return $data;
		}

		$data['Success'] = $data['Success'] ?? false;

		if (empty($data['Message'])) {
			$data['Message'] = 'HTTP ' . $httpCode . ' | URL: ' . $url;
		}

		return $data;
	}

	private static function safePreview(string $body): string
	{
		$body = trim(preg_replace('/\s+/', ' ', strip_tags($body)));

		if ($body === '') {
			return '(boş)';
		}

		return mb_substr($body, 0, 180);
	}
}
