<?php

class AiAssistantClient
{
	public static function isConfigured(): bool
	{
		return trim((string) Settings::get('AI_ASSISTANT_API_KEY')) !== '';
	}

	public static function chat(string $systemPrompt, string $userPrompt, array $options = []): array
	{
		$apiKey = trim((string) Settings::get('AI_ASSISTANT_API_KEY'));

		if ($apiKey === '') {
			return [
				'success' => false,
				'message' => 'API anahtarı tanımlı değil. Modül ayarlarından ekleyin.',
			];
		}

		$baseUrl = rtrim((string) Settings::get('AI_ASSISTANT_BASE_URL'), '/');

		if ($baseUrl === '') {
			$baseUrl = 'https://api.openai.com/v1';
		}

		$model = trim((string) Settings::get('AI_ASSISTANT_MODEL'));

		if ($model === '') {
			$model = 'gpt-4o-mini';
		}

		$maxTokens = (int) ($options['max_tokens'] ?? 0);

		if ($maxTokens <= 0) {
			$maxTokens = (int) Settings::get('AI_ASSISTANT_MAX_TOKENS');
		}

		if ($maxTokens < 256) {
			$maxTokens = 1200;
		}

		if ($maxTokens > 4000) {
			$maxTokens = 4000;
		}

		$temperature = isset($options['temperature'])
			? (float) $options['temperature']
			: 0.4;

		$payload = [
			'model' => $model,
			'messages' => [
				['role' => 'system', 'content' => $systemPrompt],
				['role' => 'user', 'content' => $userPrompt],
			],
			'temperature' => $temperature,
			'max_tokens' => $maxTokens,
		];

		if (!empty($options['json'])) {
			$payload['response_format'] = ['type' => 'json_object'];
		}

		$url = $baseUrl . '/chat/completions';
		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $apiKey,
		];

		$provider = (string) Settings::get('AI_ASSISTANT_PROVIDER');

		if ($provider === 'openrouter') {
			$headers[] = 'HTTP-Referer: ' . rtrim((string) Settings::get('DOMAIN'), '/');
			$headers[] = 'X-Title: FShop AI Assistant';
		}

		$response = self::httpPostJson($url, $payload, $headers);

		if (!$response['success']) {
			return $response;
		}

		$body = $response['body'];
		$content = trim((string) ($body['choices'][0]['message']['content'] ?? ''));

		if ($content === '') {
			return [
				'success' => false,
				'message' => 'Yapay zeka boş yanıt döndürdü',
				'raw' => $body,
			];
		}

