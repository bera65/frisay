<?php

class Mail
{
	private static string $lastError = '';

	public static function getLastError(): string
	{
		return self::$lastError;
	}

	public static function usesSmtp(): bool
	{
		return Settings::get('MAIL_DRIVER') === 'smtp';
	}

	public static function isConfigured(): bool
	{
		if (self::usesSmtp()) {
			return trim(Settings::get('SMTP_HOST')) !== ''
				&& trim(Settings::get('SMTP_USER')) !== ''
				&& Settings::get('SMTP_PASS') !== '';
		}

		return trim(self::getFromEmail()) !== '';
	}

	public static function send(string $to, string $subject, string $bodyHtml): bool
	{
		self::$lastError = '';
		$to = trim($to);

		if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
			self::$lastError = 'Geçersiz alıcı e-posta adresi';
			return false;
		}

		$body = self::wrapTemplate($bodyHtml);

		if (self::usesSmtp()) {
			if (!self::isConfigured()) {
				self::$lastError = 'SMTP ayarları eksik';
				return false;
			}

			$sent = SmtpMailer::send($to, $subject, $body);
			if (!$sent) {
				self::$lastError = SmtpMailer::getLastError() ?: 'SMTP gönderimi başarısız';
			}

			return $sent;
		}

		return self::sendViaPhpMail($to, $subject, $body);
	}

	private static function sendViaPhpMail(string $to, string $subject, string $bodyHtml): bool
	{
		$from = self::getFromEmail();

		if ($from === '') {
			self::$lastError = 'Gönderen e-posta tanımlı değil (İletişim e-postası alanını doldurun)';
			return false;
		}

		$siteName = Settings::get('SITE_NAME') ?: 'FShop';
		$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
		$headers = [
			'MIME-Version: 1.0',
			'Content-type: text/html; charset=utf-8',
			'From: ' . self::encodeHeader($siteName) . ' <' . $from . '>',
			'Reply-To: ' . $from,
		];

		$sent = @mail($to, $encodedSubject, $bodyHtml, implode("\r\n", $headers));

		if (!$sent) {
			self::$lastError = 'PHP mail() gönderimi başarısız. Sunucunuzda mail() etkin mi kontrol edin.';
		}

		return $sent;
	}

	private static function getFromEmail(): string
	{
		if (self::usesSmtp()) {
			return trim(Settings::get('SMTP_FROM_EMAIL'))
				?: trim(Settings::get('SMTP_USER'));
		}

		return trim(Settings::get('CONTACT_EMAIL'));
	}

	public static function sendWelcome(string $to, string $fullName): bool
	{
		global $domain;

		$siteName = Settings::get('SITE_NAME') ?: 'FShop';
		$name = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
		$loginUrl = htmlspecialchars(rtrim($domain, '/') . '/login', ENT_QUOTES, 'UTF-8');

		$body = '<h2 style="margin:0 0 16px;">Hoş geldiniz!</h2>'
			. '<p>Merhaba <strong>' . $name . '</strong>,</p>'
			. '<p>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . ' ailesine katıldığınız için teşekkür ederiz. Hesabınız başarıyla oluşturuldu.</p>'
			. '<p>Siparişlerinizi takip etmek ve alışverişe devam etmek için hesabınıza giriş yapabilirsiniz.</p>'
			. '<p style="margin:24px 0;"><a href="' . $loginUrl . '" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:6px;">Giriş Yap</a></p>';

		return self::send($to, $siteName . ' - Hoş geldiniz', $body);
	}

	public static function sendPasswordReset(string $to, string $fullName, string $resetUrl): bool
	{
		$siteName = Settings::get('SITE_NAME') ?: 'FShop';
		$name = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
		$url = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

		$body = '<h2 style="margin:0 0 16px;">Şifre Sıfırlama</h2>'
			. '<p>Merhaba <strong>' . $name . '</strong>,</p>'
			. '<p>Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın. Bu bağlantı 1 saat geçerlidir.</p>'
			. '<p style="margin:24px 0;"><a href="' . $url . '" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:6px;">Şifremi Sıfırla</a></p>'
			. '<p style="font-size:13px;color:#666;">Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz.</p>'
			. '<p style="font-size:12px;color:#999;word-break:break-all;">' . $url . '</p>';

		return self::send($to, $siteName . ' - Şifre Sıfırlama', $body);
	}

	private static function encodeHeader(string $text): string
	{
		return '=?UTF-8?B?' . base64_encode($text) . '?=';
	}

	private static function wrapTemplate(string $body): string
	{
		$siteName = htmlspecialchars(Settings::get('SITE_NAME') ?: 'FShop', ENT_QUOTES, 'UTF-8');

		return '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;line-height:1.5;color:#222;">'
			. '<div style="max-width:560px;margin:0 auto;padding:24px;">'
			. $body
			. '<p style="margin-top:32px;font-size:12px;color:#888;">' . $siteName . '</p>'
			. '</div></body></html>';
	}
}
