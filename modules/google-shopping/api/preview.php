<?php

/**
 * Admin önizleme endpoint'i — ilk 5 ürünü JSON olarak döner
 * URL: /api/module.php?m=google-shopping&action=preview
 * Yalnızca admin oturumunda çağrılmalıdır.
 */

if (!defined('IN_SCRIPT')) {
    exit;
}

header('Content-Type: application/json; charset=utf-8');

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
    // Geçici olarak feed'i üret, parse et, ilk 5 item'ı döndür
    $xml = GoogleShoppingModule::generateXml();

    $dom = new DOMDocument();
    $dom->loadXML($xml);

    $items   = $dom->getElementsByTagName('item');
    $preview = [];

    $limit = min(5, $items->length);
    for ($i = 0; $i < $limit; $i++) {
        $item    = $items->item($i);
        $row     = [];
        foreach ($item->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $localName = $child->localName;
            $row[$localName] = $child->nodeValue;
        }
        $preview[] = $row;
    }

    echo json_encode([
        'success'      => true,
        'total'        => $items->length,
        'preview'      => $preview,
        'generated_at' => date('d.m.Y H:i:s'),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
