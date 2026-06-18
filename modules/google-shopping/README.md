# Google Shopping Feed Modülü

FShop için Google Merchant Center uyumlu XML ürün feed'i üretir.

## Kurulum

1. `modules/google-shopping/` klasörünü sunucuya yükleyin.
2. Admin → Modüller → **Google Shopping Feed** → **Kur**
3. Admin → Modüller → **Yapılandır**

## Feed URL

Yapılandır ekranında görünen URL'yi Google Merchant Center'a ekleyin:

```
/api/module.php?m=google-shopping&action=feed&token=YOUR_TOKEN
```

## Google Merchant Center Entegrasyonu

1. merchants.google.com → Ürünler → Feed'ler → **+**
2. **Zamanlanmış Getirme** seçin
3. Feed URL'yi yapıştırın
4. Dil: **Türkçe**, Ülke: **Türkiye**, Para birimi: **TRY**
5. Getirme: **Günlük**

## XML Çıktı Alanları

### Zorunlu (Google şartı)
| Alan | Açıklama |
|------|----------|
| `g:id` | Ürün ID |
| `title` | Ürün adı (CDATA, max 150 kar.) |
| `description` | HTML temizlenmiş açıklama |
| `link` | Ürün sayfası URL |
| `g:image_link` | Ana görsel URL |
| `g:availability` | `in_stock` / `out_of_stock` |
| `g:price` | Fiyat + para birimi (ör. `299.90 TRY`) |
| `g:condition` | `new` / `used` / `refurbished` |

### Önerilen
| Alan | Açıklama |
|------|----------|
| `g:brand` | Marka adı veya varsayılan |
| `g:gtin` | Barkod (varsa) |
| `g:mpn` | Ürün kodu (varsa) |
| `g:product_type` | Kategori adı |
| `g:sale_price` | İndirimli fiyat (varsa) |
| `g:additional_image_link` | Ek görseller (max 9) |
| `g:shipping` | Kargo ülke/servis/fiyat |
| `g:custom_label_0` | Özel etiket (ayardan) |
| `g:custom_label_1` | `indirimli` (otomatik) |
| `g:custom_label_2` | `stok-disi` (otomatik) |
| `g:quantity_to_sell_on_google` | Stok adedi |

## Ayarlar

| Ayar | Açıklama |
|------|----------|
| Feed aktif | Feed'i açar/kapatır |
| Para birimi | TRY / USD / EUR |
| Ürün durumu | Yeni / İkinci el / Yenilenmiş |
| Cache süresi | Dakika (varsayılan: 360) |
| Varsayılan marka | Marka atanmamış ürünler için |
| Hariç kategori | Virgülle ayrılmış ID'ler |
| Stok dışı dahil | Tükenen ürünleri ekle |
| Custom Label 0 | Tüm ürünlere uygulanır |

## Cron (isteğe bağlı)

Cache'i periyodik olarak yenilemek için:

```
GET /api/module.php?m=google-shopping&action=feed&token=TOKEN
```

Günde 1–4 kez çekmeniz yeterlidir. Google Merchant Center zaten zamanlanmış getirme yapar.
