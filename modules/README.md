# FShop Modül Sistemi

## Admin akışı

1. **Modüller** listesinde modül satırı görünür
2. Kurulu değilse sağda **Kur**
3. Kuruluysa **Yapılandır** → otomatik `/admin/module-{ad}` (ör. `/admin/module-whatsapp`)

Slug, başlık ve route **otomatik** oluşur; `$adminPages` tanımlamaya gerek yok.

## Yapılandırma ekranı

Her modülde:

| Dosya / metod | Görev |
|---------------|--------|
| `adminPage()` | Form işlemleri, veritabanı, Smarty değişkenleri |
| `assets/templates/admin/admin.tpl` | Yapılandırma arayüzü (modül içinde) |
| `logo.png` | Modül listesinde logo (modül kök dizini) |

```php
class OrnekModule extends ModuleBase
{
    public string $name = 'ornek';
    public string $title = 'Örnek Modül';

    public function adminPage(): void
    {
        global $smarty;
        $smarty->assign('ayar', Settings::get('ORNEK_AYAR'));
    }
}
```

`assets/templates/admin/admin.tpl`:

```smarty
<div class="admin-panel">
  <p>Ayar: {$ayar|escape}</p>
</div>
```

Otomatik URL: `/admin/module-ornek` · Başlık: `Örnek Modül — Yapılandır`

## Display hook (şablonda görünür alan)

| Hook | Şablonda |
|------|----------|
| `footer` | `{$hooks.footer}` |
| `header` | `{$hooks.header}` |
| `home` | `{$hooks.home}` |
| `product` | `{$hooks.product}` — ürün sayfasında, sayfa yüklenince bağlam ile render |

```php
public array $displayHooks = ['footer' => 'Footer alanı'];
public array $defaultDisplayHooks = ['footer'];

public function renderDisplayHook(string $hook): ?string
{
    if ($hook !== 'footer') {
        return null;
    }

    $html = $this->renderFrontTemplate('footer', [
        'ornekDegisken' => 'değer',
    ]);

    return $html !== '' ? $html : null;
}
```

Şablon: `assets/templates/front/footer.tpl` (HTML burada, PHP'de değil)

## Klasör yapısı

```
modules/ornek/
  ornek.php
  logo.png              ← modül listesi logosu
  install.sql
  uninstall.sql
  assets/
    templates/
      admin/
        admin.tpl            ← admin yapılandırma
      front/
        footer.tpl           ← mağaza hook çıktısı (renderFrontTemplate)
    css/
    js/
  api/...
```

Tema dizinine (`templates/admin/`) dosya eklemeye gerek yok.

## CSS ve JS

Dosyalar modül içinde kalır; tema dizinine eklenmez.

```
modules/ornek/assets/
  css/
    front.css       ← mağaza
    ornek.css       ← modüle özel stiller
    admin.css       ← sadece yapılandırma ekranı
  js/
    front.js
    admin.js
```

Modül sınıfında hangi dosyaların yükleneceğini belirtin:

```php
public array $frontStylesheets = ['front.css', 'ornek.css'];
public array $frontScripts = ['ornek.js'];
public array $adminStylesheets = ['admin.css'];
public array $adminScripts = ['admin.js'];
```

| Dizi | Nerede yüklenir |
|------|-----------------|
| `frontStylesheets` / `frontScripts` | Mağaza — `header.tpl` / `footer.tpl` |
| `adminStylesheets` / `adminScripts` | `/admin/module-{ad}` yapılandırma sayfası |

Diziler **boş bırakılırsa** ilgili klasördeki tüm `.css` / `.js` dosyaları otomatik yüklenir (newsletter `newsletter.js` gibi).

URL: `/modules/{ad}/assets/css/whatsapp.css`

## Dahili hook'lar

`boot()` içinde `Module::registerHook()`:

| Hook | Ne zaman |
|------|----------|
| `smarty.assign` | Smarty değişkenleri |
| `head.assets` | CSS/JS |
| `order.placed` | Sipariş sonrası |

## API

`POST /api/module.php?m={modul}&action={islem}`
