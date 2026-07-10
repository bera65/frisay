<?php

namespace BizimHesap;

class EFaturaCreate extends HttpRequest
{
	/** @var array<string, mixed> */
	private array $informations = [];

	private string $invoiceUrl = 'https://bizimhesap.com/api/b2b/addinvoice';

	/** @param array<string, mixed> $informations */
	public function __construct(array $informations)
	{
		$this->informations = $informations;
	}

	/** @return array{error: bool, msg?: string} */
	public function check(): array
	{
		if (empty($this->informations['firmId'])) {
			return ['error' => true, 'msg' => 'Lütfen Firma ID alanını boş bırakmayın.'];
		}

		if (empty($this->informations['invoiceType'])) {
			return ['error' => true, 'msg' => 'Lütfen invoiceType alanını boş bırakmayın.'];
		}

		if (empty($this->informations['dates']['invoiceDate'])) {
			return ['error' => true, 'msg' => 'Lütfen invoiceDate alanını boş bırakmayın.'];
		}

		if (empty($this->informations['dates']['dueDate'])) {
			return ['error' => true, 'msg' => 'Lütfen dueDate alanını boş bırakmayın.'];
		}

		if ($this->informations['customer']['customerId'] === '' || $this->informations['customer']['customerId'] === null) {
			return ['error' => true, 'msg' => 'Lütfen Müşteri ID alanını boş bırakmayın.'];
		}

		if (empty($this->informations['customer']['title'])) {
			return ['error' => true, 'msg' => 'Lütfen Müşteri Adını boş bırakmayın.'];
		}

		if (empty($this->informations['customer']['address'])) {
			return ['error' => true, 'msg' => 'Lütfen Müşteri Adresini boş bırakmayın.'];
		}

		if (empty($this->informations['amounts']['currency'])) {
			return ['error' => true, 'msg' => 'Lütfen Para birimini boş bırakmayın.'];
		}

		if (!isset($this->informations['amounts']['gross'])) {
			return ['error' => true, 'msg' => 'Lütfen Brüt alanını boş bırakmayın.'];
		}

		if (!isset($this->informations['amounts']['discount'])) {
			return ['error' => true, 'msg' => 'Lütfen İndirim alanını boş bırakmayın.'];
		}

		if (!isset($this->informations['amounts']['net'])) {
			return ['error' => true, 'msg' => 'Lütfen Net Tutar alanını boş bırakmayın.'];
		}

		if (!isset($this->informations['amounts']['tax'])) {
			return ['error' => true, 'msg' => 'Lütfen KDV Tutarını boş bırakmayın.'];
		}

		if (!isset($this->informations['amounts']['total'])) {
			return ['error' => true, 'msg' => 'Lütfen Toplam Tutarı boş bırakmayın.'];
		}

		if (empty($this->informations['details']) || !is_array($this->informations['details'])) {
			return ['error' => true, 'msg' => 'En az bir ürün satırı gerekli.'];
		}

		return ['error' => false];
	}

	/** @return array{error: bool, msg?: string, data?: mixed} */
	public function run(): array
	{
		$check = $this->check();

		if ($check['error']) {
			return $check;
		}

		return $this->sendRequest($this->invoiceUrl, [
			'data' => $this->informations,
		]);
	}
}
