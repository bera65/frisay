<?php
/**
 * Batch 2: wrap remaining Turkish admin UI strings.
 * Run: php tools/patch-admin-i18n-batch2.php && php tools/sync-admin-lang.php
 */
$root = dirname(__DIR__);
$dir = $root . '/templates/admin';

// Turkish => Smarty adminT wrapper (longest keys first)
$map = [
	// cms.tpl fix + pages
	"{'{'Slug'|adminT}'|adminT}" => "{'Slug'|adminT}",
	'Yeni Sayfa Ekle' => "{'Add page'|adminT}",
	'Dilleri Yönet' => "{'Manage languages'|adminT}",
	'Sayfa' => "{'Page'|adminT}",
	'Bu sayfa silinsin mi?' => "{'Delete this page?'|adminT}",
	'Henüz CMS sayfası yok.' => "{'No CMS pages yet.'|adminT}",
	'CMS içerikleri veritabanında saklanır. Her dil için ayrı başlık ve içerik girebilirsiniz.' => "{'CMS content is stored in the database. You can enter separate title and content for each language.'|adminT}",

	// order.tpl
	'Sipariş No:' => "{'Order no:'|adminT}",
	'Tarih:' => "{'Date:'|adminT}",
	'Müşteri:' => "{'Customer:'|adminT}",
	'Telefon:' => "{'Phone:'|adminT}",
	'Firma:' => "{'Company:'|adminT}",
	'Vergi Dairesi:' => "{'Tax office:'|adminT}",
	'Vergi No / TCKN:' => "{'Tax no / ID:'|adminT}",
	'Adres:' => "{'Address:'|adminT}",
	'Not:' => "{'Note:'|adminT}",
	'Kargo:' => "{'Shipping:'|adminT}",
	'Takip No:' => "{'Tracking no:'|adminT}",
	'Takibi aç ↗' => "{'Open tracking ↗'|adminT}",
	'Adet' => "{'Qty'|adminT}",
	'Birim' => "{'Unit'|adminT}",
	'Verilen lisanslar:' => "{'Issued licenses:'|adminT}",
	'Henüz lisans atanmadı (ödeme onayı bekleniyor olabilir).' => "{'No license assigned yet (payment may be pending).'|adminT}",
	'Dijital dosya' => "{'Digital file'|adminT}",
	'İndirme henüz aktif değil.' => "{'Download not active yet.'|adminT}",
	'Özet' => "{'Summary'|adminT}",
	'Ara Toplam' => "{'Subtotal'|adminT}",
	'Kupon' => "{'Coupon'|adminT}",
	'Kampanya' => "{'Promotion'|adminT}",
	'Ödeme indirimi' => "{'Payment discount'|adminT}",
	'Kargo' => "{'Shipping'|adminT}",
	'Ödeme:' => "{'Payment:'|adminT}",
	'Durum ve Kargo' => "{'Status and shipping'|adminT}",
	'Kargo Firması' => "{'Carrier company'|adminT}",
	'— Seçin —' => "{'— Select —'|adminT}",
	'Takip Numarası' => "{'Tracking number'|adminT}",
	'Takip linki:' => "{'Tracking link:'|adminT}",
	'Takip linki, seçilen kargonun takip adresine numaranın eklenmesiyle oluşur.' => "{'The tracking link is built by appending the number to the carrier tracking URL.'|adminT}",
	' menüsünden firma ekleyebilirsiniz.' => "{' menu to add carriers.'|adminT}",
	'&larr; Sipariş listesine dön' => "{'← Back to orders'|adminT}",

	// return.tpl
	'İade Talebi #' => "{'Return request #'|adminT}",
	'Talep tarihi:' => "{'Request date:'|adminT}",
	'Sipariş durumu:' => "{'Order status:'|adminT}",
	'Sipariş tutarı:' => "{'Order total:'|adminT}",
	'Müşteri Mesajı' => "{'Customer message'|adminT}",
	'Mağaza Yanıtı' => "{'Store reply'|adminT}",
	'İade Dekontu' => "{'Return receipt'|adminT}",
	'İade dekontu' => "{'Return receipt'|adminT}",
	'Talebi İşle' => "{'Process request'|adminT}",
	'Onayladığınızda müşteriye mesajınız gider ve sipariş durumu <strong>İade edildi</strong> olur.' => "{'When approved, your message is sent to the customer and the order status becomes <strong>Returned</strong>.'|adminT}",
	'Müşteriye mesaj' => "{'Message to customer'|adminT}",
	'İade onayı ve talimatlarınızı yazın' => "{'Write return approval and instructions'|adminT}",
	'Onayla ve süreci başlat' => "{'Approve and start process'|adminT}",
	'İade talebi reddedilsin mi?' => "{'Reject this return request?'|adminT}",
	'İadeyi Tamamla' => "{'Complete return'|adminT}",
	'İade işlemi bittiğinde müşteriye dekont yükleyebilir ve ek mesaj yazabilirsiniz.' => "{'When the return is done, you can upload a receipt and add a message for the customer.'|adminT}",
	'Ek mesaj (isteğe bağlı)' => "{'Additional message (optional)'|adminT}",
	'İade tamamlandı bilgisi' => "{'Return completed notice'|adminT}",
	'İade dekontu (isteğe bağlı)' => "{'Return receipt (optional)'|adminT}",
	'Müşteri bu görseli iade detayında görebilir. JPG, PNG veya WEBP — en fazla 5 MB.' => "{'The customer can see this image on the return details. JPG, PNG or WEBP — max 5 MB.'|adminT}",
	'İadeyi tamamlandı olarak işaretle' => "{'Mark return as completed'|adminT}",
	'← İade listesine dön' => "{'← Back to returns'|adminT}",

	// cancel.tpl
	'İptal Talebi #' => "{'Cancel request #'|adminT}",
	'Mesaj yazılmadı' => "{'No message provided'|adminT}",
	'İptal Dekontu' => "{'Cancel receipt'|adminT}",
	'Onayladığınızda sipariş iptal edilir. İsteğe bağlı dekont yükleyebilirsiniz.' => "{'When approved, the order is cancelled. You may upload a receipt optionally.'|adminT}",
	'İptal dekontu (isteğe bağlı)' => "{'Cancel receipt (optional)'|adminT}",
	'Onayla ve iptal et' => "{'Approve and cancel'|adminT}",
	'Reddedilsin mi?' => "{'Reject this request?'|adminT}",
	'← İptal listesine dön' => "{'← Back to cancellations'|adminT}",

	// customer.tpl
	'Müşteri Bilgileri' => "{'Customer information'|adminT}",
	'Bilgileri Kaydet' => "{'Save information'|adminT}",
	'Şifre Değiştir' => "{'Change password'|adminT}",
	'Yeni şifre doğrudan müşteri hesabına yazılır. En az 8 karakter yeterlidir (ör. <code>12345678</code>). Müşteri telefona kayıtlı numarayla giriş yapar.' => "{'The new password is saved directly to the customer account. At least 8 characters (e.g. <code>12345678</code>). The customer signs in with their registered phone number.'|adminT}",
	'Yeni şifre' => "{'New password'|adminT}",
	'Yeni şifre (tekrar)' => "{'New password (confirm)'|adminT}",
	'Şifreyi ' => "{'Update password'|adminT}",
	'Hesap Durumu' => "{'Account status'|adminT}",
	'Hesabı Pasifleştir' => "{'Deactivate account'|adminT}",
	'Hesabı Aktifleştir' => "{'Activate account'|adminT}",
	'Sipariş Geçmişi' => "{'Order history'|adminT}",
	'Referans' => "{'Reference'|adminT}",
	'Henüz sipariş yok.' => "{'No orders yet.'|adminT}",

	// messages.tpl
	'Okunmamış' => "{'Unread'|adminT}",
	'Okunmuş' => "{'Read'|adminT}",
	'Konuşma' => "{'Conversation'|adminT}",
	'Son aktivite' => "{'Last activity'|adminT}",
	' mesaj' => "{' messages'|adminT}",
	' yanıt' => "{' replies'|adminT}",
	'Mesaj bulunamadı.' => "{'No messages found.'|adminT}",

	// message.tpl
	'Siparişi aç' => "{'Open order'|adminT}",
	' müşteri mesajı · ' => "{' customer messages · '|adminT}",
	' yanıt' => "{' replies'|adminT}",
	'Mesaj #' => "{'Message #'|adminT}",
	'Ek belgeyi aç' => "{'Open attachment'|adminT}",
	'Müşteriye yanıt yaz' => "{'Write reply to customer'|adminT}",
	'Yanıtı gönder' => "{'Send reply'|adminT}",
	'Yanıt, son müşteri mesajına bağlanır ve müşteriye e-posta + bildirim olarak iletilir.' => "{'The reply is linked to the latest customer message and sent by email and notification.'|adminT}",
	'&larr; Mesaj listesine dön' => "{'← Back to messages'|adminT}",

	// coupons
	'Kupon Kodları' => "{'Coupon codes'|adminT}",
	'Sepet Kampanyaları' => "{'Cart promotions'|adminT}",
	'+ Yeni Kupon' => "{'+ New coupon'|adminT}",
	'Kod' => "{'Code'|adminT}",
	'İndirim' => "{'Discount'|adminT}",
	'Min. Sepet' => "{'Min. cart'|adminT}",
	'Kullanım' => "{'Usage'|adminT}",
	'Geçerlilik' => "{'Validity'|adminT}",
	'Aktif' => "{'Active'|adminT}",
	'Bu kupon silinsin mi?' => "{'Delete this coupon?'|adminT}",
	'Henüz kupon yok.' => "{'No coupons yet.'|adminT}",
	'Sepet kampanyaları otomatik uygulanır; müşteri kod girmek zorunda değildir.' => "{'Cart promotions apply automatically; customers do not need to enter a code.'|adminT}",
	'<strong>N. ürüne indirim:</strong> örn. 2. ürüne 10 TL veya %5.' => "{'<strong>Nth item discount:</strong> e.g. 10 off the 2nd item or 5%.'|adminT}",
	'<strong>X al Y öde:</strong> örn. 3 al 2 öde (en ucuz ürün bedava).' => "{'<strong>Buy X pay Y:</strong> e.g. buy 3 pay for 2 (cheapest item free).'|adminT}",
	'Uygun koşulları sağlayan tüm aktif kampanyalar sepette birlikte uygulanır.' => "{'All active promotions that match are applied together in the cart.'|adminT}",
	'+ Yeni Sepet Kampanyası' => "{'+ New cart promotion'|adminT}",
	'Kampanya' => "{'Promotion'|adminT}",
	'Kural' => "{'Rule'|adminT}",
	'Bu kampanya silinsin mi?' => "{'Delete this promotion?'|adminT}",
	'Henüz sepet kampanyası yok.' => "{'No cart promotions yet.'|adminT}",
	'Kupon Kodu' => "{'Coupon code'|adminT}",
	'İndirim Tipi' => "{'Discount type'|adminT}",
	"İndirim {'Value'|adminT}i" => "{'Discount value'|adminT}",
	'Yüzde (%)' => "{'Percent (%)'|adminT}",
	'Sabit Tutar (₺)' => "{'Fixed amount'|adminT}",
	'Minimum Sepet (₺)' => "{'Minimum cart (₺)'|adminT}",
	'Maks. Kullanım (0 = sınırsız)' => "{'Max uses (0 = unlimited)'|adminT}",
	'Başlangıç (opsiyonel)' => "{'Start (optional)'|adminT}",
	'Bitiş (opsiyonel)' => "{'End (optional)'|adminT}",

	// products
	'Dışa Aktar' => "{'Export'|adminT}",
	'İçe Aktar' => "{'Import'|adminT}",
	'Sanal' => "{'Virtual'|adminT}",
	'Excel ile Ürün İçe Aktar' => "{'Import products from Excel'|adminT}",
	'Bilgilendirme' => "{'Information'|adminT}",
	'Yükle ve İçe Aktar' => "{'Upload and import'|adminT}",
	'Excel Dosyası' => "{'Excel file'|adminT}",

	// product price / cover
	'Kapak' => "{'Cover'|adminT}",
	'Fiyat (' => "{'Price ('|adminT}",
	'Alış Fiyatı' => "{'Cost price'|adminT}",
	'Satış Fiyatı' => "{'Sale price'|adminT}",
	'Eski Fiyat' => "{'Old price'|adminT}",
	"{'Value'|adminT}ler (her satıra bir tane)" => "{'Values (one per line)'|adminT}",
	'Stok Kodu' => "{'SKU'|adminT}",
	'Barkod' => "{'Barcode'|adminT}",
	'Desi' => "{'Desi'|adminT}",
	'Varyasyon kullan' => "{'Use variations'|adminT}",
	'+ Varyasyon Ekle' => "{'+ Add variation'|adminT}",
	'Toplam stok:' => "{'Total stock:'|adminT}",
	'Zorunlu' => "{'Required'|adminT}",
	'Grubu sil' => "{'Remove group'|adminT}",

	// category / brand
	'Çeviri sekmeleri sitedeki aktif dillere göre oluşturulur' => "{'Translation tabs are created for active store languages'|adminT}",
	'Kategori Adı' => "{'Category name'|adminT}",
	'Üst Kategori' => "{'Parent category'|adminT}",
	'Yok (kök)' => "{'None (root)'|adminT}",
	'Marka Adı' => "{'Brand name'|adminT}",
	'+ Yeni Marka' => "{'+ New brand'|adminT}",
	'Markayı Sil' => "{'Delete brand'|adminT}",
	'Bu markayı silmek istediğinize emin misiniz? Marka kaydı kalıcı olarak kaldırılacaktır.' => "{'Are you sure you want to delete this brand? The brand record will be permanently removed.'|adminT}",

	// languages
	'Aktif diller' => "{'Active languages'|adminT}",
	'CMS, ürün ve kategori çeviri sekmeleri bu listedeki dillere göre oluşturulur.' => "{'CMS, product and category translation tabs use this language list.'|adminT}",
	'Görünen ad' => "{'Display name'|adminT}",
	'Dosya' => "{'File'|adminT}",
	'Eksik' => "{'Missing'|adminT}",
	"{'Default'|adminT} yap" => "{'Set as default'|adminT}",
	'Yeni dil ekle' => "{'Add language'|adminT}",
	'ISO kodu: en, tr, de, fr, es …' => "{'ISO code: en, tr, de, fr, es …'|adminT}",
	'Dili ekle' => "{'Add language'|adminT}",
	'Yeni dil eklendiğinde' => "{'When a new language is added'|adminT}",
	'&larr; CMS sayfalarına dön' => "{'← Back to CMS pages'|adminT}",

	// currencies
	'Tanımlı para birimleri' => "{'Defined currencies'|adminT}",
	'Ürün fiyatları <strong>mağaza para birimi</strong> ile girilir. Listeden birini aktif mağaza birimi yapın.' => "{'Product prices are entered in the <strong>store currency</strong>. Set one from the list as the active store currency.'|adminT}",
	'Ad / Sembol' => "{'Name / Symbol'|adminT}",
	'Mağaza birimi' => "{'Store currency'|adminT}",
	'Mağaza birimi yap' => "{'Set as store currency'|adminT}",
	'Yeni para birimi ekle' => "{'Add currency'|adminT}",
	'ISO kodu' => "{'ISO code'|adminT}",
	'3 harf: try, usd, eur, gbp, chf …' => "{'3 letters: try, usd, eur, gbp, chf …'|adminT}",
	'Sembol' => "{'Symbol'|adminT}",
	'Para birimi ekle' => "{'Add currency'|adminT}",
	'&larr; Ayarlara dön' => "{'← Back to settings'|adminT}",

	// seo
	'Sayfa SEO Ayarları' => "{'Page SEO settings'|adminT}",
	'Boş bırakılan alanlarda varsayılan başlık ve açıklama kullanılır.' => "{'Empty fields use the default title and description.'|adminT}",
	'Ürün, kategori ve marka SEO bilgileri kendi düzenleme ekranlarından yönetilir.' => "{'Product, category and brand SEO is managed on their edit screens.'|adminT}",
	'Schema.org — İşletme Bilgileri' => "{'Schema.org — Business information'|adminT}",
	'Organization şemasında kullanılır. E-posta ve telefon Site Ayarlarından alınır.' => "{'Used in Organization schema. Email and phone come from Site Settings.'|adminT}",
	'Şehir' => "{'City'|adminT}",
	'Posta Kodu' => "{'Postal code'|adminT}",
	'Enlem' => "{'Latitude'|adminT}",
	'Boylam' => "{'Longitude'|adminT}",
	'SEO Ayarlarını Kaydet' => "{'Save SEO settings'|adminT}",

	// modules
	' tarafından' => "{' by'|adminT}",
	'Tüm modülleri göster' => "{'Show all modules'|adminT}",
	'Kurulu modüller' => "{'Installed modules'|adminT}",
	'Aktif modüller' => "{'Active modules'|adminT}",
	'Kurulu olmayanlar' => "{'Not installed'|adminT}",
	'Yönetim' => "{'Management'|adminT}",
	'Diğer işlemler' => "{'More actions'|adminT}",
	'Modül detayı' => "{'Module details'|adminT}",
	'Devre dışı bırak' => "{'Disable'|adminT}",
	'Etkinleştir' => "{'Enable'|adminT}",
	'Henüz modül bulunamadı.' => "{'No modules found yet.'|adminT}",
	'Arama veya filtreye uygun modül bulunamadı.' => "{'No modules match your search or filter.'|adminT}",
	'&larr; Modül listesine dön' => "{'← Back to module list'|adminT}",
	'Kurulu değil' => "{'Not installed'|adminT}",
	'Görünür hook ataması' => "{'Display hook assignment'|adminT}",
	"Bu modül bu hook'u desteklemiyor" => "{'This module does not support this hook'|adminT}",
	'Hook atamalarını kaydet' => "{'Save hook assignments'|adminT}",
	"Kullandığı hook'lar" => "{'Registered hooks'|adminT}",
	'Yapılandırma' => "{'Configuration'|adminT}",
	' — Yapılandır' => "{' — Configure'|adminT}",
	'Durum ve işlemler' => "{'Status and actions'|adminT}",
	'Kur ve etkinleştir' => "{'Install and enable'|adminT}",
	'API uçları' => "{'API endpoints'|adminT}",

	// performance
	'Performans &amp; Önbellek' => "{'Performance & cache'|adminT}",
	'Site hızı, önbellek ve hata ayıklama ayarları.' => "{'Site speed, cache and debugging settings.'|adminT}",
	'Önbelleği Temizle' => "{'Clear cache'|adminT}",
	'Şablon derleme' => "{'Template compilation'|adminT}",
	'Sayfa önbelleği' => "{'Page cache'|adminT}",
	'Sunucu' => "{'Server'|adminT}",
	'Destekleniyor' => "{'Supported'|adminT}",
	'Yok' => "{'None'|adminT}",
	'Şablon önbelleği' => "{'Template cache'|adminT}",
	'Hızlı sayfa modu' => "{'Fast page mode'|adminT}",
	'Sayfa önbellek süresi (dakika)' => "{'Page cache TTL (minutes)'|adminT}",
	'Hızlandırma' => "{'Acceleration'|adminT}",
	'Gzip sıkıştırma' => "{'Gzip compression'|adminT}",
	'HTML küçültme' => "{'HTML minification'|adminT}",
	'Hata ayıklama' => "{'Debugging'|adminT}",
	'Hata gösterimi' => "{'Error display'|adminT}",
	'Ayarları Kaydet' => "{'Save settings'|adminT}",

	// api
	'API durumu' => "{'API status'|adminT}",
	'Web API aktif' => "{'Web API enabled'|adminT}",
	'FriSay API dökümantasyonu' => "{'FriSay API documentation'|adminT}",
	'API düzenle' => "{'Edit API'|adminT}",
	'Yeni API oluştur' => "{'Create new API'|adminT}",
	'Partner / isim' => "{'Partner / name'|adminT}",
	'Yetkiler' => "{'Permissions'|adminT}",
	'Oluştur' => "{'Create'|adminT}",
	'Tanımlı API anahtarları' => "{'Defined API keys'|adminT}",
	'Henüz anahtar yok. Soldan «Yeni API oluştur» ile ekleyin.' => "{'No keys yet. Add one using Create new API on the left.'|adminT}",
	'Anahtarı Yenile' => "{'Regenerate key'|adminT}",
	'Yeni API Key (bir kez gösterilir / kopyalayın):' => "{'New API key (shown once — copy it):'|adminT}",

	// cargos
	'Takip linki (prefix)' => "{'Tracking link (prefix)'|adminT}",
	'Kaydet' => "{'Save'|adminT}",
	'Bu kargo silinsin mi?' => "{'Delete this carrier?'|adminT}",
	'Takip:' => "{'Tracking:'|adminT}",
	' + takip no' => "{' + tracking no'|adminT}",
	'boş = +∞' => "{'empty = +∞'|adminT}",

	// templates
	'Klonlanacak (Referans) Tema' => "{'Theme to clone (reference)'|adminT}",
	'Yeni Tema Klasör Adı' => "{'New theme folder name'|adminT}",
	'Yeni Tema Görünen Adı' => "{'New theme display name'|adminT}",
	'Yeni Temayı Klonla' => "{'Clone new theme'|adminT}",

	// order-rows
	"{'Takip'|adminT}" => "{'Tracking'|adminT}",
];

uksort($map, static fn($a, $b) => strlen($b) <=> strlen($a));

$total = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($it as $file) {
	if ($file->getExtension() !== 'tpl') {
		continue;
	}
	$content = file_get_contents($file->getPathname());
	$count = 0;
	foreach ($map as $from => $to) {
		if ($from === $to) {
			continue;
		}
		$content = str_replace($from, $to, $content, $c);
		$count += $c;
	}
	if ($count > 0) {
		file_put_contents($file->getPathname(), $content);
		$total += $count;
		echo basename($file->getPathname()) . ": {$count}\n";
	}
}

echo "Total replacements: {$total}\n";
