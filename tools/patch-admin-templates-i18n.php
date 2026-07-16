<?php
/**
 * Wrap known Turkish admin UI strings with {'English key'|adminT}.
 * Run: php tools/patch-admin-templates-i18n.php
 */
$root = dirname(__DIR__);
$exact = [
	'Yeni ürün' => 'New Product',
	'Ürün düzenle' => 'Edit Product',
	'Önce kaydedin; sonra görselleri sürükle-bırak ile ekleyebilirsiniz.' => 'Save first; then you can add images via drag and drop.',
	'Ürüne bak' => 'View product',
	'Ürün içeriği' => 'Product content',
	'Ad, slug, kısa/uzun açıklama ve SEO alanları dil bazlıdır.' => 'Name, slug, short/long description and SEO fields are per language.',
	'Ürün Adı' => 'Product name',
	'Boş bırakılırsa otomatik' => 'Leave blank for automatic',
	'Kısa Açıklama' => 'Short description',
	'Meta Başlık' => 'Meta title',
	'Meta Açıklama' => 'Meta description',
	'Uzun Açıklama' => 'Long description',
	'Kategori, marka, stok kodu ve ürün tipi.' => 'Category, brand, SKU and product type.',
	'Ürün Türü' => 'Product type',
	'Fiziksel ürün' => 'Physical product',
	'Sanal / dijital ürün' => 'Virtual / digital product',
	'Teslimat Türü' => 'Delivery type',
	'İndirilebilir dosya' => 'Downloadable file',
	'Lisans anahtarı' => 'License key',
	'Metin teslimatı' => 'Text delivery',
	'Ürün Durumu' => 'Product status',
	'Termin Süresi (gün)' => 'Lead time (days)',
	'0 ise genel kargo süresi kullanılır.' => '0 uses the general shipping time.',
	'Ürün Etiketi' => 'Product label',
	'Ürün varyasyonları' => 'Product variations',
	'Ürün seçenekleri' => 'Product options',
	'Seçenek 1' => 'Option 1',
	'Seçenek 2' => 'Option 2',
	'Değer' => 'Value',
	'Satırı sil' => 'Remove row',
	'Boş = ana fiyat' => 'Empty = base price',
	'+ Seçenek Grubu Ekle' => '+ Add option group',
	'Grup adı' => 'Group name',
	'Tüm kategoriler' => 'All categories',
	'Tüm markalar' => 'All brands',
	'Tüm durumlar' => 'All statuses',
	'Ara...' => 'Search...',
	'Kargo düzenle' => 'Edit carrier',
	'Yeni kargo ekle' => 'Add new carrier',
	'Kargo firmaları' => 'Carrier companies',
	'Henüz kargo yok. Soldan ekleyin.' => 'No carriers yet. Add one on the left.',
	'Varsayılan' => 'Default',
	'Sıra' => 'Sort order',
	'Fiyat aralıkları (sepet tutarı → kargo ücreti)' => 'Price ranges (cart total → shipping fee)',
	'+ Aralık ekle' => '+ Add range',
	'Güncelle' => 'Update',
	'Ücret (₺)' => 'Fee (₺)',
	'Fiyat aralığı tanımlı değil.' => 'No price range defined.',
	'Menüyü aç/kapat' => 'Toggle menu',
	'Bu dil kaldırılsın mı? CMS ve çeviri kayıtları silinir.' => 'Remove this language? CMS and translation records will be deleted.',
	'Bu para birimi listeden kaldırılsın mı?' => 'Remove this currency from the list?',
	'Anahtar yenilenecek. Eski anahtar geçersiz olur.' => 'The key will be regenerated. The old key will become invalid.',
	'Bu API anahtarı silinsin mi?' => 'Delete this API key?',
	'Modül kaldırılsın mı? Veriler silinebilir.' => 'Uninstall this module? Data may be deleted.',
	'Sayfa bulunamadı' => 'Page not found',
];

uksort($exact, static function ($a, $b) {
	return strlen($b) <=> strlen($a);
});

