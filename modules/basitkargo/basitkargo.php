<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class BasitKargoModule extends ModuleBase
{
	public string $name = 'basitkargo';
	public string $title = 'Basit Kargo';
	public string $version = '1.0.0';
	public string $description = 'Basitkargo ile kargo gönder';
	public string $author = 'FShop';


	public function install(): bool
	{
		return $this->runSqlFile('install.sql');
	}

	public function uninstall(): bool
	{
		return $this->runSqlFile('uninstall.sql');
	}
	public array $displayHooks = [
        'admin_order_detail'   => 'Sipariş detayında ek panel',
    ];
    public array $defaultDisplayHooks = ['admin_order_detail'];
	
	public array $apiActions = [
        'webhook' => 'api/webhook.php'
    ];
	public function boot(): void
	{
	}
	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		$flash = '';
		$html = $this->renderFrontTemplate($hook, [
			'flash' => $flash,
		]);

		return $html !== '' ? $html : null;
	}
	public function adminPage(): void
	{
		global $smarty, $adminToken;
		$flash = '';
		$sonuc = '';
		if (Tools::isSubmit('testOrder'))
		{
			$postToken 	= (string) Tools::getValue('testOrder');

			if (hash_equals($adminToken, $postToken)) 
			{
				$siparisVerisi = [
					"type" => "OUTGOING",
					"content" => [
						"name" => "Test Sipariş",
						"code" => "#123456",
						"items" => [
							[ "name" => "Test Ürünü", "code" => "STK32", "quantity" => "1" ]
						],
						"packages" => [
							[ "height" => 10, "width" => 15, "depth" => 5, "weight" => 1 ]
						]
					],
					"client" => [
						"name" => "Test Alıcı",
						"phone" => "5555555555",
						"city" => "İstanbul",
						"town" => "Kadıköy",
						"address" => "Koşuyolu Mah."
					],
					"collect" => 100,
					"collectOnDeliveryType" => "CASH"
				];

				$sonuc = $this->basitKargoApi('order', 'POST', $siparisVerisi);
				if ($sonuc['http_code'] == 200)
					$flas = 'Test kargosu başarılı';
			}
			else
				$flash = 'Test sipariş oluşturulamadı';

		}
		if (Tools::isSubmit('saveKargo'))
		{
			$postToken 	= (string) Tools::getValue('saveKargo');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BASITKARGO_TOKEN', trim((string) Tools::getValue('basitKargoToken')));
				Settings::set('BASITKARGO_LINK_TOKEN', trim((string) Tools::getValue('basitLinkToken')));
				$flash = 'Kargo token bilgisi kayıt edildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}
		
		$smarty->assign([
			'basitKargoToken' 	=> Settings::get('BASITKARGO_TOKEN'),
			'basitLinkToken' 	=> Settings::get('BASITKARGO_LINK_TOKEN'),
			'flash' 			=> $flash,
			'sonuc' 			=> json_encode($sonuc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
		]);
	}
	public function basitKargoApi($action, $type = 'GET', $data = null)
	{
		$baseUrl = 'https://basitkargo.com/api'; 
		$url = $baseUrl;
		if ($action == 'order') {
			$url = $baseUrl . '/v2/order';
		}
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer '.Settings::get('BASITKARGO_TOKEN') 
		];
		if (strtoupper($type) === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			if ($data !== null) {
				$payload = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			}
		} 
		elseif (strtoupper($type) !== 'GET') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($type));
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			curl_close($ch);
			return [
				'success' => false,
				'message' => 'cURL Hatası: ' . $error_msg
			];
		}

		curl_close($ch);
		return [
			'http_code' => $httpCode,
			'response'  => json_decode($response, true)
		];
	}

}
