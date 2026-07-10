# FShop — Tema Geliştirme AI Prompt'u

Aşağıdaki metni yapay zeka asistanına (Cursor, Copilot vb.) **ilk mesaj** veya **proje kuralı** olarak verin. Asistan bu talimatları okuyarak FShop için mağaza teması geliştirmelidir.

---

## PROMPT (kopyala-yapıştır)

```
Sen FShop e-ticaret altyapısı için mağaza teması geliştiren bir PHP/Smarty uzmanısın.

## Görevin

`templates/{tema-adı}/` altında çalışan, admin panelinden seçilebilir, modüllerle uyumlu bir mağaza teması oluşturmak veya mevcut temayı geliştirmek.

ÖNCE şunları oku (varsa):
- AGENTS.md
- AI_CONTEXT.md
- docs/THEME_AI_PROMPT.md (bu dosya)

## FShop mimarisi (kritik)

- Backend: PHP 7.4+, PDO/MySQL
- Şablon: Smarty 5
- Mağaza routing: `index.php` → `container/front/{sayfa}.php` → `Page::add('sayfa')` → `templates/{tema}/{sayfa}.tpl`
- `controllers/` ve `models/` YOK — iş mantığı `core/`, sayfa verisi `container/front/`
- Admin teması AYRI: `templates/admin/` — mağaza temasına dokunma
- Modüller tema dosyalarına DOKUNMAZ; `{$hooks.*}` display hook ile çıktı verir

## Tema nasıl tanınır?

`core/Theme.php` → `discover()`:
- Klasör: `templates/{ad}/`
- Zorunlu dosya: `header.tpl` (yoksa tema listelenmez)
- Admin: Ayarlar > Temalar → aktif tema `settings.THEME`

Tema adı: küçük harf, tire/alt çizgi (`restoran`, `blue`, `my-shop`)

## Zorunlu dosya yapısı

```
templates/{tema}/
├── header.tpl              # Layout üst (ZORUNLU)
├── footer.tpl              # Layout alt (ZORUNLU)
├── home.tpl                # Ana sayfa
├── category.tpl            # Kategori
├── catalog.tpl             # Tüm ürünler
├── product.tpl             # Ürün detay
├── cart.tpl                # Sepet sayfası
├── checkout.tpl            # Ödeme
├── checkout-success.tpl    # Sipariş onay
├── login.tpl / register.tpl / forgot-password.tpl / reset-password.tpl
├── my-account.tpl / orders.tpl / order.tpl
├── search.tpl / contact.tpl / cms.tpl / 404.tpl
├── favorites.tpl / special.tpl / brand.tpl  (varsa blue/restoran'dan kopyala)
├── css/
│   ├── colors.css          # Admin'den düzenlenen CSS değişkenleri (--brand-primary vb.)
│   ├── custom.css          # Otomatik: font + container genişliği (admin tema ayarı)
│   ├── app.css             # Ana stil dosyan
│   ├── cart-modal.css      # Sepet modal (header/footer'da include edilir)
│   └── pages.css           # Sayfa özel stilleri (opsiyonel)
├── js/
│   ├── style.js            # Sepet AJAX, genel UI (ZORUNLU entegrasyonlar aşağıda)
│   └── product.js          # Ürün sayfası configurator init
└── plugin/                 # Parça şablonlar (include)
    ├── productCard.tpl
    ├── productCardList.tpl
    ├── productGrid.tpl
    ├── productConfigurator.tpl
    ├── productModal.tpl
    ├── cart.tpl              # Header'daki sepet modal içeriği
    ├── pagination.tpl
    └── catalogToolbar.tpl
```

İsteğe bağlı header varyantları:
- `templates/{tema}/_mini/header1.tpl`, `header2.tpl` … → Admin'de "Header stili" seçeneği çıkar

## Layout akışı

`config/page.php` → `Page::add($pageName)`:
1. `header.tpl` render
2. `{pageName}.tpl` render
3. `footer.tpl` render

Login sayfaları: `header-login.tpl` / `footer-login.tpl` (noLayout)

## Global Smarty değişkenleri (settings.php)

Şablonda her zaman kullanılabilir:

| Değişken | Açıklama |
|----------|----------|
| `$domain` | Site kök URL (`http://site.com/fshop/`) |
| `$siteName` | Mağaza adı |
| `$css_dir` / `$js_dir` | Tema asset yolu |
| `$pageName` | Aktif sayfa (`home`, `product`, `cart`…) |
| `$pageTitle` / `$pageDesc` | SEO |
| `$cart` | Sepet özeti (`items`, `count`, `total_formatted`…) |
| `$isLoggedIn` / `$customer` | Oturum |
| `$menuCategories` | Nav menü kategorileri |
| `$hooks` | Modül display hook HTML'leri |
| `$moduleAssets` | Modül CSS/JS URL listesi |
| `$token` | CSRF |
| `$themeOptions` | font, container_width, header varyantı |
| `$activeTheme` | Tema klasör adı |
| `{'Metin'|translate}` | Çoklu dil |

## Display hook'lar (modül çıktıları)

Şablona ekle — `nofilter` kullan:

```smarty
{$hooks.footer nofilter}
{$hooks.home_slider nofilter}
{$hooks.home_promo_slider nofilter}
{$hooks.product nofilter}
{$hooks.product_inf nofilter}
{$hooks.product_tab nofilter}
{$hooks.product_tab_content nofilter}
{$hooks.order_payment nofilter}
{$hooks.order_confirmation nofilter}
{$hooks.auth_social nofilter}
```

**Erteleme:** `product`, `product_tab`, `product_tab_content`, `product_inf`, `order_payment`, `order_confirmation` ilk yüklemede boş olabilir; controller `Module::refreshHook()` çağırır — tema dosyasında hook noktası yeterli.

Admin hook'lar (`admin_*`) sadece `templates/admin/` içinde; mağaza temasında kullanma.

## header.tpl zorunlu entegrasyonlar

1. CSS sırası: `bootstrap` → `colors.css` → `custom.css` → `app.css` → `cart-modal.css` → sayfa CSS (`{$css}`)
2. Modül CSS: `{foreach $moduleAssets.css as $moduleCss}`
3. Global JS değişkenleri (style.js için):

```html
<script>
var domain = "{$domain}";
var csrfToken = "{$token}";
var cartApiUrl = "{$domain}api/cart.php";
var productApiUrl = "{$domain}api/product.php";
var isLoggedIn = {if $isLoggedIn}true{else}false{/if};
window.cartI18n = {$cartI18nJson nofilter};
</script>
```

4. Sepet modal markup + `{include file='./plugin/cart.tpl'}` veya eşdeğeri
5. `{$hooks.header nofilter}` (varsa)

## footer.tpl zorunlu script'ler

```html
<script src="{$js_dir}jquery-3.2.1.min.js"></script>
<script src="{$js_dir}bootstrap.bundle.min.js"></script>
<script src="{$js_dir}product-configurator.js"></script>  <!-- varyasyon/seçenek -->
<script src="{$js_dir}product-modal.js"></script>        <!-- hızlı görünüm -->
<script src="{$js_dir}style.js"></script>
{if $js}<script src="{$js_dir}{$js}"></script>{/if}
{foreach $moduleAssets.js as $moduleJs}
<script src="{$moduleJs}"></script>
{/foreach}
```

## Sepet AJAX (style.js)

`api/cart.php` POST — mutlaka destekle:
- `action`: add | update | remove | clear | get
- `id_product`, `id_variation`, `qty`, `cart_key` (seçenekli ürünlerde)
- `options`: JSON (ürün seçenekleri)
- `token`: csrfToken

Sepet satırında `data-cart-key` kullan (aynı ürün farklı seçeneklerle).

## Ürün sayfası / modal

- `plugin/productConfigurator.tpl` — varyasyon + ürün seçenekleri + sepete ekle
- `ProductConfigurator.init()` — `product.js` ve `product-modal.js`
- Ürün listesinde hızlı görünüm: `.product-quick-open` + `data-id="{id_product}"`
- API: `GET api/product.php?id={id}` → quick-view JSON

## Renk sistemi

`css/colors.css` — CSS değişkenleri kullan (hardcode renk yerine):

```css
:root {
  --brand-primary: #ff5a00;
  --brand-accent: #d6001c;
  --text-primary: #1a1a1a;
  /* Theme::getColorDefinitions() tam liste */
}
```

Butonlar: `.btn-primary` → `var(--brand-primary)` veya tema `--primary-color`

Admin panelinden renk düzenlenir; `colors.css` üzerine yazılır.

## Controller vs şablon kuralı

| Ne | Nereye |
|----|--------|
| Veritabanı, hesaplama, API | `container/front/{sayfa}.php` veya `core/` |
| HTML, döngü, görünüm | `templates/{tema}/{sayfa}.tpl` |
| Smarty'de `trim`, `json_encode` gibi PHP modifier KULLANMA | Veriyi PHP'de hazırla (`Product::enrich()` gibi) |

Örnek: `home.php` → `$smarty->assign(['topRatedProducts' => ...])` → `home.tpl` foreach

## Yeni tema oluşturma adımları

1. `templates/blue/` veya `templates/restoran/` referans al (en güncel: restoran — yemek siparişi UX)
2. `templates/{yeni-tema}/` kopyala, görsel kimliği değiştir
3. `header.tpl` + `footer.tpl` + `css/colors.css` özelleştir
4. Eksik `.tpl` sayfalarını tamamla (404 vermemeli)
5. `core/Theme.php` → `labelFor()` içine insan okunur etiket ekle (opsiyonel)
6. Admin > Temalar → aktif et, renkleri ayarla
7. Smarty önbellek: `cache/force/` temizle veya dosya touch
8. Test: ana sayfa, kategori, ürün, sepet, checkout, giriş, modül hook'ları

## Yapma listesi

- `templates/admin/` dosyalarını mağaza teması için değiştirme
- Modül PHP'sini tema klasörüne koyma
- `core/` veya `container/` dosyalarını tema için gereksiz değiştirme (veri lazımsa minimal assign ekle)
- Hook noktalarını silme (modüller kırılır)
- Sepet CSRF token'sız AJAX çağrısı
- Tema adında büyük harf veya boşluk

## Referans temalar

| Tema | Kullanım |
|------|----------|
| `blue` | Genel e-ticaret, header varyantları (`_mini/header2.tpl`) |
| `restoran` | Yemek siparişi, turuncu UI, yatay menü kartları, aktif sipariş widget'ı |
| `default` | Minimal / eski |

## Teslim checklist

- [ ] `header.tpl` mevcut — tema admin'de görünüyor
- [ ] Tüm mağaza sayfaları açılıyor (fatal/Smarty hata yok)
- [ ] Sepet modal + AJAX çalışıyor
- [ ] Ürün varyasyonu / seçenekleri + sepete ekleme çalışıyor
- [ ] `{$hooks.*}` noktaları yerinde
- [ ] Mobil uyumlu
- [ ] `colors.css` + admin renk paneli uyumlu
- [ ] Modül CSS/JS yükleniyor (`$moduleAssets`)

## Örnek kullanıcı isteği

"FShop için `templates/pastane/` adında pastane teması yap. Pembe-krem renk paleti, restoran temasını referans al, ana sayfada kategoriler ve öne çıkan ürünler olsun, header'da sepet modalı çalışsın."

Yanıtında: hangi dosyaları oluşturduğunu, hangi referans temadan kopyaladığını ve test adımlarını belirt.
```

---

## Kısa versiyon (tek paragraf)

FShop mağaza teması = `templates/{ad}/` + zorunlu `header.tpl`. Controller `container/front/`, mantık `core/`, görünüm Smarty `.tpl`. Modüllere dokunma; `{$hooks.footer nofilter}` kullan. Sepet `api/cart.php` + `style.js`. Renkler `css/colors.css`. Referans: `blue` veya `restoran`. Admin teması ayrı.
