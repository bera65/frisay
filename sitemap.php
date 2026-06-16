<?php
/**
 * Basit sitemap — DOMAIN ayarına göre URL üretir.
 */
define('IN_SCRIPT', true);
require_once dirname(__FILE__) . '/config/settings.php';

header('Content-Type: application/xml; charset=utf-8');

$base = rtrim(Settings::get('DOMAIN'), '/');
$urls = [
	['loc' => $base . '/', 'priority' => '1.0'],
	['loc' => $base . '/special', 'priority' => '0.8'],
	['loc' => $base . '/contact', 'priority' => '0.6'],
];

foreach (Cms::getPages() as $slug => $page) {
	$urls[] = ['loc' => $base . '/' . $slug, 'priority' => '0.5'];
}

foreach (Category::getMenuList() as $cat) {
	$urls[] = ['loc' => Category::getUrl($cat), 'priority' => '0.7'];
}

$products = Product::getActiveList(null, 5000);
foreach ($products as $p) {
	$urls[] = ['loc' => $p['url'], 'priority' => '0.6'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $u) {
	echo "  <url>\n";
	echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
	echo '    <priority>' . $u['priority'] . "</priority>\n";
	echo "  </url>\n";
}

echo "</urlset>\n";
