<?php
require_once __DIR__ . '/app.php';
App::boot();

define('_DB_SERVER_', App::env('DB_HOST', 'localhost'));
define('_DB_NAME_', App::env('DB_NAME', 'fshop'));
define('_DB_USER_', App::env('DB_USER', 'root'));
define('_DB_PASSWD_', App::env('DB_PASS', ''));
define('_MYSQL_ENGINE_', 'InnoDB');

$user = _DB_USER_;
$pass = _DB_PASSWD_;

try {
	$db = new PDO(
		'mysql:host=' . _DB_SERVER_ . ';dbname=' . _DB_NAME_,
		$user,
		$pass,
		[
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]
	);
	$db->exec("SET NAMES utf8mb4");
	$db->exec("SET CHARACTER SET utf8mb4");
	$db->exec("SET COLLATION_CONNECTION = 'utf8mb4_unicode_ci'");
} catch (PDOException $e) {
	if (App::isDebug()) {
		echo 'Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
	} else {
		error_log('DB connection failed: ' . $e->getMessage());
		http_response_code(503);
		echo 'Geçici bir sorun oluştu. Lütfen daha sonra tekrar deneyin.';
	}
	exit;
}