$map = [
	// Common actions
	'Kaydet' => 'Save',
	'Filtrele' => 'Filter',
	'Temizle' => 'Clear',
	'Sil' => 'Delete',
	'Düzenle' => 'Edit',
	'Görüntüle' => 'View',
	'Detay' => 'Detail',
	'Detaylar' => 'Details',
	'İptal' => 'Cancel',
	'Evet' => 'Yes',
	'Hayır' => 'No',
	'Ara' => 'Search',
	'Yeni Ekle' => 'Add New',
	'Geri' => 'Back',
	'Yazdır' => 'Print',
	'Onayla' => 'Approve',
	'Reddet' => 'Reject',
	// Nav / sections
	'Genel' => 'General',
	'Katalog' => 'Catalog',
	'Sistem' => 'System',
	'İletişim' => 'Contact',
	'Ayarlar' => 'Settings',
	'Siparişler' => 'Orders',
	'Sipariş' => 'Order',
	'Ürünler' => 'Products',
	'Ürün' => 'Product',
	'Müşteriler' => 'Customers',
	'Müşteri' => 'Customer',
	'Kategoriler' => 'Categories',
	'Kategori' => 'Category',
	'Markalar' => 'Brands',
	'Marka' => 'Brand',
	'Mesajlar' => 'Messages',
	'Bildirimler' => 'Notifications',
	'Modüller' => 'Modules',
	'Kargolar' => 'Shipping',
	'Kuponlar' => 'Coupons',
	'Diller' => 'Languages',
	'Para Birimleri' => 'Currencies',
	'Performans' => 'Performance',
	// Table headers
	'Tarih' => 'Date',
	'Durum' => 'Status',
	'Ad' => 'Name',
	'Ad Soyad' => 'Full name',
	'E-posta' => 'Email',
	'Telefon' => 'Phone',
	'Tutar' => 'Amount',
	'Toplam' => 'Total',
	'İşlem' => 'Action',
	'İşlemler' => 'Actions',
	// Products
	'Yeni Ürün' => 'New Product',
	'Ürün Düzenle' => 'Edit Product',
	'Ürünü Sil' => 'Delete product',
	'Stok' => 'Stock',
	'Fiyat' => 'Price',
	'Aktif' => 'Active',
	'Pasif' => 'Inactive',
	'Görsel' => 'Image',
	'Görseller' => 'Images',
	'Açıklama' => 'Description',
	'SEO' => 'SEO',
	// Orders
	'Sipariş Bilgileri' => 'Order Information',
	'Sipariş Durumu' => 'Order status',
	'Kargo' => 'Shipping',
	'Takip No' => 'Tracking number',
	'Ödeme' => 'Payment',
	'Adres' => 'Address',
	// Messages
	'Konu' => 'Subject',
	'Mesaj' => 'Message',
	'Yanıtla' => 'Reply',
	'Okunmadı' => 'Unread',
	'Okundu' => 'Read',
	// Confirm dialogs (data attributes - English keys for JS)
	'Bu işlemi gerçekleştirmek istediğinize emin misiniz?' => 'Are you sure you want to perform this action?',
	'İşlem Onayı' => 'Confirm action',
	// Performance
	'Önbellek' => 'Cache',
	'OPcache' => 'OPcache',
	'Açık' => 'Enabled',
	'Kapalı' => 'Disabled',
	// Languages page
	'Mağaza dilleri' => 'Store languages',
	'Varsayılan dil' => 'Default language',
	'Dil kodu' => 'Language code',
	'Dil adı' => 'Language name',
	// Empty states
	'Kayıt bulunamadı.' => 'No records found.',
	'Henüz kayıt yok.' => 'No records yet.',
	// API
	'API Anahtarları' => 'API Keys',
	'Anahtar adı' => 'Key name',
	// Cargos
	'Kargo firması' => 'Carrier company',
	'Ücret' => 'Fee',
	'Ücretsiz' => 'Free',
	// Customers
	'Kayıt tarihi' => 'Registration date',
	'Sipariş sayısı' => 'Order count',
	// Modules
	'Modül ara…' => 'Search modules…',
	'Modül kaldırılsın mı?' => 'Uninstall this module?',
	'Kur' => 'Install',
	'Kaldır' => 'Uninstall',
	'Yapılandır' => 'Configure',
	// CMS
	'Sayfa başlığı' => 'Page title',
	'Slug' => 'Slug',
	'İçerik' => 'Content',
	// SEO
	'Meta başlık' => 'Meta title',
	'Meta açıklama' => 'Meta description',
	// Misc
	'Tümü' => 'All',
	'Site Ayarları' => 'Site settings',
	'Son Siparişler' => 'Recent Orders',
	'Hoş geldiniz' => 'Welcome',
	'Çıkış Yap' => 'Sign Out',
	'Giriş Yap' => 'Sign In',
	'Yönetim Paneli' => 'Admin Panel',
];

$wrap = static function (string $content, array $map): array {
	$count = 0;
	foreach ($map as $turkish => $english) {
		$needle = $english;
		$wrapped = "{'{$english}'|adminT}";
		if (strpos($content, $wrapped) !== false) {
			continue;
		}
		// HTML text nodes: >Turkish<
		$patterns = [
			'/>(\s*)' . preg_quote($turkish, '/') . '(\s*)</u',
			'/"(>?' . preg_quote($turkish, '/') . ')"/u',
			"/'" . preg_quote($turkish, '/') . "'/u",
		];
		$replacements = [
			'>$1' . $wrapped . '$2<',
			'"' . $wrapped . '"',
			"'" . $wrapped . "'",
		];
		for ($i = 0; $i < count($patterns); $i++) {
			$content = preg_replace($patterns[$i], $replacements[$i], $content, -1, $c);
			$count += $c;
		}
		// Plain attribute values: placeholder="Turkish"
		$content = preg_replace(
			'/(placeholder|title|aria-label|data-confirm-title|data-confirm-message)="'
			. preg_quote($turkish, '/') . '"/u',
			'$1="' . $wrapped . '"',
			$content,
			-1,
			$c
		);
		$count += $c;
	}
	return [$content, $count];
};

$total = 0;
$dir = $root . '/templates/admin';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($it as $file) {
	if ($file->getExtension() !== 'tpl') {
		continue;
	}
	$path = $file->getPathname();
	$content = file_get_contents($path);
	$count = 0;

	foreach ($exact as $turkish => $english) {
		$wrapped = "{'{$english}'|adminT}";
		if (strpos($content, $wrapped) !== false) {
			continue;
		}
		$content = str_replace($turkish, $wrapped, $content, $c);
		$count += $c;
	}

	[$content, $c] = $wrap($content, $map);
	$count += $c;

	if ($count > 0) {
		file_put_contents($path, $content);
		$total += $count;
		echo basename($path) . ": {$count}\n";
	}
}

echo "Total template wraps: {$total}\n";
