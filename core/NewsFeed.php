<?php

class NewsFeed
{
	private const REMOTE_URL = 'https://frisay.com/rss.xml';
	private const CACHE_FILE = 'news-feed.json';
	private const CACHE_TTL = 3600;

	/** @return array<int, array{title: string, link: string, description: string, date: string, date_label: string, category: string}> */
	public static function getDashboardItems(int $limit = 6): array
	{
		$cached = self::readCache();

		if ($cached !== null) {
			return array_slice($cached, 0, $limit);
		}

		$items = self::fetchRemote(self::REMOTE_URL);

		if ($items === [] && class_exists('Settings', false)) {
			$localDomain = rtrim((string) Settings::get('DOMAIN'), '/');

			if ($localDomain !== '') {
				$items = self::fetchRemote($localDomain . '/rss.xml');
			}
		}

		if ($items === []) {
			$items = self::loadLocalItems();
		}

		if ($items === []) {
			return [];
		}

		self::writeCache($items);

		return array_slice($items, 0, $limit);
	}

	/** @return array<int, array{title: string, link: string, description: string, pubDate: string, category: string}> */
	public static function loadLocalItems(): array
	{
		$path = dirname(__DIR__) . '/data/frisay-news.json';

		if (!is_file($path)) {
			return [];
		}

		$raw = file_get_contents($path);

		if ($raw === false) {
			return [];
		}

		$decoded = json_decode($raw, true);

		if (!is_array($decoded)) {
			return [];
		}

		$items = [];

		foreach ($decoded as $row) {
			if (!is_array($row)) {
				continue;
			}

			$items[] = [
				'title' => (string) ($row['title'] ?? ''),
				'link' => (string) ($row['link'] ?? 'https://frisay.com'),
				'description' => (string) ($row['description'] ?? ''),
				'pubDate' => (string) ($row['pubDate'] ?? date('c')),
				'category' => (string) ($row['category'] ?? 'Haber'),
			];
		}

		return self::normalizeItems($items);
	}

	/** @return array<int, array{title: string, link: string, description: string, pubDate: string, category: string}> */
	public static function fetchRemote(string $url): array
	{
		$xml = self::httpGet($url);

		if ($xml === '') {
			return [];
		}

		return self::parseRssXml($xml);
	}

	public static function renderRssXml(): string
	{
		$items = self::loadLocalItems();
		$domain = Settings::get('DOMAIN') ?: 'https://frisay.com/';
		$siteName = Settings::get('SITE_NAME') ?: 'FShop';
		$channelLink = rtrim($domain, '/');
		$buildDate = gmdate('D, d M Y H:i:s') . ' GMT';

		$lines = [
			'<?xml version="1.0" encoding="UTF-8"?>',
			'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
			'<channel>',
			'<title>' . self::xmlEscape('FriSay ' . FShop::NAME . ' Haberleri') . '</title>',
			'<link>' . self::xmlEscape($channelLink) . '</link>',
			'<description>' . self::xmlEscape(FShop::NAME . ' sürüm, modül ve özellik duyuruları') . '</description>',
			'<language>tr</language>',
			'<generator>' . self::xmlEscape(FShop::fullName()) . '</generator>',
			'<lastBuildDate>' . $buildDate . '</lastBuildDate>',
			'<atom:link href="' . self::xmlEscape($channelLink . '/rss.xml') . '" rel="self" type="application/rss+xml" />',
		];

		foreach ($items as $item) {
			$lines[] = '<item>';
			$lines[] = '<title>' . self::xmlEscape($item['title']) . '</title>';
			$lines[] = '<link>' . self::xmlEscape($item['link']) . '</link>';
			$lines[] = '<guid isPermaLink="true">' . self::xmlEscape($item['link']) . '</guid>';
			$lines[] = '<description>' . self::xmlEscape($item['description']) . '</description>';
			$lines[] = '<category>' . self::xmlEscape($item['category']) . '</category>';
			$lines[] = '<pubDate>' . self::formatRssDate($item['date'] ?? '') . '</pubDate>';
			$lines[] = '</item>';
		}

		$lines[] = '</channel>';
		$lines[] = '</rss>';

		return implode("\n", $lines) . "\n";
	}

	/** @param array<int, array<string, string>> $items */
	private static function normalizeItems(array $items): array
	{
		$normalized = [];

		foreach ($items as $item) {
			$title = trim((string) ($item['title'] ?? ''));

			if ($title === '') {
				continue;
			}

			$timestamp = strtotime((string) ($item['pubDate'] ?? '')) ?: time();

			$normalized[] = [
				'title' => $title,
				'link' => trim((string) ($item['link'] ?? 'https://frisay.com')) ?: 'https://frisay.com',
				'description' => trim((string) ($item['description'] ?? '')),
				'date' => date('c', $timestamp),
				'date_label' => date('d.m.Y', $timestamp),
				'category' => trim((string) ($item['category'] ?? 'Haber')) ?: 'Haber',
			];
		}

		usort($normalized, static function (array $a, array $b): int {
			return strcmp($b['date'], $a['date']);
		});

		return $normalized;
	}

	/** @return array<int, array{title: string, link: string, description: string, date: string, date_label: string, category: string}>|null */
	private static function readCache(): ?array
	{
		$path = self::cachePath();

		if (!is_file($path)) {
			return null;
		}

		if ((time() - filemtime($path)) > self::CACHE_TTL) {
			return null;
		}

		$raw = file_get_contents($path);

		if ($raw === false) {
			return null;
		}

		$decoded = json_decode($raw, true);

		return is_array($decoded) ? $decoded : null;
	}

	/** @param array<int, array<string, string>> $items */
	private static function writeCache(array $items): void
	{
		$dir = dirname(self::cachePath());

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		$json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

		if ($json !== false) {
			file_put_contents(self::cachePath(), $json);
		}
	}

	private static function cachePath(): string
	{
		return dirname(__DIR__) . '/cache/' . self::CACHE_FILE;
	}

	private static function httpGet(string $url): string
	{
		if (function_exists('curl_init')) {
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_TIMEOUT => 8,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_USERAGENT => FShop::fullName() . ' RSS Reader',
			]);
			$response = curl_exec($ch);
			$code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if ($response !== false && $code >= 200 && $code < 300) {
				return (string) $response;
			}
		}

		$context = stream_context_create([
			'http' => [
				'timeout' => 8,
				'user_agent' => FShop::fullName() . ' RSS Reader',
			],
		]);
		$response = @file_get_contents($url, false, $context);

		return is_string($response) ? $response : '';
	}

	/** @return array<int, array{title: string, link: string, description: string, pubDate: string, category: string}> */
	private static function parseRssXml(string $xml): array
	{
		libxml_use_internal_errors(true);
		$doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		libxml_clear_errors();

		if ($doc === false || !isset($doc->channel->item)) {
			return [];
		}

		$items = [];

		foreach ($doc->channel->item as $item) {
			$category = '';

			if (isset($item->category)) {
				$category = trim((string) $item->category);
			}

			$items[] = [
				'title' => trim((string) $item->title),
				'link' => trim((string) $item->link),
				'description' => trim(strip_tags((string) $item->description)),
				'pubDate' => trim((string) ($item->pubDate ?? '')),
				'category' => $category !== '' ? $category : 'Haber',
			];
		}

		return self::normalizeItems($items);
	}

	private static function formatRssDate(string $value): string
	{
		$timestamp = strtotime($value);

		if ($timestamp === false) {
			$timestamp = time();
		}

		return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
	}

	private static function xmlEscape(string $value): string
	{
		return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
	}
}
