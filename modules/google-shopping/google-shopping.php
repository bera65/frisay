<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
    exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';

class GoogleShoppingModule extends ModuleBase
{
    // ── Kimlik ─────────────────────────────────────────────────────
    public string $name        = 'google-shopping';
    public string $title       = 'Google Shopping Feed';
    public string $version     = '1.0.0';
    public string $description = 'Google Merchant Center için RSS 2.0 / XML ürün feed\'i üretir.';
    public string $author      = 'FShop';

    public array $displayHooks        = [];
    public array $defaultDisplayHooks = [];

    public array $frontStylesheets = [];
    public array $frontScripts     = [];
    public array $adminStylesheets = ['admin.css'];
    public array $adminScripts     = ['admin.js'];

    public array $apiActions = [
        'feed'        => 'api/feed.php',       // /api/module.php?m=google-shopping&action=feed
        'preview'     => 'api/preview.php',    // Admin önizleme (ilk 5 ürün)
        'regenerate'  => 'api/regenerate.php', // Cache yenile
    ];

    // ── Kurulum / Kaldırma ─────────────────────────────────────────
    public function install(): bool
    {
        Settings::set('GSF_ENABLED',         '1');
        Settings::set('GSF_CACHE_TTL',       '360');   // dakika
        Settings::set('GSF_CURRENCY',        'TRY');
        Settings::set('GSF_CONDITION',       'new');
        Settings::set('GSF_BRAND_FALLBACK',  Settings::get('SITE_NAME'));
        Settings::set('GSF_EXCLUDE_CATS',    '');      // virgülle ayrılmış id_category
        Settings::set('GSF_INCLUDE_OUTSTOCK','0');
        Settings::set('GSF_CUSTOM_LABEL_0',  '');
        Settings::set('GSF_FEED_TOKEN',      bin2hex(random_bytes(16)));
        Settings::set('GSF_LAST_REGEN',      '');
        return true;
    }

    public function uninstall(): bool
    {
        $keys = [
            'GSF_ENABLED','GSF_CACHE_TTL','GSF_CURRENCY','GSF_CONDITION',
            'GSF_BRAND_FALLBACK','GSF_EXCLUDE_CATS','GSF_INCLUDE_OUTSTOCK',
            'GSF_CUSTOM_LABEL_0','GSF_FEED_TOKEN','GSF_LAST_REGEN',
        ];
        foreach ($keys as $k) {
            Settings::set($k, '');
        }
        @unlink(self::cachePath());
        return true;
    }

    // ── Admin sayfası ──────────────────────────────────────────────
    public function adminPage(): void
    {
        global $smarty, $adminToken, $domain;

        $flash = '';

        if (Tools::isSubmit('saveGsfSettings')) {
            $postToken = (string) Tools::getValue('token');

            if (hash_equals($adminToken, $postToken)) {
                Settings::set('GSF_ENABLED',          Tools::getValue('gsf_enabled')          ? '1' : '0');
                Settings::set('GSF_CACHE_TTL',        (string)(int) Tools::getValue('gsf_cache_ttl'));
                Settings::set('GSF_CURRENCY',         strtoupper(trim((string) Tools::getValue('gsf_currency'))));
                Settings::set('GSF_CONDITION',        in_array(Tools::getValue('gsf_condition'), ['new','used','refurbished'], true) ? Tools::getValue('gsf_condition') : 'new');
                Settings::set('GSF_BRAND_FALLBACK',   trim(strip_tags((string) Tools::getValue('gsf_brand_fallback'))));
                Settings::set('GSF_EXCLUDE_CATS',     trim((string) Tools::getValue('gsf_exclude_cats')));
                Settings::set('GSF_INCLUDE_OUTSTOCK', Tools::getValue('gsf_include_outstock') ? '1' : '0');
                Settings::set('GSF_CUSTOM_LABEL_0',   trim(strip_tags((string) Tools::getValue('gsf_custom_label_0'))));
                @unlink(self::cachePath());
                $flash = 'Ayarlar kaydedildi. Cache temizlendi.';
            } else {
                $flash = 'Geçersiz istek';
            }
        }

        if (Tools::isSubmit('regenToken')) {
            $postToken = (string) Tools::getValue('token');
            if (hash_equals($adminToken, $postToken)) {
                Settings::set('GSF_FEED_TOKEN', bin2hex(random_bytes(16)));
                @unlink(self::cachePath());
                $flash = 'Feed token yenilendi.';
            }
        }

        $feedToken = Settings::get('GSF_FEED_TOKEN');
        $feedUrl   = rtrim($domain, '/') . '/api/module.php?m=google-shopping&action=feed&token=' . $feedToken;

        $smarty->assign([
            'flash'              => $flash,
            'gsfEnabled'         => Settings::get('GSF_ENABLED'),
            'gsfCacheTtl'        => Settings::get('GSF_CACHE_TTL') ?: '360',
            'gsfCurrency'        => Settings::get('GSF_CURRENCY')  ?: 'TRY',
            'gsfCondition'       => Settings::get('GSF_CONDITION') ?: 'new',
            'gsfBrandFallback'   => Settings::get('GSF_BRAND_FALLBACK'),
            'gsfExcludeCats'     => Settings::get('GSF_EXCLUDE_CATS'),
            'gsfIncludeOutstock' => Settings::get('GSF_INCLUDE_OUTSTOCK'),
            'gsfCustomLabel0'    => Settings::get('GSF_CUSTOM_LABEL_0'),
            'feedUrl'            => $feedUrl,
            'lastRegen'          => Settings::get('GSF_LAST_REGEN') ?: '—',
            'cacheExists'        => file_exists(self::cachePath()),
        ]);
    }

