<?php
session_start();

require_once dirname(__DIR__) . '/core/Installer.php';

if (Installer::isInstalled()) {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$installDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/install'));
	$appBase = rtrim(dirname($installDir), '/');
	$siteUrl = $scheme . '://' . $host . ($appBase === '' ? '/' : $appBase . '/');
	$adminUrl = $siteUrl . 'admin/';
	?><!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FShop — Zaten Kurulu</title>
	<link rel="stylesheet" href="assets/install.css">
</head>
<body>
<div class="install-wrap">
	<div class="install-card">
		<h1>Sistem zaten kurulu</h1>
		<p class="install-lead"><code>config/env.php</code> dosyası mevcut olduğu için kurulum sihirbazı tekrar çalıştırılmıyor.</p>
		<ul class="install-summary">
			<li><strong>Mağaza:</strong> <a href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?></a></li>
			<li><strong>Admin:</strong> <a href="<?php echo htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8'); ?></a></li>
		</ul>
		<h2>Sıfırdan kurulum için</h2>
		<ol class="install-note">
			<li><code>config/env.php</code> dosyasını yedekleyip silin veya yeniden adlandırın</li>
			<li>İsteğe bağlı: <code>config/installed.lock</code> dosyasını da silin</li>
			<li>Boş bir MySQL veritabanı hazırlayın</li>
			<li>Bu sayfayı yenileyin veya <code>/install/</code> adresine tekrar girin</li>
		</ol>
		<div class="install-actions">
			<a class="install-btn" href="<?php echo htmlspecialchars($siteUrl, ENT_QUOTES, 'UTF-8'); ?>">Mağazaya Git</a>
			<a class="install-btn install-btn-muted" href="<?php echo htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8'); ?>">Admin Paneli</a>
		</div>
	</div>
</div>
</body>
</html><?php
	exit;
}

$step = max(1, min(4, (int) ($_POST['step'] ?? $_GET['step'] ?? 1)));
$error = '';
$result = null;
$requirements = Installer::requirements();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($step === 2) {
		$_SESSION['install_db'] = [
			'db_host' => trim((string) ($_POST['db_host'] ?? 'localhost')),
			'db_name' => trim((string) ($_POST['db_name'] ?? '')),
			'db_user' => trim((string) ($_POST['db_user'] ?? '')),
			'db_pass' => (string) ($_POST['db_pass'] ?? ''),
		];

		$test = Installer::testDatabase($_SESSION['install_db']);

		if ($test['success']) {
			header('Location: ?step=3');
			exit;
		}

		$error = $test['message'];
	} elseif ($step === 3) {
		if (empty($_SESSION['install_db'])) {
			header('Location: ?step=2');
			exit;
		}

		$payload = array_merge($_SESSION['install_db'], [
			'site_name' => trim((string) ($_POST['site_name'] ?? 'FShop')),
			'site_url' => trim((string) ($_POST['site_url'] ?? '')),
			'rewrite_base' => trim((string) ($_POST['rewrite_base'] ?? '/')),
			'admin_name' => trim((string) ($_POST['admin_name'] ?? 'Site Yöneticisi')),
			'admin_email' => trim((string) ($_POST['admin_email'] ?? '')),
			'admin_password' => (string) ($_POST['admin_password'] ?? ''),
			'install_demo' => !empty($_POST['install_demo']) ? 1 : 0,
			'shop_lang' => (string) ($_POST['shop_lang'] ?? 'tr'),
			'admin_lang' => (string) ($_POST['admin_lang'] ?? 'tr'),
			'theme' => (string) ($_POST['theme'] ?? 'blue'),
		]);

		$result = Installer::install($payload);

		if ($result['success']) {
			unset($_SESSION['install_db']);
			$_SESSION['install_done'] = $result;
			header('Location: ?step=4');
			exit;
		}

		$error = $result['message'];
	}
}

if ($step === 4 && empty($_SESSION['install_done'])) {
	header('Location: ?step=1');
	exit;
}

$done = $_SESSION['install_done'] ?? null;
$db = $_SESSION['install_db'] ?? [
	'db_host' => 'localhost',
	'db_name' => 'fshop',
	'db_user' => 'root',
	'db_pass' => '',
];

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$installDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/install'));
$appBase = rtrim(dirname($installDir), '/');
$guessUrl = $scheme . '://' . $host . ($appBase === '' ? '/' : $appBase . '/');
$guessRewrite = ($appBase === '' || $appBase === '/') ? '/' : $appBase . '/';

?><!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FShop Kurulum</title>
	<link rel="stylesheet" href="assets/install.css">
