<?php
/**
 * Trendyol Satıcı API v1 - Ürün / Stok / Fiyat Güncelleme
 * Doküman: https://developers.trendyol.com
 */
class TrendyolApi
{
    private $supplierId;  
    private $apiKey;
    private $apiSecret;
    private $baseUri;

    public function __construct(string $supplierId, string $apiKey, string $apiSecret)
    {
        $this->supplierId = $supplierId;
        $this->apiKey     = $apiKey;
        $this->apiSecret  = $apiSecret;
        $this->baseUri    = 'https://apigw.trendyol.com/integration/';
    }

    public function getOrders()
    {
		$endpoint = "order/sellers/{$this->supplierId}/orders?status=Created,Picking,Invoiced,Shipped,Cancelled,Delivered,UnDelivered,Returned,AtCollectionPoint,UnPacked,UnSupplied&orderByField=PackageLastModifiedDate&orderByDirection=DESC&size=200";
        return $this->request('GET', $endpoint);
    }
	public function getOrderDetail($idOrder)
    {
        $endpoint = "order/sellers/{$this->supplierId}/orders?orderNumber=".(int)$idOrder;
        return $this->request('GET', $endpoint);
    }
	public function getReturnOrders($claimItemStatus = null, $page = 0, $size = 50, $startDate = null, $endDate = null)
    {
		$params = [
			'page' => (int)$page,
			'size' => min(200, max(1, (int)$size)),
		];
		if ($claimItemStatus) {
			$params['claimItemStatus'] = $claimItemStatus;
		}
		if ($startDate !== null) {
			$params['startDate'] = (int)$startDate;
		}
		if ($endDate !== null) {
			$params['endDate'] = (int)$endDate;
		}
        $endpoint = "order/sellers/{$this->supplierId}/claims?".http_build_query($params);
        return $this->request('GET', $endpoint);
    }
	public function getClaimById($claimId)
    {
        $endpoint = "order/sellers/{$this->supplierId}/claims?claimIds=".urlencode($claimId);
        return $this->request('GET', $endpoint);
    }
	public function approveClaimItems($claimId, array $claimLineItemIdList)
    {
        $endpoint = "order/sellers/{$this->supplierId}/claims/".urlencode($claimId)."/items/approve";
		$data = [
			'claimLineItemIdList' => array_values($claimLineItemIdList),
			'params'              => new stdClass(),
		];
        return $this->request('PUT', $endpoint, $data);
    }
	public function getOrderInf($idOrder, $startDate, $endDate)
	{
		date_default_timezone_set('Europe/Istanbul');

		$start = (int) round(strtotime($startDate . ' 00:00:00') * 1000);
		$end   = (int) round(strtotime($endDate . ' 23:59:59') * 1000);

		if ($idOrder) {
			$endpoint = "order/sellers/{$this->supplierId}/orders?orderNumber=".(int)$idOrder;
		} else {
			$endpoint = "order/sellers/{$this->supplierId}/orders?startDate={$start}&endDate={$end}&orderByField=PackageLastModifiedDate&orderByDirection=DESC&size=200";
		}

		return $this->request('GET', $endpoint);
		//return $endpoint;
	}

	public function getBuybox($barcode)
	{
		return $this->getBuyboxInformation([(string)$barcode]);
	}

