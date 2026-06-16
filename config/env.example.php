<?php
/**
 * Ortam ayarları şablonu.
 * Canlı sunucuda: cp config/env.example.php config/env.php
 * Ardından değerleri düzenleyin.
 */
return [
	// local = geliştirme, production = canlı
	'APP_ENV' => 'production',
	'APP_DEBUG' => false,

	'DB_HOST' => 'localhost',
	'DB_NAME' => 'fshop',
	'DB_USER' => 'fshop_user',
	'DB_PASS' => 'GÜÇLÜ_ŞİFRE_BURAYA',

	// Apache RewriteBase (.htaccess) — kök dizinde / , alt klasörde /fshop/
	'REWRITE_BASE' => '/',
];