    // ── Feed üretimi ───────────────────────────────────────────────

    /**
     * XML feed döndürür. Cache varsa ve süresi geçmemişse cache'den okur.
     */
    public static function buildFeed(): string
    {
        $cachePath = self::cachePath();
        $ttl       = (int)(Settings::get('GSF_CACHE_TTL') ?: 360) * 60;

        if (file_exists($cachePath) && (time() - filemtime($cachePath)) < $ttl) {
            return (string) file_get_contents($cachePath);
        }

        $xml = self::generateXml();
        file_put_contents($cachePath, $xml);
        Settings::set('GSF_LAST_REGEN', date('d.m.Y H:i'));
        return $xml;
    }

    /**
     * Veritabanından tüm ürünleri çekip Google RSS 2.0 XML üretir.
     */
    public static function generateXml(): string
    {
        $domain       = rtrim((string) Settings::get('DOMAIN'), '/');
        $siteName     = Settings::get('SITE_NAME') ?: 'FShop';
        $currency     = Settings::get('GSF_CURRENCY')      ?: 'TRY';
        $condition    = Settings::get('GSF_CONDITION')     ?: 'new';
        $brandDefault = Settings::get('GSF_BRAND_FALLBACK') ?: $siteName;
        $inclOutstock = Settings::get('GSF_INCLUDE_OUTSTOCK') === '1';
        $customLabel0 = Settings::get('GSF_CUSTOM_LABEL_0');

        $excludeCatIds = array_filter(
            array_map('intval', explode(',', Settings::get('GSF_EXCLUDE_CATS')))
        );

        $products = self::fetchProducts($inclOutstock, $excludeCatIds);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // <rss>
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $dom->appendChild($rss);

        // <channel>
        $channel = $dom->createElement('channel');
        $rss->appendChild($channel);

        self::addTextNode($dom, $channel, 'title',       $siteName . ' Ürün Feed\'i');
        self::addTextNode($dom, $channel, 'link',        $domain . '/');
        self::addTextNode($dom, $channel, 'description', $siteName . ' Google Shopping Ürün Kataloğu');

        foreach ($products as $p) {
            $item = $dom->createElement('item');

            // ── Zorunlu alanlar ────────────────────────────────────
            self::addGNode($dom, $item, 'id',          (string) $p['id_product']);
            self::addTextNode($dom, $item, 'title',    self::cleanTitle($p['product_name']));
            self::addTextNode($dom, $item, 'link',     self::toAbsoluteUrl($domain, (string) ($p['url'] ?? '')));

            $desc = !empty($p['description_short']) ? $p['description_short'] : $p['description'] ?? $p['product_name'];
            self::addTextNode($dom, $item, 'description', self::stripHtml($desc));

            self::addGNode($dom, $item, 'image_link',  self::toAbsoluteUrl($domain, (string) ($p['image_url'] ?? '')));
            self::addGNode($dom, $item, 'availability', $p['in_stock'] ? 'in_stock' : 'out_of_stock');

            $priceStr = number_format((float)$p['price'], 2, '.', '') . ' ' . $currency;
            self::addGNode($dom, $item, 'price', $priceStr);

            // İndirimli fiyat
            if (!empty($p['old_price']) && (float)$p['old_price'] > (float)$p['price']) {
                self::addGNode($dom, $item, 'sale_price', $priceStr);
                $oldStr = number_format((float)$p['old_price'], 2, '.', '') . ' ' . $currency;
                self::addGNode($dom, $item, 'price', $oldStr); // orijinal fiyatı güncelle
                // Not: sale_price önceden eklendi, price'ı düzeltiyoruz
                // DOM'dan son price node'unu bulalım
                $nodes = $item->getElementsByTagNameNS('http://base.google.com/ns/1.0', 'price');
                if ($nodes->length >= 1) {
                    $nodes->item($nodes->length - 1)->nodeValue = $oldStr;
                }
            }

            self::addGNode($dom, $item, 'condition', $condition);

            // ── Önerilen alanlar ───────────────────────────────────
            $brand = !empty($p['brand_name']) ? $p['brand_name'] : $brandDefault;
            self::addGNode($dom, $item, 'brand', $brand);

            // GTIN / MPN
            if (!empty($p['barcode'])) {
                self::addGNode($dom, $item, 'gtin', preg_replace('/\D/', '', $p['barcode']));
            }
            if (!empty($p['product_code'])) {
                self::addGNode($dom, $item, 'mpn', (string) $p['product_code']);
            }

            // Kategori
            if (!empty($p['category_name'])) {
                self::addGNode($dom, $item, 'product_type', htmlspecialchars($p['category_name'], ENT_XML1));
            }

            // Stok adedi
            if (isset($p['quantity'])) {
                self::addGNode($dom, $item, 'quantity_to_sell_on_google', (string)(int)$p['quantity']);
            }

            // Ek görseller (varsa products tablosunda extra_images JSON alanı)
            if (!empty($p['extra_images'])) {
                $extras = is_array($p['extra_images']) ? $p['extra_images'] : json_decode($p['extra_images'], true);
                if (is_array($extras)) {
                    foreach (array_slice($extras, 0, 9) as $img) {
                        self::addGNode($dom, $item, 'additional_image_link', self::toAbsoluteUrl($domain, (string) $img));
                    }
                }
            }

            // Kargo — Türkiye varsayılanı
            $shipping = $dom->createElementNS('http://base.google.com/ns/1.0', 'g:shipping');
            self::addGNode($dom, $shipping, 'country', 'TR');
            self::addGNode($dom, $shipping, 'service', 'Standart Kargo');
            // Kargo ücreti: settings'den çekmeye çalış, yoksa 0
            $freeMin = (float)(Settings::get('FREE_SHIPPING_MIN') ?: 0);
            $shippingCost = ($freeMin > 0 && (float)$p['price'] >= $freeMin) ? '0.00' : (Settings::get('SHIPPING_COST') ?: '0.00');
            self::addGNode($dom, $shipping, 'price', number_format((float)$shippingCost, 2, '.', '') . ' ' . $currency);
            $item->appendChild($shipping);

            // Custom label
            if ($customLabel0 !== '') {
                self::addGNode($dom, $item, 'custom_label_0', $customLabel0);
            }

            // İndirimli ürünlere otomatik label
            if (!empty($p['old_price']) && (float)$p['old_price'] > (float)$p['price']) {
                self::addGNode($dom, $item, 'custom_label_1', 'indirimli');
            }

            // Stok durumuna göre label
            if (!$p['in_stock']) {
                self::addGNode($dom, $item, 'custom_label_2', 'stok-disi');
            }

            $channel->appendChild($item);
        }

        return $dom->saveXML();
    }

