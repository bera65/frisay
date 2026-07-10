<?php

namespace Bifatura;

class InvoiceService
{
	public static function ensureSchema(): void
	{
		static $ready = false;

		if ($ready) {
			return;
		}

		$ready = true;

		\DB::execute(
			'CREATE TABLE IF NOT EXISTS bifatura_invoices (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				id_order INT UNSIGNED NOT NULL,
				reference VARCHAR(24) NOT NULL DEFAULT "",
				invoice_no VARCHAR(64) NOT NULL DEFAULT "",
				ettn VARCHAR(64) NOT NULL DEFAULT "",
				system_type VARCHAR(16) NOT NULL DEFAULT "",
				pdf_link TEXT NULL,
				status VARCHAR(32) NOT NULL DEFAULT "created",
				message TEXT NULL,
				date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_order (id_order),
				KEY idx_reference (reference),
				KEY idx_ettn (ettn)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
		);
	}

	public static function findByOrderId(int $idOrder): ?array
	{
		self::ensureSchema();

		$row = \DB::getRowSafe('bifatura_invoices', 'id_order = ?', [$idOrder]);

		return $row ?: null;
	}

	public static function newEttn(): string
	{
		$data = random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
		$hex = bin2hex($data);

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr($hex, 0, 8),
			substr($hex, 8, 4),
			substr($hex, 12, 4),
			substr($hex, 16, 4),
			substr($hex, 20, 12)
		);
	}

	/**
	 * Siparişten Bifatura e-fatura / e-arşiv oluşturur.
	 *
	 * @return array{ok: bool, message: string, invoice?: array, pdf_link?: string}
	 */
	public static function createFromOrder(int $idOrder): array
	{
		self::ensureSchema();

		$api = BifaturaApi::fromSettings();

		if (!$api) {
			return ['ok' => false, 'message' => 'Bifatura API anahtarları tanımlı değil. Modül ayarlarından kaydedin.'];
		}

		$order = \Order::getByIdAdmin($idOrder);

		if (!$order) {
			return ['ok' => false, 'message' => 'Sipariş bulunamadı'];
		}

		$existing = self::findByOrderId($idOrder);

		if ($existing && trim((string) $existing['invoice_no']) !== '') {
			return [
				'ok' => false,
				'message' => 'Bu sipariş zaten faturalandı (' . $existing['invoice_no'] . ')',
				'invoice' => $existing,
			];
		}

		$reference = (string) $order['reference'];
		$billingName = trim((string) ($order['company_name'] ?? ''));

		if ($billingName === '') {
			$billingName = trim((string) ($order['customer_name'] ?? 'Müşteri'));
		}

		$address = trim((string) ($order['address_text'] ?? 'Adres'));
		$city = trim((string) ($order['address_city'] ?? 'İstanbul'));
		$district = trim((string) ($order['address_district'] ?? ''));
		$fullAddress = trim($address . ($district !== '' ? ', ' . $district : ''));
		$phone = preg_replace('/\s+/', '', (string) ($order['customer_phone'] ?? '0000000000'));
		$email = trim((string) ($order['customer_email'] ?? ''));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$email = 'magaza@magaza.com';
		}

		$taxNo = preg_replace('/\D/', '', (string) ($order['tax_number'] ?? ''));

		if (strlen($taxNo) < 10) {
			$taxNo = '11111111111';
		}

		$systemType = BifaturaApi::resolveSystemType($taxNo);
		$ettn = self::newEttn();

		$orderDetails = [];
		$productsTotalIncl = 0.0;
		$productsTotalExcl = 0.0;

		foreach ($order['items'] as $item) {
			$vatRate = (int) round((float) ($item['vat'] ?? 20));

			if ($vatRate <= 0) {
				$vatRate = 20;
			}

			$qty = max(1, (int) ($item['qty'] ?? 1));
			$lineTotalIncl = (float) ($item['total'] ?? 0);
			$unitIncl = $lineTotalIncl / $qty;
			$unitExcl = $unitIncl / (1 + ($vatRate / 100));
			$lineExcl = $unitExcl * $qty;

			$productsTotalIncl += $lineTotalIncl;
			$productsTotalExcl += $lineExcl;

			$productCode = trim((string) ($item['stock_code'] ?? ''));

			if ($productCode === '') {
				$productCode = (string) ((int) ($item['id_product'] ?? 0) ?: 'SKU');
			}

			$orderDetails[] = [
				'productCode' => $productCode,
				'barcode' => (string) ($item['barcode'] ?? ''),
				'productName' => (string) ($item['product_name'] ?? 'Ürün'),
				'productQuantityType' => 'Adet',
				'productQuantity' => $qty,
				'vatRate' => $vatRate,
				'productUnitPriceTaxExcluding' => round($unitExcl, 2),
				'productUnitPriceTaxIncluding' => round($unitIncl, 2),
			];
		}

		$shippingIncl = (float) ($order['shipping'] ?? 0);
		$shippingExcl = $shippingIncl > 0 ? ($shippingIncl / 1.20) : 0.0;

		// Kupon indirimi ürün toplamından düş
		$couponDiscount = (float) ($order['coupon_discount'] ?? 0);

		if ($couponDiscount > 0 && $productsTotalIncl > 0) {
			$ratio = max(0, ($productsTotalIncl - $couponDiscount) / $productsTotalIncl);
			$productsTotalIncl *= $ratio;
			$productsTotalExcl *= $ratio;

			foreach ($orderDetails as $i => $detail) {
				$orderDetails[$i]['productUnitPriceTaxExcluding'] = round($detail['productUnitPriceTaxExcluding'] * $ratio, 2);
				$orderDetails[$i]['productUnitPriceTaxIncluding'] = round($detail['productUnitPriceTaxIncluding'] * $ratio, 2);
			}
		}

		$totalIncl = $productsTotalIncl + $shippingIncl;
		$totalExcl = $productsTotalExcl + $shippingExcl;

		// Sipariş toplamı ile hizala
		$expectedTotal = (float) $order['total'];

		if (abs($totalIncl - $expectedTotal) >= 0.01 && $expectedTotal > 0) {
			$totalIncl = $expectedTotal;
			$totalExcl = $expectedTotal / 1.20;
		}

		$orderDate = date('Y-m-d', strtotime((string) $order['date_add']));
		$invoiceDate = date('Y-m-d');

		$invoice = [
			'orderCode' => $reference,
			'orderDate' => $orderDate,
			'invoiceDate' => $invoiceDate,
			'invoiceExplanation' => 'Sipariş #' . $reference,
			'isDocumentNoAuto' => true,
			'ettn' => $ettn,
			'billingName' => $billingName,
			'billingAddress' => mb_substr($fullAddress, 0, 250),
			'billingCity' => mb_substr($city, 0, 32),
			'billingMobilePhone' => $phone,
			'taxNo' => $taxNo,
			'email' => $email,
			'shippingName' => $billingName,
			'shippingAddress' => mb_substr($fullAddress, 0, 250),
			'shippingCity' => mb_substr($city, 0, 32),
			'shippingCountry' => 'Türkiye',
			'shippingPhone' => $phone,
			'currency' => 'TRY',
			'currencyRate' => 1,
			'totalPaidTaxExcluding' => round($totalExcl, 2),
			'totalPaidTaxIncluding' => round($totalIncl, 2),
			'productsTotalTaxExcluding' => round($productsTotalExcl, 2),
			'productsTotalTaxIncluding' => round($productsTotalIncl, 2),
			'shippingChargeTotalTaxExcluding' => round($shippingExcl, 2),
			'shippingChargeTotalTaxIncluding' => round($shippingIncl, 2),
			'orderDetails' => $orderDetails,
		];

		$taxOffice = trim((string) ($order['tax_office'] ?? ''));

		if ($taxOffice !== '') {
			$invoice['taxOffice'] = mb_substr($taxOffice, 0, 64);
		}

		$cargoCompany = trim((string) ($order['cargo_company'] ?? ''));

		if ($cargoCompany !== '') {
			$invoice['shipCompany'] = mb_substr($cargoCompany, 0, 64);
		}

		$tracking = trim((string) ($order['tracking_number'] ?? ''));

		if ($tracking !== '') {
			$invoice['cargoCampaignCode'] = mb_substr($tracking, 0, 64);
		}

		$response = $api->sendBasicInvoice($invoice);

		if (!$response || empty($response['Success'])) {
			$msg = (string) ($response['Message'] ?? 'Bilinmeyen API hatası');

			\DB::execute(
				'INSERT INTO bifatura_invoices (id_order, reference, status, message, system_type)
				 VALUES (?, ?, "failed", ?, ?)
				 ON DUPLICATE KEY UPDATE status = "failed", message = VALUES(message), date_upd = CURRENT_TIMESTAMP',
				[$idOrder, $reference, mb_substr($msg, 0, 500), $systemType]
			);

			return ['ok' => false, 'message' => $reference . ' — fatura başarısız: ' . $msg];
		}

		$result = is_array($response['Result'] ?? null) ? $response['Result'] : [];
		$invoiceNo = (string) ($result['invoiceNo'] ?? $result['InvoiceNo'] ?? $result['DocumentNo'] ?? '');
		$pdfLink = (string) ($result['pdfLink'] ?? $result['PdfLink'] ?? '');

		if (!empty($result['ettn'])) {
			$ettn = (string) $result['ettn'];
		} elseif (!empty($result['uuid'])) {
			$ettn = (string) $result['uuid'];
		} elseif (!empty($result['UUID'])) {
			$ettn = (string) $result['UUID'];
		}

		\DB::execute(
			'INSERT INTO bifatura_invoices
				(id_order, reference, invoice_no, ettn, system_type, pdf_link, status, message)
			 VALUES (?, ?, ?, ?, ?, ?, "created", ?)
			 ON DUPLICATE KEY UPDATE
				invoice_no = VALUES(invoice_no),
				ettn = VALUES(ettn),
				system_type = VALUES(system_type),
				pdf_link = VALUES(pdf_link),
				status = "created",
				message = VALUES(message),
				date_upd = CURRENT_TIMESTAMP',
			[
				$idOrder,
				$reference,
				mb_substr($invoiceNo, 0, 64),
				mb_substr($ettn, 0, 64),
				$systemType,
				$pdfLink !== '' ? $pdfLink : null,
				'Fatura oluşturuldu',
			]
		);

		$msg = $reference . ' — fatura oluşturuldu';

		if ($invoiceNo !== '') {
			$msg .= ' (No: ' . $invoiceNo . ')';
		}

		if ($pdfLink === '') {
			$msg .= ' (PDF birkaç dakika içinde hazır olur)';
		}

		return [
			'ok' => true,
			'message' => $msg,
			'invoice' => self::findByOrderId($idOrder),
			'pdf_link' => $pdfLink,
		];
	}

	/** @return array{ok: bool, message?: string, pdfLink?: string, uuid?: string} */
	public static function getPdfLinkForOrder(int $idOrder): array
	{
		self::ensureSchema();

		$api = BifaturaApi::fromSettings();

		if (!$api) {
			return ['ok' => false, 'message' => 'Bifatura API anahtarları tanımlı değil'];
		}

		$row = self::findByOrderId($idOrder);

		if (!$row) {
			return ['ok' => false, 'message' => 'Bu sipariş için fatura kaydı yok'];
		}

		if (!empty($row['pdf_link'])) {
			return ['ok' => true, 'pdfLink' => (string) $row['pdf_link'], 'uuid' => (string) $row['ettn']];
		}

		$ettn = trim((string) $row['ettn']);
		$tip = trim((string) $row['system_type']);
		$faturaNo = trim((string) $row['invoice_no']);
		$order = \Order::getByIdAdmin($idOrder);
		$orderDate = $order ? (string) $order['date_add'] : null;

		if ($ettn === '' && $faturaNo !== '') {
			$found = self::findOutboxByInvoiceNo($faturaNo, $orderDate);

			if ($found) {
				$ettn = $found['uuid'];
				$tip = $found['systemType'];
				\DB::update(
					'bifatura_invoices',
					['ettn' => $ettn, 'system_type' => $tip],
					'id_order = :id',
					['id' => $idOrder]
				);
			}
		}

		if ($ettn === '') {
			return ['ok' => false, 'message' => 'Fatura UUID bulunamadı. Bifatura giden kutusunda eşleşme yok.'];
		}

		if ($tip === '' && $faturaNo !== '') {
			$tip = (stripos($faturaNo, 'EA') === 0) ? 'EARSIV' : 'EFATURA';
		}

		$types = $tip !== '' ? [$tip] : ['EARSIV', 'EFATURA'];
		$result = self::requestPdfLink($api, $ettn, $types);

		if ($result['ok'] && !empty($result['pdfLink'])) {
			\DB::update(
				'bifatura_invoices',
				['pdf_link' => $result['pdfLink']],
				'id_order = :id',
				['id' => $idOrder]
			);
		}

		return $result;
	}

	/** @return array{uuid: string, systemType: string, invoiceNo: string}|null */
	public static function findOutboxByInvoiceNo(string $faturaNo, ?string $orderDate = null): ?array
	{
		$api = BifaturaApi::fromSettings();

		if (!$api || $faturaNo === '') {
			return null;
		}

		if ($orderDate && strtotime($orderDate)) {
			$startTs = strtotime($orderDate . ' -30 days');
			$endTs = strtotime($orderDate . ' +7 days');
		} else {
			$startTs = strtotime('-120 days');
			$endTs = time();
		}

		$start = gmdate('Y-m-d\TH:i:s\Z', $startTs);
		$end = gmdate('Y-m-d\TH:i:s\Z', $endTs);
		$target = mb_strtoupper(trim($faturaNo));

		foreach (['EARSIV', 'EFATURA'] as $systemType) {
			for ($page = 0; $page < 3; $page++) {
				$response = $api->getOutBoxDocuments($systemType, $start, $end, $page, 'INVOICE', 'ALL');

				if (!$response || empty($response['Success'])) {
					break;
				}

				$buckets = $systemType === 'EARSIV'
					? ['OutBoxEArchiveDocuments']
					: ['OutBoxEDocuments', 'OutBoxEArchiveDocuments'];

				foreach ($buckets as $bucket) {
					$objects = $response['Result'][$bucket]['objects'] ?? [];

					if (!is_array($objects)) {
						continue;
					}

					foreach ($objects as $obj) {
						if (!is_array($obj)) {
							continue;
						}

						$no = mb_strtoupper(self::pickField($obj, [
							'DocumentNo', 'InvoiceNo', 'documentNo', 'invoiceNo',
						]));

						if ($no === '' || $no !== $target) {
							continue;
						}

						$uuid = self::pickField($obj, ['UUID', 'uuid', 'ettn', 'ETTN']);

						if ($uuid === '') {
							continue;
						}

						return [
							'uuid' => $uuid,
							'systemType' => self::pickField($obj, ['SystemTypeCode']) ?: $systemType,
							'invoiceNo' => self::pickField($obj, ['DocumentNo', 'InvoiceNo']),
						];
					}
				}

				$total = (int) ($response['Result'][$buckets[0]]['total'] ?? 0);
				$limit = (int) ($response['Result'][$buckets[0]]['limit'] ?? 200);

				if ($limit <= 0 || ($page + 1) * $limit >= $total) {
					break;
				}
			}
		}

		return null;
	}

	/** @return array{ok: bool, items: array, message?: string} */
	public static function fetchInboxInvoices(string $startDate, string $endDate, int $page = 0, string $readStatus = 'ALL'): array
	{
		$api = BifaturaApi::fromSettings();

		if (!$api) {
			return ['ok' => false, 'message' => 'Bifatura API anahtarları tanımlı değil', 'items' => []];
		}

		$start = gmdate('Y-m-d\TH:i:s\Z', strtotime($startDate . ' 00:00:00'));
		$end = gmdate('Y-m-d\TH:i:s\Z', strtotime($endDate . ' 23:59:59'));
		$items = [];

		foreach (['EFATURA', 'EARSIV'] as $systemType) {
			$response = $api->getInBoxDocuments($systemType, $start, $end, $page, 'INVOICE', $readStatus);

			if (!$response || empty($response['Success'])) {
				continue;
			}

			$objects = $response['Result']['InBoxInvoices']['objects'] ?? [];

			if (!is_array($objects)) {
				continue;
			}

			foreach ($objects as $obj) {
				if (!is_array($obj)) {
					continue;
				}

				$items[] = self::normalizeInboxInvoice($obj, $systemType);
			}
		}

		usort($items, static function ($a, $b) {
			return strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? ''));
		});

		return ['ok' => true, 'items' => $items];
	}

	/** @return array{ok: bool, message?: string, pdfLink?: string, uuid?: string} */
	public static function getInboxPdfLink(string $uuid, string $systemType = 'EFATURA'): array
	{
		$api = BifaturaApi::fromSettings();

		if (!$api) {
			return ['ok' => false, 'message' => 'Bifatura API anahtarları tanımlı değil'];
		}

		$uuid = trim($uuid);

		if ($uuid === '') {
			return ['ok' => false, 'message' => 'UUID gerekli'];
		}

		$types = $systemType !== '' ? [$systemType] : ['EFATURA', 'EARSIV'];

		return self::requestPdfLink($api, $uuid, $types);
	}

	/** @param array<int, string> $systemTypes */
	private static function requestPdfLink(BifaturaApi $api, string $uuid, array $systemTypes): array
	{
		$lastMsg = 'PDF henüz hazır değil. Birkaç dakika sonra tekrar deneyin.';

		foreach ($systemTypes as $systemType) {
			$response = $api->getPdfLinkByUuid([$uuid], $systemType);
			$parsed = self::parsePdfLinkResponse($response, $uuid);

			if ($parsed['ok']) {
				return $parsed;
			}

			if (!empty($parsed['message'])) {
				$lastMsg = $parsed['message'];
			}
		}

		return ['ok' => false, 'message' => $lastMsg, 'uuid' => $uuid];
	}

	private static function parsePdfLinkResponse(?array $response, string $uuid = ''): array
	{
		if (!$response || empty($response['Success'])) {
			return ['ok' => false, 'message' => (string) ($response['Message'] ?? 'API hatası')];
		}

		$result = $response['Result'] ?? [];

		if (isset($result[0]) && is_array($result[0])) {
			$result = $result[0];
		}

		$link = self::pickField($result, ['pdfLink', 'PdfLink', 'PDFLink']);

		if ($link !== '') {
			return [
				'ok' => true,
				'pdfLink' => $link,
				'uuid' => $uuid !== '' ? $uuid : self::pickField($result, ['uuid', 'UUID']),
			];
		}

		$msg = self::pickField($result, ['message', 'Message']);

		if ($msg === '') {
			$msg = (string) ($response['Message'] ?? 'PDF linki alınamadı');
		}

		return ['ok' => false, 'message' => $msg, 'uuid' => $uuid];
	}

	/** @param array<string, mixed> $obj */
	private static function normalizeInboxInvoice(array $obj, string $systemType): array
	{
		$dateRaw = self::pickField($obj, ['IssueDate', 'issueDate', 'CreateDate', 'documentDate']);
		$dateFmt = $dateRaw;

		if ($dateRaw !== '' && ($ts = strtotime($dateRaw))) {
			$dateFmt = date('d.m.Y', $ts);
		}

		$amountRaw = self::pickField($obj, ['PayableAmount', 'payableAmount', 'TaxInclusiveAmount', 'totalAmount']);
		$readRaw = self::pickField($obj, ['ReadStatus', 'readStatus']);

		return [
			'uuid' => self::pickField($obj, ['UUID', 'uuid', 'ettn', 'ETTN']),
			'no' => self::pickField($obj, ['InvoiceNo', 'DocumentNo', 'invoiceNumber', 'eInvoiceId']),
			'sender' => self::pickField($obj, ['SenderName', 'senderTitle', 'supplierTitle', 'billingName']),
			'date' => $dateFmt,
			'amount' => $amountRaw !== '' ? number_format((float) $amountRaw, 2, ',', '.') . ' ₺' : '',
			'read' => $readRaw,
			'systemType' => self::pickField($obj, ['SystemTypeCode']) ?: $systemType,
		];
	}

	/** @param array<string, mixed> $obj @param array<int, string> $keys */
	private static function pickField(array $obj, array $keys): string
	{
		foreach ($keys as $key) {
			if (!array_key_exists($key, $obj) || $obj[$key] === null || $obj[$key] === '') {
				continue;
			}

			return trim((string) $obj[$key]);
		}

		return '';
	}
}
