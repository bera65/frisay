<?php

/**
 * Cache yenile endpoint'i
 * URL: /api/module.php?m=google-shopping&action=regenerate
 * Admin panelinden AJAX ile çağrılır.
 */

if (!defined('IN_SCRIPT')) {
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Admin session kontrolü
if (empty($_SESSION['id_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// Admin CSRF: admin panel token'ı (admin_csrf_token) esas alınır.
$token = Tools::getValue('token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
$sessionToken = (string) ($_SESSION['admin_csrf_token'] ?? ($_SESSION['csrf_token'] ?? ''));

if ($sessionToken === '' || !hash_equals($sessionToken, (string) $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
    exit;
}

try {
    @unlink(GoogleShoppingModule::cachePath());
    $xml = GoogleShoppingModule::buildFeed();

    // Ürün sayısını say
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $count = $dom->getElementsByTagName('item')->length;

    echo json_encode([
        'success'      => true,
        'message'      => 'Feed yenilendi. ' . $count . ' ürün işlendi.',
        'product_count'=> $count,
        'generated_at' => Settings::get('GSF_LAST_REGEN'),
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
