<?php

namespace BizimHesap;

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
			'CREATE TABLE IF NOT EXISTS bizimhesap_invoices (
				id INT UNSIGNED NOT NULL AUTO_INCREMENT,
				id_order INT UNSIGNED NOT NULL,
				reference VARCHAR(24) NOT NULL DEFAULT "",
				invoice_no VARCHAR(64) NOT NULL DEFAULT "",
				external_id VARCHAR(128) NOT NULL DEFAULT "",
				status VARCHAR(32) NOT NULL DEFAULT "created",
				message TEXT NULL,
				response_json TEXT NULL,
				date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
				date_upd DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY uq_order (id_order),
				KEY idx_reference (reference)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
		);
	}

	public static function findByOrderId(int $idOrder): ?array
	{
		self::ensureSchema();

		$row = \DB::getRowSafe('bizimhesap_invoices', 'id_order = ?', [$idOrder]);

		return $row ?: null;
	}

	public static function isConfigured(): bool
	{
		return trim((string) \Settings::get('BIZIMHESAP_FIRM_ID')) !== '';
	}

	/**
	 * @return array{ok: bool, message: string, invoice?: array, data?: mixed}
	 */
	public static function createFromOrder(int $idOrder): array
	{
		self::ensureSchema();

		$firmId = trim((string) \Settings::get('BIZIMHESAP_FIRM_ID'));

		if ($firmId === '') {
			return ['ok' => false, 'message' => 'BizimHesap Firma ID tanımlı değil. Modül ayarlarından kaydedin.'];
		}

		$order = \Order::getByIdAdmin($idOrder);

		if (!$order) {
			return ['ok' => false, 'message' => 'Sipariş bulunamadı'];
		}

		$existing = self::findByOrderId($idOrder);

		if ($existing && ($existing['status'] ?? '') === 'created') {
			return [
				'ok' => false,
				'message' => 'Bu sipariş daha önce BizimHesap\'a gönderildi'
					. (!empty($existing['invoice_no']) ? ' (' . $existing['invoice_no'] . ')' : ''),
				'invoice' => $existing,
			];
		}

		$reference = (string) ($order['reference'] ?? '');
		$billingName = trim((string) ($order['company_name'] ?? ''));

		if ($billingName === '') {
			$billingName = trim((string) ($order['customer_name'] ?? 'Müşteri'));
		}

		$address = trim((string) ($order['address_text'] ?? ''));
		$city = trim((string) ($order['address_city'] ?? ''));
		$district = trim((string) ($order['address_district'] ?? ''));
		$fullAddress = trim($address . ($district !== '' ? ', ' . $district : '') . ($city !== '' ? ', ' . $city : ''));

		if ($fullAddress === '') {
			$fullAddress = 'Adres belirtilmedi';
		}

		$phone = preg_replace('/\s+/', '', (string) ($order['customer_phone'] ?? ''));

		if ($phone === '') {
			$phone = '0000000000';
		}

		$email = trim((string) ($order['customer_email'] ?? ''));
		$taxNo = preg_replace('/\D/', '', (string) ($order['tax_number'] ?? ''));

		if (strlen($taxNo) < 10) {
			$taxNo = '11111111111';
		}

		$taxOffice = trim((string) ($order['tax_office'] ?? ''));
		$items = is_array($order['items'] ?? null) ? $order['items'] : [];

		if ($items === []) {
			return ['ok' => false, 'message' => 'Siparişte ürün satırı yok'];
		}

		$efatura = new EFatura();
		$grossSum = 0.0;
		$taxSum = 0.0;
		$discountSum = 0.0;
		$totalSum = 0.0;

		$couponDiscount = (float) ($order['coupon_discount'] ?? 0);
		$productsTotalIncl = 0.0;

		foreach ($items as $item) {
			$productsTotalIncl += (float) ($item['total'] ?? 0);
		}

		$discountRatio = ($couponDiscount > 0 && $productsTotalIncl > 0)
			? min(1, $couponDiscount / $productsTotalIncl)
			: 0.0;

		foreach ($items as $item) {
			$vatRate = (int) round((float) ($item['vat'] ?? 20));

			if ($vatRate <= 0) {
				$vatRate = 20;
			}

			$qty = max(1, (int) ($item['qty'] ?? 1));
			$lineTotalIncl = (float) ($item['total'] ?? 0);
			$lineDiscountIncl = $lineTotalIncl * $discountRatio;
			$lineAfterDiscountIncl = $lineTotalIncl - $lineDiscountIncl;

			$kdvHaric = $lineAfterDiscountIncl / ((100 + $vatRate) / 100);
			$urunKdv = $lineAfterDiscountIncl - $kdvHaric;
			$kdvHDis = $lineDiscountIncl / ((100 + $vatRate) / 100);

			$productCode = trim((string) ($item['stock_code'] ?? ''));

			if ($productCode === '') {
				$productCode = (string) ((int) ($item['id_product'] ?? 0) ?: 'SKU');
			}

			$efatura->addProduct([
				'Id' => $productCode,
				'name' => (string) ($item['product_name'] ?? 'Ürün'),
				'barcode' => (string) ($item['barcode'] ?? ''),
				'taxrate' => $vatRate,
				'count' => $qty,
				'price' => round($kdvHaric / $qty, 4),
				'gross' => round($kdvHaric, 2),
				'discount' => round($kdvHDis, 2),
				'net' => round($kdvHaric, 2),
				'tax' => round($urunKdv, 2),
				'total' => round($lineAfterDiscountIncl, 2),
			]);

			$grossSum += $kdvHaric;
			$taxSum += $urunKdv;
			$discountSum += $kdvHDis;
			$totalSum += $lineAfterDiscountIncl;
		}

		$shippingIncl = (float) ($order['shipping'] ?? 0);

		if ($shippingIncl > 0) {
			$shipVat = 20;
			$shipExcl = $shippingIncl / 1.20;
			$shipTax = $shippingIncl - $shipExcl;

			$efatura->addProduct([
				'Id' => 'SHIPPING',
				'name' => 'Kargo',
				'barcode' => '',
				'taxrate' => $shipVat,
				'count' => 1,
				'price' => round($shipExcl, 2),
				'gross' => round($shipExcl, 2),
				'discount' => 0,
				'net' => round($shipExcl, 2),
				'tax' => round($shipTax, 2),
				'total' => round($shippingIncl, 2),
			]);

			$grossSum += $shipExcl;
			$taxSum += $shipTax;
			$totalSum += $shippingIncl;
		}

		$expectedTotal = (float) ($order['total'] ?? 0);

		if ($expectedTotal > 0 && abs($totalSum - $expectedTotal) >= 0.05) {
			$totalSum = $expectedTotal;
		}

		$ts = time();
		$orderDateTs = strtotime((string) ($order['date_add'] ?? '')) ?: $ts;

		$efatura->setFirmId($firmId);
		$efatura->setInvoiceNo($reference);
		$efatura->setInvoiceType(false);
		$efatura->addNote('Sipariş #' . $reference);
		$efatura->setInvoiceDate($orderDateTs);
		$efatura->setDueDate($ts);
		$efatura->setDeliveryDate($ts);
		$efatura->setCustomerId((string) $idOrder);
		$efatura->setCustomerFullName($billingName);
		$efatura->setCustomerEmail($email);
		$efatura->setCustomerPhone($phone);
		$efatura->setCustomerAddress(mb_substr($fullAddress, 0, 250));
		$efatura->setCustomerTaxOffice($taxOffice !== '' ? $taxOffice : ($order['company_name'] ?? ''));
		$efatura->setCustomerTaxNo($taxNo);
		$efatura->setAmountCurrency('TL');
		$efatura->setAmountGross(round($grossSum, 2));
		$efatura->setAmountDiscount(round($discountSum, 2));
		$efatura->setAmountNet(round($grossSum, 2));
		$efatura->setAmountTax(round($taxSum, 2));
		$efatura->setAmountTotal(round($totalSum, 2));

		try {
			$response = $efatura->sendInvoice();
		} catch (\Throwable $e) {
			$msg = $e->getMessage();
			self::saveFailed($idOrder, $reference, $msg);

			return ['ok' => false, 'message' => $reference . ' — fatura başarısız: ' . $msg];
		}

		if (!empty($response['error'])) {
			$msg = (string) ($response['msg'] ?? 'Bilinmeyen API hatası');
			self::saveFailed($idOrder, $reference, $msg, $response);

			return ['ok' => false, 'message' => $reference . ' — fatura başarısız: ' . $msg];
		}

		$data = is_array($response['data'] ?? null) ? $response['data'] : [];
		$invoiceNo = (string) (
			$data['invoiceNo']
			?? $data['InvoiceNo']
			?? $data['guid']
			?? $data['id']
			?? $reference
		);
		$externalId = (string) (
			$data['guid']
			?? $data['id']
			?? $data['uuid']
			?? ''
		);

		\DB::execute(
			'INSERT INTO bizimhesap_invoices
				(id_order, reference, invoice_no, external_id, status, message, response_json)
			 VALUES (?, ?, ?, ?, "created", ?, ?)
			 ON DUPLICATE KEY UPDATE
				invoice_no = VALUES(invoice_no),
				external_id = VALUES(external_id),
				status = "created",
				message = VALUES(message),
				response_json = VALUES(response_json),
				date_upd = CURRENT_TIMESTAMP',
			[
				$idOrder,
				$reference,
				mb_substr($invoiceNo, 0, 64),
				mb_substr($externalId, 0, 128),
				'Fatura oluşturuldu',
				json_encode($data, JSON_UNESCAPED_UNICODE),
			]
		);

		$msg = $reference . ' — fatura başarıyla oluşturuldu';

		if ($invoiceNo !== '' && $invoiceNo !== $reference) {
			$msg .= ' (No: ' . $invoiceNo . ')';
		}

		return [
			'ok' => true,
			'message' => $msg,
			'invoice' => self::findByOrderId($idOrder),
			'data' => $data,
		];
	}

	/** @param array<string, mixed>|null $response */
	private static function saveFailed(int $idOrder, string $reference, string $msg, ?array $response = null): void
	{
		\DB::execute(
			'INSERT INTO bizimhesap_invoices (id_order, reference, status, message, response_json)
			 VALUES (?, ?, "failed", ?, ?)
			 ON DUPLICATE KEY UPDATE
				status = "failed",
				message = VALUES(message),
				response_json = VALUES(response_json),
				date_upd = CURRENT_TIMESTAMP',
			[
				$idOrder,
				$reference,
				mb_substr($msg, 0, 500),
				$response !== null ? json_encode($response, JSON_UNESCAPED_UNICODE) : null,
			]
		);
	}
}
