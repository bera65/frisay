<?php

namespace BizimHesap;

class EFatura
{
	/** @var array<string, mixed> */
	protected array $cloneInformations = [
		'firmId' => '',
		'invoiceNo' => '',
		'invoiceType' => 3,
		'note' => '',
		'dates' => [
			'invoiceDate' => '',
			'dueDate' => '',
			'deliveryDate' => '',
		],
		'customer' => [
			'customerId' => '',
			'title' => '',
			'taxOffice' => '',
			'taxNo' => '',
			'email' => '',
			'phone' => '',
			'address' => '',
		],
		'amounts' => [
			'currency' => '',
			'gross' => '',
			'discount' => '',
			'net' => '',
			'tax' => '',
			'total' => '',
		],
		'details' => [],
	];

	/** @var array<string, mixed> */
	protected array $informations = [];

	public function __construct()
	{
		$this->clearInformations();
	}

	public function setFirmId($data): void
	{
		$this->informations['firmId'] = $data;
	}

	public function setInvoiceNo($data): void
	{
		$this->informations['invoiceNo'] = $data;
	}

	/** @param bool $isPurchase true = alış (5), false = satış (3) */
	public function setInvoiceType($data): void
	{
		$this->informations['invoiceType'] = $data === false ? 3 : 5;
	}

	public function addNote($note): void
	{
		$this->informations['note'] = $note;
	}

	public function setInvoiceDate($date): void
	{
		$this->informations['dates']['invoiceDate'] = date('Y-m-d\TH:i:s', (int) $date);
	}

	public function setDueDate($date): void
	{
		$this->informations['dates']['dueDate'] = date('Y-m-d\TH:i:s', (int) $date);
	}

	public function setDeliveryDate($date): void
	{
		$this->informations['dates']['deliveryDate'] = date('Y-m-d\TH:i:s', (int) $date);
	}

	public function setCustomerId($data): void
	{
		$this->informations['customer']['customerId'] = $data;
	}

	public function setCustomerFullName($data): void
	{
		$this->informations['customer']['title'] = $data;
	}

	public function setCustomerEmail($data): void
	{
		$this->informations['customer']['email'] = $data;
	}

	public function setCustomerPhone($data): void
	{
		$this->informations['customer']['phone'] = $data;
	}

	public function setCustomerAddress($data): void
	{
		$this->informations['customer']['address'] = $data;
	}

	public function setCustomerTaxOffice($data): void
	{
		$this->informations['customer']['taxOffice'] = $data;
	}

	public function setCustomerTaxNo($data): void
	{
		$this->informations['customer']['taxNo'] = $data;
	}

	public function setAmountCurrency($data): void
	{
		$this->informations['amounts']['currency'] = $data;
	}

	public function setAmountGross($data): void
	{
		$this->informations['amounts']['gross'] = (float) $data;
	}

	public function setAmountDiscount($data): void
	{
		$this->informations['amounts']['discount'] = (float) $data;
	}

	public function setAmountNet($data): void
	{
		$this->informations['amounts']['net'] = (float) $data;
	}

	public function setAmountTax($data): void
	{
		$this->informations['amounts']['tax'] = (float) $data;
	}

	public function setAmountTotal($data): void
	{
		$this->informations['amounts']['total'] = (float) $data;
	}

	/** @param array<string, mixed>|array<int, array<string, mixed>> $data */
	public function addProduct($data): void
	{
		$productList = !isset($data[0]) ? [$data] : $data;

		foreach ($productList as $product) {
			if (!is_array($product)) {
				continue;
			}

			$this->informations['details'][] = [
				'productId' => $product['Id'] ?? '',
				'productName' => $product['name'] ?? '',
				'note' => $product['note'] ?? '',
				'barcode' => $product['barcode'] ?? '',
				'taxRate' => (float) ($product['taxrate'] ?? 0),
				'quantity' => (int) ($product['count'] ?? 1),
				'unitPrice' => (float) ($product['price'] ?? 0),
				'grossPrice' => (float) ($product['gross'] ?? 0),
				'discount' => (float) ($product['discount'] ?? 0),
				'net' => (float) ($product['net'] ?? 0),
				'tax' => (float) ($product['tax'] ?? 0),
				'total' => (float) ($product['total'] ?? 0),
			];
		}
	}

	/** @return array{error: bool, msg?: string, data?: mixed} */
	public function sendInvoice(): array
	{
		$create = new EFaturaCreate($this->informations);
		$this->clearInformations();

		return $create->run();
	}

	private function clearInformations(): void
	{
		$this->informations = $this->cloneInformations;
		$this->informations['details'] = [];
	}
}