    // ── Yardımcı metodlar ─────────────────────────────────────────

    private static function fetchProducts(bool $inclOutstock, array $excludeCatIds): array
    {
        $sql = '
            SELECT
                p.id_product,
                p.product_name,
                p.product_link,
                p.description,
                p.short_description AS description_short,
                p.price,
                p.old_price,
                p.stock,
                p.stock AS quantity,
                p.barcode,
                p.stock_code AS product_code,
                p.id_category,
                c.category_link,
                c.category_name,
                b.brand_name,
                i.id_image
            FROM products p
            LEFT JOIN categories c ON c.id_category = p.id_category
            LEFT JOIN brands b     ON b.id_brand = p.id_brand
            LEFT JOIN images i     ON i.id_product = p.id_product AND i.cover = 1
            WHERE p.active = 1
        ';

        if (!$inclOutstock) {
            $sql .= ' AND p.stock > 0';
        }

        if (!empty($excludeCatIds)) {
            $placeholders = implode(',', array_fill(0, count($excludeCatIds), '?'));
            $sql .= ' AND p.id_category NOT IN (' . $placeholders . ')';
        }

        $sql .= ' ORDER BY p.id_product ASC';

        $rows = DB::execute($sql, !empty($excludeCatIds) ? array_values($excludeCatIds) : []);
        if (!is_array($rows)) {
            return [];
        }

        return array_map(static function (array $row): array {
            $row = Product::enrich($row);
            $row['quantity'] = (int) ($row['stock'] ?? 0);
            return $row;
        }, $rows);
    }

    private static function addTextNode(DOMDocument $dom, DOMNode $parent, string $tag, string $value): void
    {
        $node = $dom->createElement($tag);
        $node->appendChild($dom->createCDATASection($value));
        $parent->appendChild($node);
    }

    private static function addGNode(DOMDocument $dom, DOMNode $parent, string $tag, string $value): void
    {
        $node = $dom->createElementNS('http://base.google.com/ns/1.0', 'g:' . $tag);
        $node->appendChild($dom->createTextNode($value));
        $parent->appendChild($node);
    }

    private static function stripHtml(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim((string) $text);
    }

    private static function cleanTitle(string $title): string
    {
        $title = strip_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return mb_substr(trim($title), 0, 150);
    }

    private static function toAbsoluteUrl(string $domain, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return rtrim($domain, '/') . '/';
        }

        if (preg_match('~^https?://~i', $value)) {
            return $value;
        }

        return rtrim($domain, '/') . '/' . ltrim($value, '/');
    }

    public static function cachePath(): string
    {
        return dirname(__DIR__, 2) . '/cache/google_shopping_feed.xml';
    }

    public function renderDisplayHook(string $hook, array $context = []): ?string
    {
        return null;
    }
}
