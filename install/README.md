# FShop Kurulum Rehberi

Bu klasör, FShop e-ticaret altyapısının sıfırdan kurulumu için gerekli dosyaları içerir.

## Gereksinimler

| Bileşen | Minimum |
|---------|---------|
| PHP | 7.4+ |
| MySQL / MariaDB | 5.7+ |
| Apache | `mod_rewrite` açık |
| PHP eklentileri | `pdo_mysql`, `mbstring`, `gd` |

Yazılabilir dizinler:

- `config/` — `env.php` ve kurulum kilidi
- `cache/` — Smarty önbelleği
- `img/products/` — ürün görselleri

## Hızlı kurulum (yerel WAMP/XAMPP)

1. Projeyi web köküne kopyalayın (ör. `C:\wamp64\www\fshop\`).
2. MySQL'de boş bir veritabanı oluşturun (`fshop`).
3. Tarayıcıda açın: `http://localhost/fshop/install/`
4. Sihirbaz adımlarını izleyin:
   - **Gereksinimler** — eksik varsa PHP/izinleri düzeltin
   - **Veritabanı** — host, ad, kullanıcı, şifre
   - **Site & Admin** — site adı, URL, tema, mağaza dili, admin dili, yönetici hesabı
   - **Tamamlandı** — mağaza ve admin linkleri
5. Admin paneline giriş yapın: `/admin/`

### Alt klasör kurulumu

Site `http://localhost/fshop/` gibi bir alt dizinde çalışıyorsa kurulumda **RewriteBase** değerini `/fshop/` olarak bırakın. Kurulum `.htaccess` dosyasını otomatik günceller.

## Kurulum dosyaları

| Dosya | Açıklama |
|-------|----------|
| `index.php` | 4 adımlı kurulum sihirbazı |
| `schema.sql` | Tam veritabanı şeması (tablolar + varsayılan ayarlar) |
| `seed_demo.sql` | Demo ürünler, kategoriler ve örnek veriler |
| `assets/install.css` | Sihirbaz arayüzü |
| `patch_*.sql` | Mevcut kurulumları güncellemek için (yeni kurulumda gerekmez) |

## Kurulum sonrası varsayılanlar

Sihirbaz şu ayarları yazar:

- **Tema:** Blue (önerilen)
- **Mağaza dili:** Türkçe (`DEFAULT_LANG=tr`)
- **Admin dili:** Türkçe (`ADMIN_DEFAULT_LANG=tr`)
- **Diller:** `tr,en`
- **Para birimi:** TRY (demo veride USD/EUR ürünleri de olabilir)

`config/env.php` otomatik oluşturulur. Canlı sunucuda referans için `config/env.example.php` dosyasına bakın.

## Güvenlik

Kurulum tamamlandıktan sonra:

1. `install/` klasörüne web erişimini kapatın. `install/.htaccess` içindeki yorum satırını açarak Apache'de erişimi engelleyebilirsiniz.
2. `config/env.php` dosyasını yedekleyin; bu dosya git'e eklenmemelidir.
3. Admin şifresini güçlü tutun (en az 8 karakter).
4. Canlı ortamda `APP_DEBUG=false` kullanın.

## Sıfırdan yeniden kurulum

1. `config/env.php` dosyasını silin veya yeniden adlandırın
2. İsteğe bağlı: `config/installed.lock` dosyasını silin
3. Veritabanını boşaltın veya yeni bir DB oluşturun
4. `/install/` adresine tekrar gidin

## Döviz fiyat güncelleme (cron)

Dövizli ürün fiyatları için kurulum sonunda gösterilen cron URL'sini sunucunuza ekleyin (ör. saatte bir):

```
GET /api/cron.php?action=currency&token=SHOP_TOKEN
```

`SHOP_TOKEN` değeri `settings` tablosunda ve kurulum çıktısında yer alır.

## Sorun giderme

| Sorun | Çözüm |
|-------|-------|
| 404 / sayfa açılmıyor | Apache `mod_rewrite` ve `AllowOverride All` kontrol edin |
| Beyaz sayfa | `config/env.php` içinde geçici `APP_DEBUG=true` |
| Veritabanı hatası | Host, port, kullanıcı yetkilerini kontrol edin |
| Görseller yüklenmiyor | `img/products/` yazılabilir mi? GD yüklü mü? |
| Kurulum tekrar çalışmıyor | `config/env.php` mevcut — silmeden sihirbaz açılmaz |

## Manuel kurulum (geliştiriciler)

Sihirbaz kullanmadan:

```bash
cp config/env.example.php config/env.php
# env.php içinde DB bilgilerini düzenleyin
mysql -u root -p fshop < install/schema.sql
mysql -u root -p fshop < install/seed_demo.sql   # isteğe bağlı
```

Ardından `.htaccess` içindeki `RewriteBase` değerini ortamınıza göre ayarlayın.

---

Daha fazla bilgi: proje kökündeki `AGENTS.md` ve `AI_CONTEXT.md`.
