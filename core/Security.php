<?php

class Security
{
	public static function sanitizeContainerSlug(string $slug): string
	{
		$slug = trim($slug);

		if ($slug === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $slug)) {
			return '';
		}

		return $slug;
	}

	public static function validatePassword(string $password, bool $requireComplexity = true): ?string
	{
		$password = (string) $password;

		if (strlen($password) < 8) {
			return 'Şifre en az 8 karakter olmalı';
		}

		if ($requireComplexity && (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))) {
			return 'Şifre en az bir harf ve bir rakam içermeli';
		}

		return null;
	}

	public static function isSafeOutboundUrl(string $url): bool
	{
		$url = trim($url);

		if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}

		$scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));

		if (!in_array($scheme, ['http', 'https'], true)) {
			return false;
		}

		$host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

		if ($host === '') {
			return false;
		}

		if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
			return false;
		}

		$ips = [];

		if (filter_var($host, FILTER_VALIDATE_IP)) {
			$ips[] = $host;
		} else {
			$records = @dns_get_record($host, DNS_A + DNS_AAAA);

			if (empty($records)) {
				return false;
			}

			foreach ($records as $record) {
				if (!empty($record['ip'])) {
					$ips[] = $record['ip'];
				}

				if (!empty($record['ipv6'])) {
					$ips[] = $record['ipv6'];
				}
			}
		}

		foreach ($ips as $ip) {
			if (self::isBlockedOutboundIp($ip)) {
				return false;
			}
		}

		return true;
	}

	public static function isBlockedOutboundIp(string $ip): bool
	{
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return filter_var(
				$ip,
				FILTER_VALIDATE_IP,
				FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
			) === false;
		}

		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return true;
		}

		$packed = inet_pton($ip);

		if ($packed === false) {
			return true;
		}

		if ($packed === inet_pton('::1')) {
			return true;
		}

		$first = ord($packed[0]);

		if (($first & 0xfe) === 0xfc) {
			return true;
		}

		if ($first === 0xfe && (ord($packed[1]) & 0xc0) === 0x80) {
			return true;
		}

		return false;
	}

	public static function sanitizeSvg(string $content): ?string
	{
		if (stripos($content, '<svg') === false) {
			return null;
		}

		$content = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $content) ?? '';
		$content = preg_replace('/<foreignObject\b[^>]*>.*?<\/foreignObject>/is', '', $content) ?? '';
		$content = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content) ?? '';
		$content = preg_replace('/\b(href|xlink:href)\s*=\s*["\']?\s*javascript:/i', '', $content) ?? '';

		if (preg_match('/<script|javascript:|data:text\/html/i', $content)) {
			return null;
		}

		return $content;
	}
}
