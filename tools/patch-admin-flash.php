<?php
/**
 * Replace common Turkish admin flash messages with adminT() English keys.
 * Run: php tools/patch-admin-flash.php
 */
$root = dirname(__DIR__);
$map = [
	"\$flash = 'Geçersiz istek';" => "\$flash = adminT('Invalid request');",
	"\$error = 'Geçersiz istek';" => "\$error = adminT('Invalid request');",
	"\$sonuc = 'Geçersiz istek';" => "\$sonuc = adminT('Invalid request');",
	"'message' => 'Geçersiz istek'" => "'message' => adminT('Invalid request')",
	"'message' => 'Geçersiz işlem'" => "'message' => adminT('Invalid action')",
	"\$flash = Tools::getValue('sent') === '1' ? 'Yanıt müşteriye gönderildi' : '';" => "\$flash = Tools::getValue('sent') === '1' ? adminT('Reply sent to customer') : '';",
	"\$flash \t= 'Kayıt güncellendi';" => "\$flash = adminT('Record updated');",
	"\$result = ['success' => false, 'message' => 'Schema.org ayarları kaydedilemedi'];" => "\$result = ['success' => false, 'message' => adminT('Schema.org settings could not be saved')];",
	"\$result = ['success' => false, 'message' => 'Geçersiz admin dili'];" => "\$result = ['success' => false, 'message' => adminT('Invalid admin language')];",
	"\$result = ['success' => true, 'message' => 'Admin panel varsayılan dili güncellendi'];" => "\$result = ['success' => true, 'message' => adminT('Default admin language updated')];",
	"\$result = ['success' => false, 'message' => 'Geçersiz işlem'];" => "\$result = ['success' => false, 'message' => adminT('Invalid action')];",
	"\$sonuc = 'Excel dosyası seçin';" => "\$sonuc = adminT('Select an Excel file');",
	"\$sonuc = 'Sadece .xlsx dosyası yükleyebilirsiniz';" => "\$sonuc = adminT('Only .xlsx files are allowed');",
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

echo "Flash message replacements: {$count}\n";
