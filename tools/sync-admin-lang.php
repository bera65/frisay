<?php
/**
 * Scan admin templates for adminT keys and merge into lang/admin/en.php + tr.php.
 * Run: php tools/sync-admin-lang.php
 */
$root = dirname(__DIR__);
$dirs = [
	$root . '/templates/admin',
	$root . '/modules',
];

$keys = [];

$scan = static function (string $content) use (&$keys): void {
	if (preg_match_all("/\{'((?:\\\\'|[^'])+)'\|adminT\}/", $content, $m)) {
		foreach ($m[1] as $key) {
			$key = str_replace("\\'", "'", $key);
			$keys[$key] = true;
		}
	}
};

foreach ($dirs as $dir) {
	if (!is_dir($dir)) {
		continue;
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
	foreach ($it as $file) {
		if ($file->getExtension() !== 'tpl') {
			continue;
		}
		$path = str_replace('\\', '/', $file->getPathname());
		if (strpos($path, '/assets/templates/admin/') === false
			&& strpos($path, '/templates/admin/') === false) {
			continue;
		}
		$scan(file_get_contents($file->getPathname()));
	}
}

$enPath = $root . '/lang/admin/en.php';
$trPath = $root . '/lang/admin/tr.php';
$en = is_file($enPath) ? require $enPath : [];
$tr = is_file($trPath) ? require $trPath : [];

if (!is_array($en)) {
	$en = [];
}
if (!is_array($tr)) {
	$tr = [];
}

$extraEn = [
	'Domain:' => 'Domain:',
	'Invalid action' => 'Invalid action',
	'Reply sent to customer' => 'Reply sent to customer',
	'Record updated' => 'Record updated',
	'Schema.org settings could not be saved' => 'Schema.org settings could not be saved',
	'Invalid admin language' => 'Invalid admin language',
	'Default admin language updated' => 'Default admin language updated',
	'Select an Excel file' => 'Select an Excel file',
	'Only .xlsx files are allowed' => 'Only .xlsx files are allowed',
];

foreach (array_keys($keys) as $key) {
	$en[$key] = $key;
}

foreach ($extraEn as $key => $value) {
	$en[$key] = $value;
	if (!isset($tr[$key])) {
		$tr[$key] = $key;
	}
}

$trExtras = [
	'Domain:' => 'Domain:',
	'Invalid action' => 'Geçersiz işlem',
	'Reply sent to customer' => 'Yanıt müşteriye gönderildi',
	'Record updated' => 'Kayıt güncellendi',
	'Schema.org settings could not be saved' => 'Schema.org ayarları kaydedilemedi',
	'Invalid admin language' => 'Geçersiz admin dili',
	'Default admin language updated' => 'Admin panel varsayılan dili güncellendi',
	'Select an Excel file' => 'Excel dosyası seçin',
	'Only .xlsx files are allowed' => 'Sadece .xlsx dosyası yükleyebilirsiniz',
	'New Product' => 'Yeni Ürün',
	'Edit Product' => 'Ürün Düzenle',
	'Save first; then you can add images via drag and drop.' => 'Önce kaydedin; sonra görselleri sürükle-bırak ile ekleyebilirsiniz.',
	'View product' => 'Ürüne bak',
	'Product content' => 'Ürün içeriği',
	'Name, slug, short/long description and SEO fields are per language.' => 'Ad, slug, kısa/uzun açıklama ve SEO alanları dil bazlıdır.',
	'Product name' => 'Ürün Adı',
	'Leave blank for automatic' => 'Boş bırakılırsa otomatik',
	'Short description' => 'Kısa Açıklama',
	'Meta title' => 'Meta Başlık',
	'Meta description' => 'Meta Açıklama',
	'Long description' => 'Uzun Açıklama',
	'Category, brand, SKU and product type.' => 'Kategori, marka, stok kodu ve ürün tipi.',
	'Product type' => 'Ürün Türü',
	'Physical product' => 'Fiziksel ürün',
	'Virtual / digital product' => 'Sanal / dijital ürün',
	'Delivery type' => 'Teslimat Türü',
	'Downloadable file' => 'İndirilebilir dosya',
	'License key' => 'Lisans anahtarı',
	'Text delivery' => 'Metin teslimatı',
	'Product status' => 'Ürün Durumu',
	'Lead time (days)' => 'Termin Süresi (gün)',
	'0 uses the general shipping time.' => '0 ise genel kargo süresi kullanılır.',
	'Product label' => 'Ürün Etiketi',
	'Product variations' => 'Ürün varyasyonları',
	'Product options' => 'Ürün seçenekleri',
	'Option 1' => 'Seçenek 1',
	'Option 2' => 'Seçenek 2',
	'Value' => 'Değer',
	'Remove row' => 'Satırı sil',
	'Empty = base price' => 'Boş = ana fiyat',
	'+ Add option group' => '+ Seçenek Grubu Ekle',
	'Group name' => 'Grup adı',
	'All categories' => 'Tüm kategoriler',
	'All brands' => 'Tüm markalar',
	'All statuses' => 'Tüm durumlar',
	'Search...' => 'Ara...',
	'Edit carrier' => 'Kargo düzenle',
	'Add new carrier' => 'Yeni kargo ekle',
	'Carrier companies' => 'Kargo firmaları',
	'No carriers yet. Add one on the left.' => 'Henüz kargo yok. Soldan ekleyin.',
	'Default' => 'Varsayılan',
	'Sort order' => 'Sıra',
	'Price ranges (cart total → shipping fee)' => 'Fiyat aralıkları (sepet tutarı → kargo ücreti)',
	'+ Add range' => '+ Aralık ekle',
	'Update' => 'Güncelle',
	'Fee (₺)' => 'Ücret (₺)',
	'No price range defined.' => 'Fiyat aralığı tanımlı değil.',
	'Toggle menu' => 'Menüyü aç/kapat',
	'Remove this language? CMS and translation records will be deleted.' => 'Bu dil kaldırılsın mı? CMS ve çeviri kayıtları silinir.',
	'Remove this currency from the list?' => 'Bu para birimi listeden kaldırılsın mı?',
	'The key will be regenerated. The old key will become invalid.' => 'Anahtar yenilenecek. Eski anahtar geçersiz olur.',
	'Delete this API key?' => 'Bu API anahtarı silinsin mi?',
	'Uninstall this module? Data may be deleted.' => 'Modül kaldırılsın mı? Veriler silinebilir.',
	'Page not found' => 'Sayfa bulunamadı',
	'Are you sure you want to delete this product? The product and related images will be permanently removed.' => 'Bu ürünü silmek istediğinize emin misiniz? Ürün ve ilişkili görseller kalıcı olarak kaldırılacaktır.',
	'Saved per language tab' => 'dil sekmelerine göre kaydedilir',
	'Delete product' => 'Ürünü Sil',
	'Inactive' => 'Pasif',
	'Stock' => 'Stok',
	'Price' => 'Fiyat',
	'Image' => 'Görsel',
	'Images' => 'Görseller',
	'Description' => 'Açıklama',
	'Subject' => 'Konu',
	'Reply' => 'Yanıtla',
	'Enabled' => 'Açık',
	'Disabled' => 'Kapalı',
	'Cache' => 'Önbellek',
	'Registration date' => 'Kayıt tarihi',
	'Order count' => 'Sipariş sayısı',
	'Search modules…' => 'Modül ara…',
	'Uninstall this module?' => 'Modül kaldırılsın mı?',
	'Install' => 'Kur',
	'Uninstall' => 'Kaldır',
	'Configure' => 'Yapılandır',
	'Page title' => 'Sayfa başlığı',
	'Content' => 'İçerik',
	'Recent Orders' => 'Son Siparişler',
	'Approve' => 'Onayla',
	'Reject' => 'Reddet',
	'Back' => 'Geri',
	'Edit' => 'Düzenle',
	'Action' => 'İşlem',
	'Actions' => 'İşlemler',
	'Amount' => 'Tutar',
	'Total' => 'Toplam',
	'Phone' => 'Telefon',
	'Name' => 'Ad',
	'Tracking number' => 'Takip No',
	'Payment' => 'Ödeme',
	'Address' => 'Adres',
	'Unread' => 'Okunmadı',
	'Read' => 'Okundu',
	'Key name' => 'Anahtar adı',
	'Carrier company' => 'Kargo firması',
	'Fee' => 'Ücret',
	'Free' => 'Ücretsiz',
	'Store languages' => 'Mağaza dilleri',
	'Default language' => 'Varsayılan dil',
	'Language code' => 'Dil kodu',
	'Language name' => 'Dil adı',
	'No records yet.' => 'Henüz kayıt yok.',
	// batch4
	'Preview' => 'Önizle',
	'← All themes' => '← Tüm temalar',
	'Active theme' => 'Aktif tema',
	'Preview site' => 'Siteyi Önizle',
	'Layout & appearance' => 'Düzen & Görünüm',
	'Saved to the <code>custom.css</code> file.' => 'Kayıt <code>custom.css</code> dosyasına yazılır.',
	'Colors' => 'Renkler',
	'Save changes' => 'Değişiklikleri Kaydet',
	'Tip' => 'İpucu',
	'Site logos' => 'Site Logoları',
	'Upload' => 'Yükle',
	'Order slip' => 'Sipariş Fişi',
	'Delivery address' => 'Teslimat Adresi',
	'Tax/ID no:' => 'VKN/TCKN:',
	'Order note' => 'Sipariş Notu',
	'Pagination' => 'Sayfalama',
	'Previous' => 'Önceki',
	'Next' => 'Sonraki',
	'No configuration template is defined for this module yet.' => 'Bu modül için yapılandırma şablonu henüz tanımlı değil.',
	'Back to module details' => 'Modül detayına dön',
	'Add modules to the' => 'Modül eklemek için',
	'folder.' => 'klasörünü kullanın.',
	'in the admin panel' => 'admin panelde',
	'Actions' => 'Eylemler',
	'Add new file' => 'Yeni dosya ekle',
	'+ Add file' => '+ Dosya ekle',
	'Media' => 'Medya',
	'Filters' => 'Filtreler',
	'Filter...' => 'filtrele...',
	'Refresh' => 'Yenile',
	'Select' => 'Seç',
	'unread notifications' => 'okunmamış bildirim',
	'All notifications read' => 'Tüm bildirimler okundu',
	'Mark all as read' => 'Tümünü okundu işaretle',
	'No notifications.' => 'Bildirim bulunmuyor.',
	'Search name, phone or email...' => 'Ad, telefon veya e-posta ara...',
	'Registered' => 'Kayıt',
	'No customers found.' => 'Müşteri bulunamadı.',
	'Delete category' => 'Kategoriyi Sil',
	'Turkish' => 'Türkçe',
	'English' => 'English',
	'on' => 'açık',
	'off' => 'kapalı',
];

$tr = array_merge($tr, $trExtras);

ksort($en);
ksort($tr);

$writeLang = static function (string $path, array $data): void {
	$lines = ["<?php", "\treturn ["];
	foreach ($data as $k => $v) {
		$lines[] = "\t\t" . var_export($k, true) . ' => ' . var_export($v, true) . ',';
	}
	$lines[] = "\t];";
	file_put_contents($path, implode("\n", $lines) . "\n");
};

$writeLang($enPath, $en);
$writeLang($trPath, $tr);

echo 'Synced ' . count($en) . ' en keys, ' . count($tr) . ' tr keys from ' . count($keys) . " template keys\n";
