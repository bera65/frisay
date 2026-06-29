<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class CashondeliveryModule extends ModuleBase
{
	public string $name = 'cashondelivery';
	public string $title = 'Kapıda Ödeme';
	public string $version = '1.0.0';
	public string $description = 'Teslimatta nakit veya kart ile ödeme';
	public string $author = 'FShop';

	public bool $isPayment = true;
	public string $paymentMethodId = 'cash_on_delivery';
	public string $paymentMethodLabel = 'Kapıda Ödeme';

	public array $displayHooks = [
		'order_payment' => 'Checkout ödeme seçeneği',
	];

	public array $defaultDisplayHooks = ['order_payment'];

	public function install(): bool
	{
		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook !== 'order_payment') {
			return null;
		}

		$cart = $context['cart'] ?? null;

		if (is_array($cart) && Cart::hasVirtualProducts($cart)) {
			return null;
		}

		$html = $this->renderFrontTemplate('order_payment', []);

		return $html !== '' ? $html : null;
	}

	/** Kapıda ödemede tahsilat teslimatta yapılır; ek işlem yok */
	public function processPayment(array $order): array
	{
		return [
			'success' => true,
			'redirect' => '',
			'message' => '',
		];
	}
}
