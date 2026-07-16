<?php
$root = dirname(__DIR__);
$map = [
	"'Siparişler'" => "'Orders'",
	"'Temalar'" => "'Themes'",
	"'Kuponlar & Kampanyalar'" => "'Coupons & Promotions'",
	"'Performans'" => "'Performance'",
	"'Mesaj Bulunamadı'" => "'Message not found'",
	"'Sipariş Mesajları'" => "'Order Messages'",
	"'Mesaj Detayı'" => "'Message details'",
	"'Ürün Bulunamadı'" => "'Product not found'",
	"'Yeni Ürün'" => "'New Product'",
	"'Ürün Düzenle'" => "'Edit product'",
	"'CMS Sayfaları'" => "'CMS Pages'",
	"'İptaller'" => "'Cancellations'",
	"'Sipariş Bulunamadı'" => "'Order not found'",
	"'Marka Bulunamadı'" => "'Brand not found'",
	"'Yeni Marka'" => "'New brand'",
	"'Marka Düzenle'" => "'Edit brand'",
	"'İadeler'" => "'Returns'",
	"'Site Ayarları'" => "'Site settings'",
	"'Kategori Bulunamadı'" => "'Category not found'",
	"'Yeni Kategori'" => "'New category'",
	"'Kategori Düzenle'" => "'Edit category'",
	"'Bildirimler'" => "'Notifications'",
	"'İade Talebi Bulunamadı'" => "'Return request not found'",
	"'İptal Talebi Bulunamadı'" => "'Cancel request not found'",
	"'Sayfa Bulunamadı'" => "'Page not found'",
	"'Yeni CMS Sayfası'" => "'New CMS page'",
	"'Kampanya Bulunamadı'" => "'Promotion not found'",
	"'Yeni Sepet Kampanyası'" => "'New Cart Promotion'",
	"'Diller'" => "'Languages'",
	"'Para Birimleri'" => "'Currencies'",
	"'Gösterge Paneli'" => "'Dashboard'",
	"'İletişim Mesajları'" => "'Contact messages'",
	"'API Anahtarları'" => "'API Keys'",
	"'Kargolar'" => "'Shipping'",
	"'Müşteri Bulunamadı'" => "'Customer not found'",
	"'Markalar'" => "'Brands'",
	"'Kategoriler'" => "'Categories'",
	"'Kupon Bulunamadı'" => "'Coupon not found'",
	"'Yeni Kupon'" => "'New coupon'",
	"'Müşteriler'" => "'Customers'",
	"'Admin Giriş'" => "'Admin login'",
	"'SEO Ayarları'" => "'SEO settings'",
	"'Modüller'" => "'Modules'",
	"'Modül Bulunamadı'" => "'Module not found'",
	"'Ürünler'" => "'Products'",
];

$count = 0;
foreach (glob($root . '/container/admin/*.php') as $file) {
	$content = file_get_contents($file);
	$new = str_replace(array_keys($map), array_values($map), $content, $c);
	if ($c > 0) {
		file_put_contents($file, $new);
		$count += $c;
	}
}

// Dynamic titles with concatenation
$files = [
	'order.php' => [
		"AdminPage::add('order', 'Sipariş #' . \$order['reference']);" => "AdminPage::add('order', adminT('Order #') . \$order['reference']);",
	],
	'return.php' => [
		"AdminPage::add('return', 'İade #' . \$return['id_return']);" => "AdminPage::add('return', adminT('Return #') . \$return['id_return']);",
	],
	'cancel.php' => [
		"AdminPage::add('cancel', 'İptal #' . \$cancel['id_cancel']);" => "AdminPage::add('cancel', adminT('Cancel #') . \$cancel['id_cancel']);",
	],
	'order-print.php' => [
		"AdminPage::add('order-print', 'Sipariş #' . \$order['reference'], true);" => "AdminPage::add('order-print', adminT('Order #') . \$order['reference'], true);",
	],
	'coupon.php' => [
		"AdminPage::add('coupon', \$idCoupon > 0 ? 'Kupon: ' . \$coupon['code'] : 'Yeni Kupon');" => "AdminPage::add('coupon', \$idCoupon > 0 ? adminT('Coupon:') . ' ' . \$coupon['code'] : adminT('New coupon'));",
	],
	'cart-promotion.php' => [
		"AdminPage::add('cart-promotion', \$idPromotion > 0 ? 'Kampanya: ' . \$promotion['name'] : 'Yeni Sepet Kampanyası');" => "AdminPage::add('cart-promotion', \$idPromotion > 0 ? adminT('Promotion:') . ' ' . \$promotion['name'] : adminT('New Cart Promotion'));",
	],
	'cms-edit.php' => [
		"AdminPage::add('cms-edit', \$isNew ? 'Yeni CMS Sayfası' : 'CMS: ' . (\$form['title'] ?? \$form['slug']));" => "AdminPage::add('cms-edit', \$isNew ? adminT('New CMS page') : adminT('CMS:') . ' ' . (\$form['title'] ?? \$form['slug']));",
	],
];

foreach ($files as $name => $replacements) {
	$path = $root . '/container/admin/' . $name;
	$content = file_get_contents($path);
	foreach ($replacements as $from => $to) {
		$content = str_replace($from, $to, $content);
	}
	file_put_contents($path, $content);
}

echo "Container title replacements: {$count}\n";