	public function getBuyboxInformation(array $barcodes): ?array
	{
		$barcodes = array_values(array_filter(array_map('strval', $barcodes), function ($b) {
			return trim($b) !== '';
		}));
		$barcodes = array_slice($barcodes, 0, 10);
		if (empty($barcodes))
			return null;

		$endpoint = "product/sellers/{$this->supplierId}/products/buybox-information";
		return $this->request('POST', $endpoint, ['barcodes' => $barcodes]);
	}
	public function getProducts($limit, $page = 0)
    {
        $endpoint = "product/sellers/{$this->supplierId}/products?approved=true&size=".(int)$limit."&page=".(int)$page;
        return $this->request('GET', $endpoint);
    }
	public function createProduct($data)
    {
        $endpoint = "product/sellers/{$this->supplierId}/products";
        return $this->request('POST', $endpoint, $data);
    }
	public function getBatch($data)
    {
        $endpoint = "product/sellers/{$this->supplierId}/products/batch-requests/".$data;
        return $this->request('GET', $endpoint);
    }
	public function getProduct($barcode)
    {
        $endpoint = "product/sellers/{$this->supplierId}/products?barcode=".$barcode;
        return $this->request('GET', $endpoint);
    }
	public function getCategories()
    {
        $endpoint = "product/product-categories";
        return $this->request('GET', $endpoint);
    }
	public function getBrand($name)
    {
        $endpoint = "product/brands/by-name?name={$name}";
        return $this->request('GET', $endpoint);
    }
	public function getAttirupes($id)
    {
        $endpoint = "product/product-categories/{$id}/attributes";
        return $this->request('GET', $endpoint);
    }
	public function getQuestion()
    {
        $endpoint = "qna/sellers/{$this->supplierId}/questions/filter?orderByField=CreatedDate&orderByDirection=DESC&size=25";
        return $this->request('GET', $endpoint);
    }
	public function postQuestion($id, $text)
    {
        $endpoint 	= "qna/sellers/{$this->supplierId}/questions/".(int)$id."/answers";
		$data      	= ['text' => $text];
        return $this->request('POST', $endpoint, $data);
    }
	public function updateStockPrice($barcode, $listPrice, $salePrice, $stock, $sku = NULL)
    {
        $updateData = [
            'items' => [
                [
                    'barcode' => $barcode,
                    'quantity' => $stock,
                    'salePrice' => $salePrice,
                    'listPrice' => $listPrice
                ]
            ]
        ];
		$endpoint = "inventory/sellers/{$this->supplierId}/products/price-and-inventory";
        return $this->request('POST', $endpoint, $updateData);
    }
	
    private function request(string $method, string $endpoint, array $data = []): ?array
	{
		$url  = $this->baseUri . $endpoint;
		$auth = base64_encode($this->apiKey . ':' . $this->apiSecret);

		$ch = curl_init();

		// 🔹 Eğer POST, PUT, PATCH gibi gövdeye veri gönderen istekse
		if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && !empty($data)) {
			$jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
		}

		curl_setopt_array($ch, [
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_CUSTOMREQUEST  => $method,
			CURLOPT_HTTPHEADER     => [
				'Authorization: Basic ' . $auth,
				'Content-Type: application/json',
				'User-Agent: Trendyol-Integration-Client/1.0',
				'Accept: application/json'
			],
			CURLOPT_TIMEOUT        => 30
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error    = curl_error($ch);
		curl_close($ch);

		$decoded = json_decode($response, true);

		if ($httpCode >= 200 && $httpCode < 300) {
			// Bazı servisler (ör. iade onaylama) 200 dönüp JSON gövde döndürmez;
			// başarıyı null'dan ayırt edebilmek için durum bilgisi döndürülür
			if ($decoded === null)
				return ['success' => true, 'httpStatus' => $httpCode];
			return $decoded;
		}

		return [
			'success'  => false,
			'httpCode' => $httpCode,
			'message'  => $error ?: ('HTTP ' . $httpCode),
			'body'     => is_array($decoded) ? $decoded : (string)$response,
		];
	}
	public function convertAttributes($attirupe) 
	{
		$attributes = [];
		foreach ($attirupe as $attributeId => $attributeValue) {
			if (!empty($attributeValue)) {
				// Eğer değer sayısal ise (örneğin: 10621762, 7009)
				if (is_numeric($attributeValue)) {
					$attributes[] = [
						"attributeId" => (int)$attributeId,
						"attributeValueId" => (int)$attributeValue
					];
				}
				// Eğer değer metinsel ise (örneğin: "Siyah")
				else {
					$attributes[] = [
						"attributeId" => (int)$attributeId,
						"customAttributeValue" => (string)$attributeValue
					];
				}
			}
		}
		return $attributes;
	}
}