		return [
			'success' => true,
			'message' => 'Tamam',
			'content' => $content,
			'model' => (string) ($body['model'] ?? $model),
			'usage' => $body['usage'] ?? null,
			'raw' => $body,
		];
	}

	public static function improveProduct(array $fields, string $tone = 'professional', string $lang = 'tr'): array
	{
		$system = 'Sen deneyimli bir Türk e-ticaret SEO ve ürün editörüsün. '
			. 'Mağaza ürün metinlerini iyileştirirsin. Abartılı vaatlerden kaçın. '
			. 'Yanıtını yalnızca geçerli JSON olarak ver.';

		$user = [
			'task' => 'Ürün alanlarını iyileştir',
			'tone' => $tone,
			'language' => $lang,
			'fields' => [
				'product_name' => (string) ($fields['product_name'] ?? ''),
				'short_description' => (string) ($fields['short_description'] ?? ''),
				'description' => (string) ($fields['description'] ?? ''),
				'meta_title' => (string) ($fields['meta_title'] ?? ''),
				'meta_description' => (string) ($fields['meta_description'] ?? ''),
			],
			'instructions' => [
				'product_name: çekici, net, max 80 karakter',
				'short_description: 1-2 cümle, satış odaklı',
				'description: HTML paragraf kullanabilirsin (<p>), özellikler için madde işaretleri',
				'meta_title: SEO başlık, max 60 karakter',
				'meta_description: SEO açıklama, max 155 karakter',
				'Sadece verilen dili kullan',
				'JSON anahtarları: product_name, short_description, description, meta_title, meta_description, notes',
			],
		];

		$result = self::chat($system, json_encode($user, JSON_UNESCAPED_UNICODE), [
			'json' => true,
			'max_tokens' => 2000,
			'temperature' => 0.5,
		]);

		if (empty($result['success'])) {
			$retry = self::chat(
				$system . ' Yalnızca geçerli bir JSON nesnesi döndür.',
				json_encode($user, JSON_UNESCAPED_UNICODE),
				['json' => false, 'max_tokens' => 2000, 'temperature' => 0.5]
			);

			if (!empty($retry['success'])) {
				$result = $retry;
			} else {
				return $result;
			}
		}

		$decoded = self::decodeJsonContent((string) $result['content']);

		if ($decoded === null) {
			return [
				'success' => false,
				'message' => 'Yapay zeka yanıtı JSON olarak çözülemedi',
				'content' => $result['content'],
			];
		}

		return [
			'success' => true,
			'message' => 'Öneriler hazır',
			'suggestions' => [
				'product_name' => trim((string) ($decoded['product_name'] ?? '')),
				'short_description' => trim((string) ($decoded['short_description'] ?? '')),
				'description' => trim((string) ($decoded['description'] ?? '')),
				'meta_title' => trim((string) ($decoded['meta_title'] ?? '')),
				'meta_description' => trim((string) ($decoded['meta_description'] ?? '')),
			],
			'notes' => trim((string) ($decoded['notes'] ?? '')),
			'model' => $result['model'] ?? '',
		];
	}

	public static function analyzeDashboard(array $stats): array
	{
		$system = 'Sen bir e-ticaret analisti ve büyüme danışmanısın. '
			. 'Türkçe, net ve uygulanabilir öneriler ver. Markdown kullan (başlık, madde). '
			. 'Abartma; verilere dayan.';

		$user = "Aşağıdaki mağaza verilerini analiz et.\n"
			. "Şunları kapsasın:\n"
			. "1) Genel performans özeti\n"
			. "2) Satış ve ciro durumu\n"
			. "3) Çok satan / öne çıkan ürünler\n"
			. "4) Riskler (düşük stok, bekleyen siparişler vb.)\n"
			. "5) Öncelikli 5 aksiyon önerisi\n\n"
			. "VERİLER (JSON):\n"
			. json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

		$result = self::chat($system, $user, [
			'json' => false,
			'max_tokens' => 2200,
			'temperature' => 0.35,
		]);

		if (empty($result['success'])) {
			return $result;
		}

		return [
			'success' => true,
			'message' => 'Analiz hazır',
			'analysis' => (string) $result['content'],
			'model' => $result['model'] ?? '',
		];
	}

	public static function collectDashboardStats(): array
	{
		$cancelled = class_exists('Order') ? Order::STATUS_CANCELLED : 4;
		$base = Admin::getDashboardStats();
		$charts = Admin::getDashboardCharts();

		$topProducts = DB::execute(
			'SELECT od.product_name, od.id_product, SUM(od.qty) AS sold_qty,
				COALESCE(SUM(od.total), 0) AS sold_revenue
			 FROM order_detail od
			 INNER JOIN orders o ON o.id_order = od.id_order
			 WHERE o.status != ?
			   AND o.date_add >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			 GROUP BY od.id_product, od.product_name
			 ORDER BY sold_qty DESC
			 LIMIT 10',
			[$cancelled]
		) ?: [];

		$lowStock = DB::execute(
			'SELECT id_product, product_name, stock, price
			 FROM products
			 WHERE active = 1 AND stock <= 5
			 ORDER BY stock ASC, id_product DESC
			 LIMIT 10'
		) ?: [];

		$recentOrders = DB::execute(
			'SELECT reference, total, status, date_add
			 FROM orders
			 ORDER BY id_order DESC
			 LIMIT 8'
		) ?: [];

		return [
			'generated_at' => date('c'),
			'kpi' => [
				'orders_total' => $base['orders_total'] ?? 0,
				'orders_today' => $base['orders_today'] ?? 0,
				'orders_pending' => $base['orders_pending'] ?? 0,
				'orders_awaiting_shipment' => $base['orders_awaiting_shipment'] ?? 0,
				'products_total' => $base['products_total'] ?? 0,
				'products_low_stock' => $base['products_low_stock'] ?? 0,
				'users_total' => $base['users_total'] ?? 0,
				'users_today' => $base['users_today'] ?? 0,
				'revenue_today' => $base['revenue_today'] ?? 0,
				'revenue_yesterday' => $base['revenue_yesterday'] ?? 0,
				'revenue_month' => $base['revenue_month'] ?? 0,
				'revenue_total' => $base['revenue_total'] ?? 0,
			],
			'top_products_30d' => $topProducts,
			'low_stock_products' => $lowStock,
			'recent_orders' => $recentOrders,
			'daily_14d' => $charts['daily'] ?? [],
			'order_status_breakdown' => $charts['status'] ?? [],
		];
	}

	/** @return array<string, mixed>|null */
	private static function decodeJsonContent(string $content): ?array
	{
		$content = trim($content);

		if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $content, $m)) {
			$content = trim($m[1]);
		}

		$decoded = json_decode($content, true);

		if (is_array($decoded)) {
			return $decoded;
		}

		if (preg_match('/\{.*\}/s', $content, $m)) {
			$decoded = json_decode($m[0], true);

			if (is_array($decoded)) {
				return $decoded;
			}
		}

		return null;
	}

	/** @param array<int, string> $headers */
	private static function httpPostJson(string $url, array $payload, array $headers): array
	{
		$body = json_encode($payload, JSON_UNESCAPED_UNICODE);

		if ($body === false) {
			return ['success' => false, 'message' => 'İstek hazırlanamadı'];
		}

		if (!function_exists('curl_init')) {
			return ['success' => false, 'message' => 'cURL eklentisi gerekli'];
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $body,
			CURLOPT_TIMEOUT => 90,
			CURLOPT_CONNECTTIMEOUT => 15,
		]);

		$raw = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($errno) {
			return [
				'success' => false,
				'message' => 'Bağlantı hatası: ' . $error,
			];
		}

		$decoded = is_string($raw) ? json_decode($raw, true) : null;

		if ($status < 200 || $status >= 300) {
			$apiMessage = '';

			if (is_array($decoded)) {
				$apiMessage = (string) ($decoded['error']['message'] ?? $decoded['message'] ?? '');
			}

			return [
				'success' => false,
				'message' => $apiMessage !== ''
					? ('API hatası (' . $status . '): ' . $apiMessage)
					: ('API hatası (HTTP ' . $status . ')'),
				'raw' => $decoded,
			];
		}

		if (!is_array($decoded)) {
			return [
				'success' => false,
				'message' => 'Geçersiz API yanıtı',
			];
		}

		return [
			'success' => true,
			'body' => $decoded,
		];
	}
}
