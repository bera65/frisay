<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class BankwireModule extends ModuleBase
{
	public string $name = 'bankwire';
	public string $title = 'Banka Havalesi';
	public string $version = '1.1.0';
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
		self::ensureDefaultDiscount();

		return true;
	}

	public function uninstall(): bool
	{
		return true;
	}

	public static function ensureDefaultDiscount(): void
	{
		$current = Settings::get('BANKWIRE_DISCOUNT_PERCENT');

		if ($current !== null && $current !== '') {
			return;
		}

		$legacy = Settings::get('HAVALE');

		if ($legacy !== null && $legacy !== '') {
			Settings::set('BANKWIRE_DISCOUNT_PERCENT', (string) $legacy);

			return;
		}

		Settings::set('BANKWIRE_DISCOUNT_PERCENT', '3');
	}

	public static function getDiscountPercent(): float
	{
		self::ensureDefaultDiscount();

		$percent = (float) str_replace(',', '.', (string) Settings::get('BANKWIRE_DISCOUNT_PERCENT'));

		if ($percent < 0) {
			$percent = 0.0;
		}

		if ($percent > 100) {
			$percent = 100.0;
		}

		return $percent;
	}

	public static function formatDiscountPercentLabel(float $percent): string
	{
		return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
	}

	public function getPaymentDiscount(float $amount): array
	{
		$percent = self::getDiscountPercent();
		$amount = max(0.0, $amount);

		if ($percent <= 0 || $amount <= 0) {
			return [
				'amount' => 0.0,
				'label' => '',
				'percent' => 0.0,
			];
		}

		$discount = round($amount * ($percent / 100), 2);

		return [
			'amount' => $discount,
			'label' => 'Havale indirimi (%' . self::formatDiscountPercentLabel($percent) . ')',
			'percent' => $percent,
		];
	}

	public function adminPage(): void
	{
		global $smarty, $adminToken;

		self::ensureDefaultDiscount();
		$flash = '';

		if (Tools::isSubmit('saveBankwire')) {
			$postToken = (string) Tools::getValue('token');

			if (hash_equals($adminToken, $postToken)) {
				Settings::set('BANKWIRE_HOLDER', trim((string) Tools::getValue('holder')));
				Settings::set('BANKWIRE_BANK', trim((string) Tools::getValue('bank')));
				Settings::set('BANKWIRE_IBAN', trim((string) Tools::getValue('iban')));

				$percent = (float) str_replace(',', '.', (string) Tools::getValue('discount_percent'));

				if ($percent < 0) {
					$percent = 0;
				}

				if ($percent > 100) {
					$percent = 100;
				}

				Settings::set('BANKWIRE_DISCOUNT_PERCENT', (string) $percent);
				Settings::set('HAVALE', (string) $percent);
				$flash = 'Havale bilgileri kaydedildi';
			} else {
				$flash = 'Geçersiz istek';
			}
		}

		$smarty->assign([
			'bankwireHolder' => Settings::get('BANKWIRE_HOLDER'),
			'bankwireBank' => Settings::get('BANKWIRE_BANK'),
			'bankwireIban' => Settings::get('BANKWIRE_IBAN'),
			'bankwireDiscountPercent' => self::getDiscountPercent(),
			'flash' => $flash,
		]);
	}

	public function renderDisplayHook(string $hook, array $context = []): ?string
	{
		if ($hook === 'order_payment') {
			$html = $this->renderFrontTemplate('order_payment', [
				'bankwireDiscountPercent' => self::getDiscountPercent(),
				'bankwireDiscountLabel' => self::formatDiscountPercentLabel(self::getDiscountPercent()),
			]);

			return $html !== '' ? $html : null;
		}

		if ($hook === 'order_confirmation') {
			$order = isset($context['order']) ? $context['order'] : null;

			if (!$order || ($order['payment_method'] ?? '') !== $this->paymentMethodId) {
				return null;
			}

			$iban = trim((string) Settings::get('BANKWIRE_IBAN'));
			$ibanDigits = strtoupper(preg_replace('/\s+/', '', $iban));
			$ibanDisplay = $iban;

			if ($ibanDigits !== '' && preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]+$/', $ibanDigits)) {
				$ibanDisplay = trim(chunk_split($ibanDigits, 4, ' '));
			}

			$paymentDiscount = (float) ($order['payment_discount'] ?? 0);
			$paymentDiscountLabel = trim((string) ($order['payment_discount_label'] ?? ''));

			$html = $this->renderFrontTemplate('order_confirmation', [
				'bankwireHolder' => Settings::get('BANKWIRE_HOLDER'),
				'bankwireBank' => Settings::get('BANKWIRE_BANK'),
				'bankwireIban' => $iban,
				'bankwireIbanDisplay' => $ibanDisplay,
				'bankwireIbanCopy' => $ibanDigits !== '' ? $ibanDigits : $iban,
				'orderReference' => $order['reference'],
				'orderTotal' => $order['total_formatted'],
				'orderPaymentDiscountLabel' => $paymentDiscount > 0 ? $paymentDiscountLabel : '',
			]);

			return $html !== '' ? $html : null;
		}

		return null;
	}

	public function processPayment(array $order): array
	{
		return [
			'success' => true,
			'redirect' => '',
			'message' => '',
		];
	}
}