</head>
<body>
<div class="install-wrap">
	<div class="install-card">
		<h1>FShop Kurulum Sihirbazı</h1>
		<p class="install-lead">Veritabanını bağlayın, admin hesabını oluşturun ve mağazanızı başlatın.</p>

		<ol class="install-steps">
			<li class="<?php if ($step >= 1) { ?>active<?php } ?>">Gereksinimler</li>
			<li class="<?php if ($step >= 2) { ?>active<?php } ?>">Veritabanı</li>
			<li class="<?php if ($step >= 3) { ?>active<?php } ?>">Site & Admin</li>
			<li class="<?php if ($step >= 4) { ?>active<?php } ?>">Tamamlandı</li>
		</ol>

		<?php if ($error !== '') { ?>
			<div class="install-alert install-alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php } ?>

		<?php if ($step === 1) { ?>
			<h2>Sistem Gereksinimleri</h2>
			<ul class="install-checks">
				<?php foreach ($requirements['items'] as $item) { ?>
				<li class="<?php echo $item['ok'] ? 'ok' : 'fail'; ?>">
					<strong><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
					<span><?php echo htmlspecialchars($item['hint'], ENT_QUOTES, 'UTF-8'); ?></span>
				</li>
				<?php } ?>
			</ul>
			<div class="install-actions">
				<?php if ($requirements['ok']) { ?>
					<a class="install-btn" href="?step=2">Devam Et</a>
				<?php } else { ?>
					<span class="install-muted">Eksik gereksinimleri tamamlayıp sayfayı yenileyin.</span>
				<?php } ?>
			</div>
		<?php } elseif ($step === 2) { ?>
			<h2>Veritabanı Bağlantısı</h2>
			<form method="post" class="install-form">
				<input type="hidden" name="step" value="2">
				<label>Sunucu
					<input type="text" name="db_host" value="<?php echo htmlspecialchars($db['db_host'], ENT_QUOTES, 'UTF-8'); ?>" required>
				</label>
				<label>Veritabanı Adı
					<input type="text" name="db_name" value="<?php echo htmlspecialchars($db['db_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
				</label>
				<label>Kullanıcı
					<input type="text" name="db_user" value="<?php echo htmlspecialchars($db['db_user'], ENT_QUOTES, 'UTF-8'); ?>" required>
				</label>
				<label>Şifre
					<input type="password" name="db_pass" value="<?php echo htmlspecialchars($db['db_pass'], ENT_QUOTES, 'UTF-8'); ?>">
				</label>
				<p class="install-note">Veritabanının önceden oluşturulmuş olması gerekir. Kurulum tabloları sıfırdan oluşturulur.</p>
				<div class="install-actions">
					<a class="install-btn install-btn-muted" href="?step=1">Geri</a>
					<button type="submit" class="install-btn">Bağlantıyı Test Et</button>
				</div>
			</form>
		<?php } elseif ($step === 3) { ?>
			<h2>Site ve Yönetici</h2>
			<form method="post" class="install-form">
				<input type="hidden" name="step" value="3">
				<label>Site Adı
					<input type="text" name="site_name" value="FShop Mağaza" required>
				</label>
				<label>Site Adresi (DOMAIN)
					<input type="url" name="site_url" value="<?php echo htmlspecialchars($guessUrl, ENT_QUOTES, 'UTF-8'); ?>" required>
				</label>
				<label>RewriteBase (.htaccess)
					<input type="text" name="rewrite_base" value="<?php echo htmlspecialchars($guessRewrite, ENT_QUOTES, 'UTF-8'); ?>" required>
				</label>
				<label>Mağaza teması
					<select name="theme">
						<option value="blue" selected>Blue (önerilen)</option>
						<option value="default">Varsayılan</option>
						<option value="prime">Prime</option>
					</select>
				</label>
				<label>Mağaza dili (varsayılan)
					<select name="shop_lang">
						<option value="tr" selected>Türkçe</option>
						<option value="en">English</option>
					</select>
				</label>
				<label>Admin panel dili (varsayılan)
					<select name="admin_lang">
						<option value="tr" selected>Türkçe</option>
						<option value="en">English</option>
					</select>
				</label>
				<label>Admin Ad Soyad
					<input type="text" name="admin_name" value="Site Yöneticisi" required>
				</label>
				<label>Admin E-posta
					<input type="email" name="admin_email" required>
				</label>
				<label>Admin Şifre
					<input type="password" name="admin_password" minlength="8" required>
				</label>
				<label class="install-checkbox">
					<input type="checkbox" name="install_demo" value="1" checked>
					Demo ürünler ve örnek veriler yüklensin
				</label>
				<div class="install-actions">
					<a class="install-btn install-btn-muted" href="?step=2">Geri</a>
					<button type="submit" class="install-btn">Kurulumu Başlat</button>
				</div>
			</form>
		<?php } else { ?>
			<h2>Kurulum Tamamlandı</h2>
			<p>Mağazanız kullanıma hazır.</p>
			<ul class="install-summary">
				<li><strong>Admin e-posta:</strong> <?php echo htmlspecialchars((string) ($done['admin_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></li>
				<li><strong>Mağaza:</strong> <a href="../"><?php echo htmlspecialchars($guessUrl, ENT_QUOTES, 'UTF-8'); ?></a></li>
				<li><strong>Admin:</strong> <a href="../admin/">Admin Paneli</a></li>
				<li><strong>Döviz cron URL:</strong><br><code><?php echo htmlspecialchars((string) ($done['cron_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></code></li>
			</ul>
			<p class="install-note">Kurulumdan sonra <code>install/</code> klasörüne web erişimini kapatmanız önerilir. <code>config/env.example.php</code> dosyasını referans alarak yedekleme ve canlı ortam ayarlarını yapın.</p>
			<div class="install-actions">
				<a class="install-btn" href="../">Mağazaya Git</a>
				<a class="install-btn install-btn-muted" href="../admin/">Admin Paneli</a>
			</div>
		<?php } ?>
	</div>
</div>
</body>
</html>
