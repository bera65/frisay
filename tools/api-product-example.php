<?php

/**
 * FShop Web API — çok dilli ürün ekleme + görsel yükleme örneği.
 *
 * Kullanım:
 *   php tools/api-product-example.php
 *
 * Ortam değişkenleri (isteğe bağlı):
 *   FSHOP_API_URL  — varsayılan: http://localhost/fshop/api/v1
 *   FSHOP_API_KEY  — Admin → Ayarlar → Web API anahtarı
 */

$baseUrl = rtrim((string) (getenv('FSHOP_API_URL') ?: 'http://localhost/fshop/api/v1'), '/');
$apiKey = trim((string) (getenv('FSHOP_API_KEY') ?: 'f25851a5e2cd17667780e12369317457b5ef1b503ed24aa3ca178e6b738d6d32'));

// Görsel yükleme (opsiyonel) — dosya yolu veya URL; boş bırakılırsa atlanır
$imageFilePath = ''; // örn: __DIR__ . '/sample-product.jpg'
$imageUrl = '';      // örn: 'https://example.com/urun.jpg'

function apiRequest(string $method, string $url, string $apiKey, ?array $data = null): array
{
	if ($apiKey === '') {
		return [
			'http_code' => 0,
			'curl_error' => '',
			'response' => ['success' => false, 'message' => 'API anahtarı boş. Admin panelden kopyalayın.'],
		];
	}

	$ch = curl_init();

	$headers = [
		'X-API-Key: ' . $apiKey,
		'Accept: application/json',
	];

	if ($data !== null) {
		$headers[] = 'Content-Type: application/json';
	}

	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_CONNECTTIMEOUT => 15,
		CURLOPT_TIMEOUT => 60,
		CURLOPT_SSL_VERIFYPEER => true,
	]);

	if ($data !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	$response = curl_exec($ch);
	$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);

	curl_close($ch);

	$decoded = json_decode((string) $response, true);

	return [
		'http_code' => $httpCode,
		'curl_error' => $error,
		'response' => is_array($decoded) ? $decoded : $response,
	];
}

function apiUploadImage(string $url, string $apiKey, string $filePath): array
{
	if ($apiKey === '') {
		return [
			'http_code' => 0,
			'response' => ['success' => false, 'message' => 'API anahtarı boş'],
		];
	}

	if (!is_file($filePath)) {
		return [
			'http_code' => 0,
			'response' => ['success' => false, 'message' => 'Görsel dosyası bulunamadı: ' . $filePath],
		];
	}

	$ch = curl_init();

	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [
			'X-API-Key: ' . $apiKey,
			'Accept: application/json',
		],
		CURLOPT_POSTFIELDS => [
			'image' => new CURLFile(
				$filePath,
				mime_content_type($filePath) ?: 'image/jpeg',
				basename($filePath)
			),
		],
		CURLOPT_CONNECTTIMEOUT => 15,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_SSL_VERIFYPEER => true,
	]);

	$response = curl_exec($ch);
	$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);

	curl_close($ch);

	$decoded = json_decode((string) $response, true);

	return [
		'http_code' => $httpCode,
		'curl_error' => $error,
		'response' => is_array($decoded) ? $decoded : $response,
	];
}

function extractProductId(array $result): int
{
	$response = $result['response'] ?? null;

	if (!is_array($response)) {
		return 0;
	}

	return (int) ($response['data']['id'] ?? 0);
}

function printApiResult(string $title, array $result): void
{
	echo "\n=== {$title} ===\n";
	echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

$suffix = (string) time();

$payload = [
	'translations' => [
		'tr' => [
			'name' => 'Pamuklu Tişört',
			'slug' => 'pamuklu-tisort-' . $suffix,
			'description_html' => '<p>Yumuşak kumaş</p>',
		],
		'en' => [
			'name' => 'Cotton T-Shirt',
			'slug' => 'cotton-t-shirt-' . $suffix,
			'description_html' => '<p>Soft fabric</p>',
		],
	],
	'category' => 'Test Category',
	'brand' => 'Test Brand',
	'cost' => 50,
	'price' => 199.99,
	'stock' => 15,
	'active' => 1,
	'barcode' => '86991233322' . substr($suffix, -4),
	'stock_code' => 'fs123-' . $suffix,
	'variations' => [
		[
			'sku' => 'TSH-RED-M',
			'barcode' => '8690000001001',
			'options' => [
				'Color' => 'Red',
				'Size' => 'M',
			],
			'price' => 249.90,
			'stock' => 12,
			'active' => true,
		],
	],
];

$createResult = apiRequest('POST', $baseUrl . '/products', $apiKey, $payload);
printApiResult('Ürün oluşturma', $createResult);

$productId = extractProductId($createResult);

if ($productId <= 0) {
	echo "\nÜrün ID alınamadı; görsel yükleme atlandı.\n";
	exit(1);
}

echo "\nOluşturulan ürün ID: {$productId}\n";

if ($imageFilePath !== '') {
	$imageResult = apiUploadImage($baseUrl . '/products/' . $productId . '/image', $apiKey, $imageFilePath);
	printApiResult('Görsel yükleme (dosya)', $imageResult);
} elseif ($imageUrl !== '') {
	$imageResult = apiRequest('POST', $baseUrl . '/products/' . $productId . '/image', $apiKey, [
		'image_url' => $imageUrl,
	]);
	printApiResult('Görsel yükleme (URL)', $imageResult);
} else {
	echo "\nGörsel yükleme atlandı. \$imageFilePath veya \$imageUrl tanımlayın.\n";
}
