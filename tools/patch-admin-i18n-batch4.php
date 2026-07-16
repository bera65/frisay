<?php
$root = dirname(__DIR__);
$dir = $root . '/templates/admin';
$map = [
	// templates.tpl
	'>Önizle<' => ">{'Preview'|adminT}<",

	// theme-customize.tpl
	'&larr; Tüm temalar' => "{'← All themes'|adminT}",
	"{'Active'|adminT} tema" => "{'Active theme'|adminT}",
	'Siteyi Önizle' => "{'Preview site'|adminT}",
	'Düzen &amp; Görünüm' => "{'Layout & appearance'|adminT}",
	'Kayıt <code>custom.css</code> dosyasına yazılır.' => "{'Saved to the <code>custom.css</code> file.'|adminT}",
	'Header varyantları <code>templates/{$editTheme|escape}/_mini/header*.tpl</code> dosyalarından algılanır.' => "{'Header variants are detected from <code>templates/{\$editTheme|escape}/_mini/header*.tpl</code> files.'|adminT}",
	'Renkler' => "{'Colors'|adminT}",
	'Kayıt <code>templates/{$editTheme|escape}/css/colors.css</code> dosyasına yazılır.' => "{'Saved to <code>templates/{\$editTheme|escape}/css/colors.css</code>.'|adminT}",
	"Değişiklikleri {'Save'|adminT}" => "{'Save changes'|adminT}",
	'Bu tema için <code>theme.schema.json</code> tanımlı değil. Özelleştirme seçenekleri eklemek için tema klasörüne şema dosyası ekleyin.' => "{'No <code>theme.schema.json</code> for this theme. Add a schema file to the theme folder to enable customization options.'|adminT}",
	'Önizleme' => "{'Preview'|adminT}",
	'İpucu' => "{'Tip'|adminT}",
	'Tema geliştiricileri <code>theme.schema.json</code> ile hangi alanların adminde görüneceğini tanımlar.' => "{'Theme developers define which fields appear in admin via <code>theme.schema.json</code>.'|adminT}",
	'Yeni tema eklemek için galeri sayfasındaki <strong>Tema Yükle</strong> veya <strong>Tema Kopyala</strong> seçeneklerini kullanın.' => "{'To add a new theme, use <strong>Upload theme</strong> or <strong>Copy theme</strong> on the gallery page.'|adminT}",
	'Site Logoları' => "{'Site logos'|adminT}",
	"JPG, PNG, WEBP, GIF veya SVG — en fazla 2 MB. {'File'|adminT}lar <code>img/</code> klasörüne kaydedilir." => "{'JPG, PNG, WEBP, GIF or SVG — max 2 MB. Files are saved to the <code>img/</code> folder.'|adminT}",
	'>Yükle<' => ">{'Upload'|adminT}<",

	// order-print.tpl
	' — Yazdır</title>' => " — {'Print'|adminT}</title>",
	'>Kapat<' => ">{'Close'|adminT}<",
	'Sipariş Fişi' => "{'Order slip'|adminT}",
	'Durum:' => "{'Status:'|adminT}",
	'Teslimat Adresi' => "{'Delivery address'|adminT}",
	'VKN/TCKN:' => "{'Tax/ID no:'|adminT}",
	'Sipariş Notu' => "{'Order note'|adminT}",

	// pagination.tpl
	"aria-label=\"{'Page'|adminT}lama\"" => "aria-label=\"{'Pagination'|adminT}\"",
	'>Önceki<' => ">{'Previous'|adminT}<",
	'>Sonraki<' => ">{'Next'|adminT}<",

	// module-config-empty.tpl
	'Bu modül için yapılandırma şablonu henüz tanımlı değil.' => "{'No configuration template is defined for this module yet.'|adminT}",
	'Modül klasörüne <code>assets/templates/admin/admin.tpl</code> ekleyin' => "{'Add <code>assets/templates/admin/admin.tpl</code> to the module folder'|adminT}",
	've isteğe bağlı olarak <code>adminPage()</code> metodunu doldurun.' => "{'and optionally implement the <code>adminPage()</code> method.'|adminT}",
	"{'Module details'|adminT}na dön" => "{'Back to module details'|adminT}",

	// modules.tpl
	"{'No modules found yet.'|adminT} <code>modules/</code> klasörüne modül ekleyin." => "{'No modules found yet.'|adminT} {'Add modules to the'|adminT} <code>modules/</code> {'folder.'|adminT}",

	// module.tpl
	'Modülün sitede hangi alanda görüneceğini seçin. Mağazada' => "{'Choose where the module appears on the site. On the storefront'|adminT}",
	' gibi hook noktaları kullanılır.' => "{' hook points are used.'|adminT}",
	"Kurulumda varsayılan hook'lar otomatik atanır; kurduktan sonra buradan değiştirebilirsiniz." => "{'Default hooks are assigned on install; you can change them here after installation.'|adminT}",
	"Hook'lar modülün <code>boot()</code> metodunda kayıt olur ve mağaza/admin akışına müdahale eder." => "{'Hooks are registered in the module <code>boot()</code> method and affect storefront/admin flow.'|adminT}",
	'Modül ayarları <code>{$mod.name|escape}</code> klasöründeki <code>adminPage()</code> ve <code>assets/templates/admin/admin.tpl</code> ile yönetilir.' => "{'Module settings are managed via <code>adminPage()</code> and <code>assets/templates/admin/admin.tpl</code> in the <code>{\$mod.name|escape}</code> folder.'|adminT}",

	// media-library-modal.tpl
	'aria-label="Kapat"' => "aria-label=\"{'Close'|adminT}\"",
	'Eylemler' => "{'Actions'|adminT}",
	'title="Yeni dosya ekle"' => "title=\"{'Add new file'|adminT}\"",
	"+ {'File'|adminT} ekle" => "{'+ Add file'|adminT}",
	'title="Yeni klasör"' => "title=\"{'New folder'|adminT}\"",
	'>Medya<' => ">{'Media'|adminT}<",
	'Filtreler' => "{'Filters'|adminT}",
	'placeholder="filtrele..."' => "placeholder=\"{'Filter...'|adminT}\"",
	'>Yenile<' => ">{'Refresh'|adminT}<",
	'>Seç<' => ">{'Select'|adminT}<",

	// notifications.tpl
	' okunmamış bildirim' => "{' unread notifications'|adminT}",
	'Tüm bildirimler okundu' => "{'All notifications read'|adminT}",
	'Tümünü okundu işaretle' => "{'Mark all as read'|adminT}",
	'Bildirim bulunmuyor.' => "{'No notifications.'|adminT}",

	// customers.tpl
	'placeholder="Ad, telefon veya e-posta ara..."' => "placeholder=\"{'Search name, phone or email...'|adminT}\"",
	'>Kayıt<' => ">{'Registered'|adminT}<",
	'Müşteri bulunamadı.' => "{'No customers found.'|adminT}",

	// categories.tpl
	'data-confirm-title="Kategoriyi Sil"' => "data-confirm-title=\"{'Delete category'|adminT}\"",
	'data-confirm-message="Bu kategoriyi silmek istediğinize emin misiniz? Kategori kaydı kalıcı olarak kaldırılacaktır."' => "data-confirm-message=\"{'Are you sure you want to delete this category? The category record will be permanently removed.'|adminT}\"",

	// cancel.tpl
	'<strong>Sipariş:</strong>' => "<strong>{'Order:'|adminT}</strong>",

	// languages.tpl - use adminT for language names in dropdown
	"{if $code == 'tr'}{'Turkish'|adminT}{else}{'English'|adminT}{/if}" => "{if $code == 'tr'}{'Turkish'|adminT}{else}{'English'|adminT}{/if}",
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
