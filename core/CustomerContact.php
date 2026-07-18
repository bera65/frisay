<?php

class CustomerContact
{
	public static function isWapioReady(): bool
	{
		return Module::isEnabled('wapio')
			&& Settings::get('WAPIO_ENABLED') === '1'
			&& trim((string) Settings::get('WAPIO_SESSION_ID')) !== '';
	}

	/** @return array{success: bool, message: string, mode?: string, url?: string} */
	public static function send(array $customer, string $channel, string $message): array
	{
		$message = trim($message);
		$channel = strtolower(trim($channel));

		if ($message === '') {
			return ['success' => false, 'message' => adminT('Message is required')];
		}

		if ($channel === 'whatsapp') {
			return self::sendWhatsApp($customer, $message);
		}

		if ($channel === 'email') {
			return self::sendEmail($customer, $message);
		}

		return ['success' => false, 'message' => adminT('Invalid contact channel')];
	}

	/** @return array{success: bool, message: string, mode?: string, url?: string} */
	private static function sendWhatsApp(array $customer, string $message): array
	{
		$phone = trim((string) ($customer['phone'] ?? ''));

		if ($phone === '') {
			return ['success' => false, 'message' => adminT('Customer phone number is missing')];
		}

		if (self::isWapioReady()) {
			$wapio = self::getWapioModule();

			if (!$wapio) {
				return ['success' => false, 'message' => adminT('Wapio module could not be loaded')];
			}

			$normalized = $wapio->normalizePhone($phone);

			if ($normalized === '') {
				return ['success' => false, 'message' => adminT('Invalid phone number format')];
			}

			$result = $wapio->sendText($normalized, $message);

			if (!empty($result['success'])) {
				return ['success' => true, 'message' => adminT('WhatsApp message sent via Wapio'), 'mode' => 'wapio'];
			}

			return [
				'success' => false,
				'message' => adminT('WhatsApp message could not be sent') . ': ' . (string) ($result['message'] ?? ''),
			];
		}

		$url = self::buildWhatsAppUrl($phone, $message);

		if ($url === '') {
			return ['success' => false, 'message' => adminT('Invalid phone number format')];
		}

		return [
			'success' => true,
			'message' => adminT('Opening WhatsApp with your message'),
			'mode' => 'redirect',
			'url' => $url,
		];
	}

	/** @return array{success: bool, message: string, mode?: string} */
	private static function sendEmail(array $customer, string $message): array
	{
		$email = trim((string) ($customer['email'] ?? ''));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return ['success' => false, 'message' => adminT('Customer email address is missing or invalid')];
		}

		$siteName = Settings::get('SITE_NAME') ?: 'FShop';
		$name = htmlspecialchars((string) ($customer['user_full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
		$body = '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'), false) . '</p>';

		if (!Mail::send($email, $siteName . ' - ' . adminT('Message from store'), $body)) {
			$error = Mail::getLastError();

			return [
				'success' => false,
				'message' => adminT('Email could not be sent') . ($error !== '' ? ': ' . $error : ''),
			];
		}

		return ['success' => true, 'message' => adminT('Email sent successfully'), 'mode' => 'email'];
	}

	public static function buildWhatsAppUrl(string $phone, string $message): string
	{
		$digits = self::normalizePhoneDigits($phone);

		if ($digits === '') {
			return '';
		}

		return 'https://wa.me/' . rawurlencode($digits) . '?text=' . rawurlencode($message);
	}

	private static function normalizePhoneDigits(string $phone): string
	{
		if (Module::isEnabled('wapio')) {
			$wapio = self::getWapioModule();

			if ($wapio) {
				return $wapio->normalizePhone($phone);
			}
		}

		$digits = preg_replace('/\D+/', '', $phone) ?: '';

		if ($digits === '') {
			return '';
		}

		if (strpos($digits, '90') === 0 && strlen($digits) === 12) {
			return $digits;
		}

		if (strpos($digits, '0') === 0 && strlen($digits) === 11) {
			return '90' . substr($digits, 1);
		}

		if (strpos($digits, '5') === 0 && strlen($digits) === 10) {
			return '90' . $digits;
		}

		return $digits;
	}

	private static function getWapioModule(): ?WapioModule
	{
		if (!Module::isEnabled('wapio')) {
			return null;
		}

		$file = dirname(__DIR__) . '/modules/wapio/wapio.php';

		if (!is_file($file)) {
			return null;
		}

		require_once $file;

		return new WapioModule();
	}
}
