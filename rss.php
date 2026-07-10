<?php

require_once __DIR__ . '/config/install_gate.php';

if (!fshop_is_installed()) {
	http_response_code(503);
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'Kurulum tamamlanmadı.';
	exit;
}

define('IN_SCRIPT', true);
require_once __DIR__ . '/config/connection.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/FShop.php';
require_once __DIR__ . '/core/NewsFeed.php';

header('Content-Type: application/rss+xml; charset=UTF-8');
header('Cache-Control: public, max-age=900');
echo NewsFeed::renderRssXml();
