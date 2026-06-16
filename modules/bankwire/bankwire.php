<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class BankwireModule extends ModuleBase
{
	public string $name = 'bankwire';
	public string $title = 'Banka Havalesi';
	public string $version = '1.0.0';
	public string $description = 'Havale / EFT ile ödeme alma';
	public string $author = 'FShop';

	public bool $isPayment = true;
	public string $paymentMethodId = 'bank_transfer';
	public string $paymentMethodLabel = 'Havale / EFT';

	public array $displayHooks = [
		'order_payment' => 'Checkout ödeme seçeneği',
		'order_confirmation' => 'Sipariş onayında havale bilgileri',
	];

	public array $defaultDisplayHooks = ['order_payment', 'order_confirmation'];

	public function install(): bool
	{
		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		$flash = '';

		if (Tools::isSubmit('saveBankwire')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BANKWIRE_HOLDER', trim((string) Tools::getValue('holder')));
				Settings::set('BANKWIRE_BANK', trim((string) Tools::getValue('bank')));
				Settings::set('BANKWIRE_IBAN', trim((string) Tools::getValue('iban')));
				$flash = 'Havale bilgileri kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'bankwireHolder' => Settings::get('BANKWIRE_HOLDER'),
			'bankwireBank' => Settings::get('BANKWIRE_BANK'),
			'bankwireIban' => Settings::get('BANKWIRE_IBAN'),
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook === 'order_payment') {
			$html = $this->renderFrontTemplate('order_payment', []);

			return $html !== '' ? $html : null;
		}

		if ($hook === 'order_confirmation') {
			$order = isset($context['order']) ? $context['order'] : null;

			// Sadece havale ile verilen siparişlerde göster
			if (!$order || $order['payment_method'] !== $this->paymentMethodId) {
				return null;
			}

			$html = $this->renderFrontTemplate('order_confirmation', [
				'bankwireHolder' => Settings::get('BANKWIRE_HOLDER'),
				'bankwireBank' => Settings::get('BANKWIRE_BANK'),
				'bankwireIban' => Settings::get('BANKWIRE_IBAN'),
				'orderReference' => $order['reference'],
				'orderTotal' => $order['total_formatted'],
			]);

			return $html !== '' ? $html : null;
		}

		return null;
	}

	/**
	 * Havalede online tahsilat yok: sipariş "Ödeme Bekliyor" durumunda kalır,
	 * müşteri onay sayfasına gider ve oradaki IBAN bilgileriyle ödeme yapar.
	 */
	public function processPayment(array $order): array
	{
		return [
			'success' => true,
			'redirect' => '',
			'message' => '',
		];
	}
}
