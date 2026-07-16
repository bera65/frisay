<?php
$root = dirname(__DIR__);
$dir = $root . '/templates/admin';
$map = [
	"{'Promotion'|adminT} Adı" => "{'Promotion name'|adminT}",
	"{'Promotion'|adminT} Tipi" => "{'Promotion type'|adminT}",
	"N. Ürüne {'Discount'|adminT}" => "{'Nth item discount'|adminT}",
	"{'Discount'|adminT} tipi" => "{'Discount type'|adminT}",
	"{'Discount'|adminT} değeri" => "{'Discount value'|adminT}",
	"N. ürüne indirim" => "{'Nth item discount'|adminT}",
	"X al Y öde" => "{'Buy X pay Y'|adminT}",
	"Kaçıncı ürün?" => "{'Which item number?'|adminT}",
	"2 = ikinci ürün, 3 = üçüncü ürün" => "{'2 = 2nd item, 3 = 3rd item'|adminT}",
	"Her N. üründe tekrarla" => "{'Repeat every Nth item'|adminT}",
	"X Al Y Öde" => "{'Buy X pay Y'|adminT}",
	"Alınacak adet (X)" => "{'Buy quantity (X)'|adminT}",
	"Ödenecek adet (Y)" => "{'Pay quantity (Y)'|adminT}",
	"Örn: 3 al 2 öde → X=3, Y=2. En ucuz ürün(ler) bedava sayılır." => "{'E.g. buy 3 pay 2 → X=3, Y=2. Cheapest item(s) are free.'|adminT}",
	"Minimum sepet (₺)" => "{'Minimum cart (₺)'|adminT}",
	"Sabit (₺)" => "{'Fixed (₺)'|adminT}",
	"Örn: 2. ürüne %5 indirim" => "{'E.g. 5% off 2nd item'|adminT}",

	"{'Text delivery'|adminT}nda bu alan doğrudan müşteriye iletilir." => "{'For text delivery, this field is sent directly to the customer.'|adminT}",
	"Teslimat Metni" => "{'Delivery text'|adminT}",
	"Lisans Anahtarları" => "{'License keys'|adminT}",
	"Kullanılabilir anahtarlar" => "{'Available keys'|adminT}",
	"Kullanılmış:" => "{'Used:'|adminT}",
	"Yeni anahtar ekle" => "{'Add new keys'|adminT}",
	"Her satıra bir lisans anahtarı yazın" => "{'One license key per line'|adminT}",
	"Yeni anahtarlar kayıt sırasında listeye eklenir; kullanılmış anahtarlar silinmez." => "{'New keys are added on save; used keys are not removed.'|adminT}",
	"Henüz kullanılabilir anahtar yok." => "{'No available keys yet.'|adminT}",
	"Sipariş sonrası müşteriye gösterilecek lisans bilgisi, indirme talimatı veya erişim detayı" => "{'License info, download instructions or access details shown after order'|adminT}",
	"Lisans ürünlerinde stok, kullanılabilir anahtar sayısıdır. 0 = sınırsız (indirme/metin)." => "{'For license products, stock is the number of available keys. 0 = unlimited (download/text).'|adminT}",
	"Varyasyonlu ürünlerde toplam stok otomatik hesaplanır." => "{'For products with variations, total stock is calculated automatically.'|adminT}",
	"örn: 3 Al 2 Öde" => "{'e.g. Buy 3 Pay 2'|adminT}",
	"Her satır bir kombinasyondur (ör. Kırmızı + M). Aynı seçenek adını tüm satırlarda aynı kullanın." => "{'Each row is one combination (e.g. Red + M). Use the same option name in every row.'|adminT}",
	"Stok etkilemez; müşteri ürün sayfasında seçer (ör. Boyut, İçecek)." => "{'Does not affect stock; customer selects on the product page (e.g. Size, Drink).'|adminT}",
	"Medya kütüphanesinden seçin veya yeni yükleyin." => "{'Pick from media library or upload new files.'|adminT}",
	"Önce ürünü kaydedin" => "{'Save the product first'|adminT}",
	"Kayıttan sonra medya kütüphanesini kullanabilirsiniz" => "{'You can use the media library after saving'|adminT}",
	"Medya kütüphanesini aç" => "{'Open media library'|adminT}",
	"Mevcut görselleri seçin veya yeni dosya yükleyin" => "{'Select existing images or upload new files'|adminT}",
	"Henüz görsel yok. Medya kütüphanesinden seçin." => "{'No images yet. Select from the media library.'|adminT}",
	"Ürün videosu" => "{'Product video'|adminT}",
	"YouTube linki — ürün sayfasında sekme olarak gösterilir." => "{'YouTube link — shown as a tab on the product page.'|adminT}",
	"İndirilebilir ürünler için dosya yükleyin (ZIP, PDF, RAR… maks. 50 MB)." => "{'Upload a file for downloadable products (ZIP, PDF, RAR… max 50 MB).'|adminT}",
	"Yüklü dosya:" => "{'Uploaded file:'|adminT}",
	"{'File'|adminT}yı Sil" => "{'Delete file'|adminT}",
	"Henüz dijital dosya yüklenmedi." => "{'No digital file uploaded yet.'|adminT}",
	"Dijital {'File'|adminT} Yükle" => "{'Upload digital file'|adminT}",
	"{'File'|adminT} yöneticisi" => "{'File manager'|adminT}",
	"+ Klasör" => "{'+ Folder'|adminT}",
	"Medya klasörü" => "{'Media folder'|adminT}",
	"Yükleniyor…" => "{'Loading…'|adminT}",
	"Görsel seçin veya yeni yükleyin" => "{'Select an image or upload new'|adminT}",
	"Vazgeç" => "{'Cancel'|adminT}",
	"Seçilenleri ürüne ekle" => "{'Add selected to product'|adminT}",

	"Smarty şablonlarını derleyip saklar. Kapalıyken her istekte yeniden derlenir (yavaş, tema geliştirme için)." => "{'Compiles and stores Smarty templates. When off, templates recompile on every request (slow, for theme development).'|adminT}",
	"Giriş yapmamış ziyaretçiler için tam sayfa önbelleği. Sepet sayısı gecikmeli görünebilir; ürün/sepet/ödeme sayfaları önbelleğe alınmaz." => "{'Full page cache for guests. Cart count may lag; product/cart/checkout pages are not cached.'|adminT}",
	"HTML çıktısını sıkıştırarak sayfa boyutunu küçültür." => "{'Compresses HTML output to reduce page size.'|adminT}",
	"Gereksiz boşlukları kaldırır. script/style blokları korunur." => "{'Removes extra whitespace. script/style blocks are preserved.'|adminT}",
	"env.php ayarını kullan (şu an:" => "{'Use env.php setting (currently:'|adminT}",
	"Açık — PHP hataları ekranda" => "{'On — PHP errors on screen'|adminT}",
	"Kapalı — hatalar gizli, log dosyasına yazılır" => "{'Off — errors hidden, written to log file'|adminT}",
	"Şu anki durum:" => "{'Current status:'|adminT}",
	"{'Debugging'|adminT} açık" => "{'Debugging enabled'|adminT}",
	"Üretim modu" => "{'Production mode'|adminT}",
	'{if $perfEnvDebug}açık{else}kapalı{/if}' => '{if $perfEnvDebug}{\'on\'|adminT}{else}{\'off\'|adminT}{/if}',

	"Sipariş #" => "{'Order #'|adminT}",

	"dosyası oluşur; CMS, ürün, kategori ve marka kayıtlarına boş çeviri sekmesi eklenir." => "{' file is created; empty translation tabs are added for CMS, products, categories and brands.'|adminT}",
	"UI metinleri için bu dosyaya çevirileri ekleyin." => "{'Add UI translations to this file.'|adminT}",

	"İçerik sekmeleri sitedeki aktif dillere göre oluşturulur" => "{'Content tabs are created for active store languages'|adminT}",
	"Yayında" => "{'Published'|adminT}",
	"Footer'da göster" => "{'Show in footer'|adminT}",
	"Başlık" => "{'Title'|adminT}",
	"Kısa açıklama" => "{'Short description'|adminT}",
	"Sitede Gör" => "{'View on site'|adminT}",

	"ör. Paroner, BizimHesap" => "{'e.g. Paroner, BizimHesap'|adminT}",
	" · Son kullanım:" => "{' · Last used:'|adminT}",
	"Dökümantasyon" => "{'Documentation'|adminT}",
	"Tüm dökümanlar" => "{'All documentation'|adminT}",
	"Partner entegrasyonu için örnek PHP kodları ve endpoint açıklamaları." => "{'Sample PHP code and endpoint descriptions for partner integration.'|adminT}",
	"Örnekler" => "{'Examples'|adminT}",
	" — ürün ekle/düzenle/sil + siparişler" => "{' — add/edit/delete products + orders'|adminT}",
	" — sadece «Siparişleri oku / çek»" => "{' — read/pull orders only'|adminT}",

	"Takip numarası bu adresin <strong>sonuna</strong> eklenir. İsterseniz <code>{ldelim}code{rdelim}</code> kullanın." => "{'The tracking number is appended to the <strong>end</strong> of this URL. You may use <code>{ldelim}code{rdelim}</code>.'|adminT}",
	"Örn: 0–1500 → 80 ₺ · 1500–2000 → 100 ₺ · 2000+ → 0 (bedava). Üst sınır boş = üst limit yok." => "{'E.g. 0–1500 → 80 · 1500–2000 → 100 · 2000+ → 0 (free). Empty max = no upper limit.'|adminT}",
	"Kargo ücretleri yalnızca buradaki firmalar ve aralıklarla hesaplanır. Ödeme sayfasında müşteri kargo seçer." => "{'Shipping fees are calculated only from carriers and ranges here. Customer selects carrier at checkout.'|adminT}",

	"Ör: my-custom-theme" => "{'E.g. my-custom-theme'|adminT}",
	"Ör: Yeni Tasarım Teması" => "{'E.g. New design theme'|adminT}",

	'{\'Translation tabs are created for active store languages\'|adminT} ({$shopLanguages|@count} dil).' => '{\'Translation tabs are created for active store languages\'|adminT} ({$shopLanguages|@count} {\'Language\'|adminT}).',
	'{\'Content tabs are created for active store languages\'|adminT} ({$shopLanguages|@count} dil).' => '{\'Content tabs are created for active store languages\'|adminT} ({$shopLanguages|@count} {\'Language\'|adminT}).',
];
uksort($map, static fn($a, $b) => strlen($b) <=> strlen($a));
$total = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($it as $file) {
	if ($file->getExtension() !== 'tpl') continue;
	$content = file_get_contents($file->getPathname());
	$c = 0;
	foreach ($map as $from => $to) {
		if ($from === $to) continue;
		$content = str_replace($from, $to, $content, $n);
		$c += $n;
	}
	if ($c > 0) {
		file_put_contents($file->getPathname(), $content);
		$total += $c;
		echo basename($file->getPathname()) . ": {$c}\n";
	}
}
echo "Total: {$total}\n";
