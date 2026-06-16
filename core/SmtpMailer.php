<?php

class SmtpMailer
{
	private static string $lastError = '';

	public static function getLastError(): string
	{
		return self::$lastError;
	}

	public static function send(string $to, string $subject, string $bodyHtml): bool
	{
		self::$lastError = '';

		$host = trim(Settings::get('SMTP_HOST'));
		$user = trim(Settings::get('SMTP_USER'));
		$pass = Settings::get('SMTP_PASS');
		$port = (int) (Settings::get('SMTP_PORT') ?: 465);
		$encryption = strtolower(trim(Settings::get('SMTP_ENCRYPTION') ?: 'ssl'));

		if ($host === '' || $user === '' || $pass === '') {
			self::$lastError = 'SMTP sunucu, kullanıcı veya şifre eksik';
			return false;
		}

		if ($port <= 0) {
			$port = $encryption === 'ssl' ? 465 : 587;
		}

		$fromEmail = trim(Settings::get('SMTP_FROM_EMAIL')) ?: $user;
		$fromName = trim(Settings::get('SMTP_FROM_NAME')) ?: (Settings::get('SITE_NAME') ?: 'FShop');

		$socket = null;

		try {
			$socket = self::connect($host, $port, $encryption);

			if (!$socket) {
				return false;
			}

			stream_set_timeout($socket, 15);

			if (!self::command($socket, null, [220], 'Sunucu karşılama')) {
				return false;
			}

			$ehloHost = preg_replace('/[^a-zA-Z0-9.-]/', '', $_SERVER['SERVER_NAME'] ?? 'localhost') ?: 'localhost';

			if (!self::command($socket, 'EHLO ' . $ehloHost, [250], 'EHLO')) {
				if (!self::command($socket, 'HELO ' . $ehloHost, [250], 'HELO')) {
					return false;
				}
			}

			if ($encryption === 'tls') {
				if (!self::command($socket, 'STARTTLS', [220], 'STARTTLS')) {
					return false;
				}

				$cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
				if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
					$cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
				}

				if (!@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
					self::$lastError = 'TLS bağlantısı kurulamadı';
					return false;
				}

				if (!self::command($socket, 'EHLO ' . $ehloHost, [250], 'EHLO (TLS sonrası)')) {
					return false;
				}
			}

			if (!self::command($socket, 'AUTH LOGIN', [334], 'AUTH LOGIN')) {
				return false;
			}

			if (!self::command($socket, base64_encode($user), [334], 'SMTP kullanıcı')) {
				return false;
			}

			if (!self::command($socket, base64_encode($pass), [235], 'SMTP şifre')) {
				return false;
			}

			if (!self::command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250], 'MAIL FROM')) {
				return false;
			}

			if (!self::command($socket, 'RCPT TO:<' . $to . '>', [250, 251], 'RCPT TO')) {
				return false;
			}

			if (!self::command($socket, 'DATA', [354], 'DATA')) {
				return false;
			}

			$message = self::buildMessage($fromEmail, $fromName, $to, $subject, $bodyHtml);
			fwrite($socket, $message);

			if (!self::command($socket, null, [250], 'Mesaj gövdesi')) {
				return false;
			}

			self::write($socket, 'QUIT');
			fclose($socket);

			return true;
		} catch (Throwable $e) {
			self::$lastError = $e->getMessage();
			error_log('SMTP error: ' . $e->getMessage());

			if (is_resource($socket)) {
				fclose($socket);
			}

			return false;
		}
	}

	private static function connect(string $host, int $port, string $encryption)
	{
		$context = stream_context_create([
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true,
				'SNI_enabled' => true,
				'peer_name' => $host,
			],
		]);

		$remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;

		$socket = @stream_socket_client(
			$remote,
			$errno,
			$errstr,
			20,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if (!$socket) {
			self::$lastError = 'SMTP bağlantısı kurulamadı (' . $host . ':' . $port . '): ' . $errstr . ' [' . $errno . ']';
			return false;
		}

		return $socket;
	}

	private static function buildMessage(string $fromEmail, string $fromName, string $to, string $subject, string $bodyHtml): string
	{
		$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
		$headers = [
			'From: ' . $encodedFromName . ' <' . $fromEmail . '>',
			'To: <' . $to . '>',
			'Subject: ' . $encodedSubject,
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'Content-Transfer-Encoding: 8bit',
			'Date: ' . date('r'),
		];

		return implode("\r\n", $headers) . "\r\n\r\n" . self::dotStuff($bodyHtml) . "\r\n.\r\n";
	}

	private static function dotStuff(string $body): string
	{
		$body = str_replace(["\r\n", "\r"], "\n", $body);
		$lines = explode("\n", $body);
		$fixed = [];

		foreach ($lines as $line) {
			if (isset($line[0]) && $line[0] === '.') {
				$line = '.' . $line;
			}
			$fixed[] = $line;
		}

		return implode("\r\n", $fixed);
	}

	private static function command($socket, ?string $command, array $codes, string $step): bool
	{
		if ($command !== null) {
			self::write($socket, $command);
		}

		$response = self::read($socket);

		if (!self::expect($response, $codes)) {
			$detail = trim(preg_replace('/\s+/', ' ', $response));
			self::$lastError = $step . ' hatası' . ($detail !== '' ? ': ' . $detail : '');
			return false;
		}

		return true;
	}

	private static function write($socket, string $command): void
	{
		fwrite($socket, $command . "\r\n");
	}

	private static function read($socket): string
	{
		$response = '';

		while ($line = fgets($socket, 515)) {
			$response .= $line;
			if (isset($line[3]) && $line[3] === ' ') {
				break;
			}
		}

		return $response;
	}

	private static function expect(string $response, array $codes): bool
	{
		if ($response === '') {
			return false;
		}

		$code = (int) substr($response, 0, 3);

		return in_array($code, $codes, true);
	}
}
