<?php

/**
 * FShop Web API test paneli (yerel geliştirme).
 * Kullanım: http://localhost/fshop/tools/api-test.php
 * API anahtarını Admin → Ayarlar → Web API bölümünden alın.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseUrl = 'http://localhost/fshop/api/v1';
$apiKey  = ''; // Admin panelden kopyalayın
$result  = '';

function apiRequest($method, $url, $apiKey, $data = null)
{
	$ch = curl_init();

	$headers = [
		'X-API-Key: ' . trim($apiKey),
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
		CURLOPT_SSL_VERIFYPEER => false,
	]);

	if ($data !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);

	curl_close($ch);

	return [
		'http_code' => $httpCode,
		'curl_error' => $error,
		'response' => json_decode($response, true) ?: $response,
	];
}

function apiUploadImage($url, $apiKey, $filePath)
{
	$ch = curl_init();

	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [
			'X-API-Key: ' . trim($apiKey),
			'Accept: application/json',
		],
		CURLOPT_POSTFIELDS => [
			'image' => new CURLFile($filePath, mime_content_type($filePath) ?: 'image/jpeg', basename($filePath)),
		],
		CURLOPT_SSL_VERIFYPEER => false,
	]);

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	curl_close($ch);

	return [
		'http_code' => $httpCode,
		'response' => json_decode($response, true) ?: $response,
	];
}

if (isset($_POST['action'])) {
	$active = isset($_POST['active']) ? (int) $_POST['active'] : 1;

	switch ($_POST['action']) {
		case 'categories':
			$result = apiRequest('GET', $baseUrl . '/categories', $apiKey);
			break;

		case 'brands':
			$result = apiRequest('GET', $baseUrl . '/brands', $apiKey);
			break;

		case 'products':
			$result = apiRequest('GET', $baseUrl . '/products?page=1&limit=20', $apiKey);
			break;

		case 'orders':
			$result = apiRequest('GET', $baseUrl . '/orders?page=0&size=20', $apiKey);
			break;

		case 'product_detail':
			$result = apiRequest('GET', $baseUrl . '/products/' . (int) $_POST['product_id'], $apiKey);
			break;

		case 'order_detail':
			$result = apiRequest('GET', $baseUrl . '/orders/' . (int) $_POST['order_id'], $apiKey);
			break;

		case 'add_product':
			$payload = [
				'name' => $_POST['name'],
				'category' => $_POST['category'],
				'brand' => $_POST['brand'],
				'price' => (float) str_replace(',', '.', $_POST['price']),
				'stock' => (int) $_POST['stock'],
				'active' => $active,
				'barcode' => $_POST['barcode'],
				'stock_code' => $_POST['stock_code'],
			];
			if (trim($_POST['description_html'] ?? '') !== '') {
				$payload['description_html'] = $_POST['description_html'];
			}
			$result = apiRequest('POST', $baseUrl . '/products', $apiKey, $payload);
			break;

		case 'update_product':
			$payload = [
				'name' => $_POST['name'],
				'category' => $_POST['category'],
				'brand' => $_POST['brand'],
				'price' => (float) str_replace(',', '.', $_POST['price']),
				'stock' => (int) $_POST['stock'],
				'active' => $active,
				'barcode' => $_POST['barcode'],
				'stock_code' => $_POST['stock_code'],
			];
			if (trim($_POST['description_html'] ?? '') !== '') {
				$payload['description_html'] = $_POST['description_html'];
			}
			$result = apiRequest('PATCH', $baseUrl . '/products/' . (int) $_POST['product_id'], $apiKey, $payload);
			break;

		case 'quick_update':
			$payload = [];
			if ($_POST['price'] !== '') {
				$payload['price'] = (float) str_replace(',', '.', $_POST['price']);
			}
			if ($_POST['stock'] !== '') {
				$payload['stock'] = (int) $_POST['stock'];
			}
			if (isset($_POST['quick_active'])) {
				$payload['active'] = (int) $_POST['quick_active'];
			}
			$result = apiRequest('PATCH', $baseUrl . '/products/' . (int) $_POST['product_id'] . '/quick', $apiKey, $payload);
			break;

		case 'delete_product':
			$result = apiRequest('DELETE', $baseUrl . '/products/' . (int) $_POST['product_id'], $apiKey);
			break;

		case 'upload_image':
			$id = (int) $_POST['product_id'];

			if ($id <= 0) {
				$result = ['http_code' => 0, 'response' => 'Ürün ID girin'];
			} elseif (!empty($_FILES['image']['tmp_name'])) {
				$result = apiUploadImage($baseUrl . '/products/' . $id . '/image', $apiKey, $_FILES['image']['tmp_name']);
			} elseif (trim($_POST['image_url'] ?? '') !== '') {
				$result = apiRequest('POST', $baseUrl . '/products/' . $id . '/image', $apiKey, [
					'image_url' => trim($_POST['image_url']),
				]);
			} else {
				$result = ['http_code' => 0, 'response' => 'Görsel dosyası veya image_url girin'];
			}
			break;

		case 'update_order':
			$payload = [];

			if ((int) ($_POST['status'] ?? 0) > 0) {
				$payload['status'] = (int) $_POST['status'];
			}
			if (trim($_POST['cargo_company'] ?? '') !== '') {
				$payload['cargoCompany'] = trim($_POST['cargo_company']);
			}
			if (trim($_POST['tracking_number'] ?? '') !== '') {
				$payload['trackingNumber'] = trim($_POST['tracking_number']);
			}

			$result = apiRequest('PATCH', $baseUrl . '/orders/' . (int) $_POST['order_id'], $apiKey, $payload);
			break;
	}
}

?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>FShop API Test</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
input, select { padding: 8px; width: 100%; margin-bottom: 8px; box-sizing: border-box; }
button { padding: 10px 15px; margin: 0 4px 8px 0; cursor: pointer; }
pre { background: #111; color: #0f0; padding: 15px; overflow: auto; font-size: 13px; }
.row { display: flex; gap: 16px; flex-wrap: wrap; }
.col { flex: 1; min-width: 280px; border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
.hint { font-size: 12px; color: #666; margin-bottom: 12px; }
</style>
</head>
<body>

<h2>FShop API Test Paneli</h2>
<p class="hint">Dosyadaki <code>$apiKey</code> değişkenine Admin → Ayarlar → Web API anahtarını yazın.</p>

<form method="post" enctype="multipart/form-data">

<div class="row">

<div class="col">
<h3>Listeleme</h3>
<button name="action" value="categories" type="submit">Kategoriler</button>
<button name="action" value="brands" type="submit">Markalar</button>
<button name="action" value="products" type="submit">Ürünler</button>
<button name="action" value="orders" type="submit">Siparişler</button>
</div>

<div class="col">
<h3>Ürün İşlemleri</h3>

<input type="number" name="product_id" placeholder="Ürün ID" value="<?= (int) ($_POST['product_id'] ?? 0) ?: '' ?>">
<input type="text" name="name" placeholder="Ürün Adı" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="category" placeholder="Kategori" value="<?= htmlspecialchars($_POST['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="brand" placeholder="Marka" value="<?= htmlspecialchars($_POST['brand'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="barcode" placeholder="Barkod" value="<?= htmlspecialchars($_POST['barcode'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="stock_code" placeholder="Stok Kodu" value="<?= htmlspecialchars($_POST['stock_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="price" placeholder="Fiyat (ör. 199.90)" value="<?= htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="number" name="stock" placeholder="Stok" value="<?= htmlspecialchars($_POST['stock'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

<textarea name="description_html" rows="4" placeholder="HTML açıklama (ör. &lt;p&gt;Detay&lt;/p&gt;)"><?= htmlspecialchars($_POST['description_html'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

<select name="active">
<option value="1" <?= (($_POST['active'] ?? '1') === '1') ? 'selected' : '' ?>>Aktif</option>
<option value="0" <?= (($_POST['active'] ?? '') === '0') ? 'selected' : '' ?>>Pasif</option>
</select>

<button name="action" value="product_detail" type="submit">Ürün Getir</button>
<button name="action" value="add_product" type="submit">Ürün Ekle</button>
<button name="action" value="update_product" type="submit">Ürün Güncelle</button>
<button name="action" value="delete_product" type="submit">Ürün Sil</button>

<hr>
<h4>Hızlı Güncelleme (fiyat / stok / durum)</h4>
<p class="hint">PATCH /api/v1/products/{id}/quick — sadece değiştirmek istediğin alanları doldur.</p>
<select name="quick_active">
<option value="">— durum değiştirme —</option>
<option value="1">Aktif</option>
<option value="0">Pasif</option>
</select>
<button name="action" value="quick_update" type="submit">Hızlı Güncelle</button>

<hr>
<h4>Görsel Yükle</h4>
<input type="text" name="image_url" placeholder="Görsel URL (https://...)" value="<?= htmlspecialchars($_POST['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="file" name="image" accept="image/jpeg,image/png,image/webp">
<button name="action" value="upload_image" type="submit">Görsel Gönder</button>
<p class="hint">POST /api/v1/products/{id}/image — dosya, image_url veya base64.</p>
</div>

<div class="col">
<h3>Sipariş İşlemleri</h3>

<input type="number" name="order_id" placeholder="Sipariş ID" value="<?= (int) ($_POST['order_id'] ?? 0) ?: '' ?>">

<select name="status">
<option value="0">— durum değiştirme —</option>
<option value="1">Ödeme Bekliyor</option>
<option value="2">Hazırlanıyor</option>
<option value="3">Kargoda</option>
<option value="4">Teslim Edildi</option>
<option value="5">İptal Edildi</option>
</select>

<input type="text" name="cargo_company" placeholder="Kargo firması" value="<?= htmlspecialchars($_POST['cargo_company'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<input type="text" name="tracking_number" placeholder="Takip numarası" value="<?= htmlspecialchars($_POST['tracking_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

<button name="action" value="order_detail" type="submit">Sipariş Getir</button>
<button name="action" value="update_order" type="submit">Sipariş Güncelle</button>
</div>

</div>

</form>

<?php if ($result) { ?>
<h3>API Cevabı</h3>
<pre><?= htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?></pre>
<?php } ?>

</body>
</html>
