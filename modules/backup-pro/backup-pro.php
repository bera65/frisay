<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

require_once dirname(__DIR__, 2) . '/core/ModuleBase.php';
require_once __DIR__ . '/src/Autoloader.php';
BackupPro\Autoloader::register();

use BackupPro\Core\Settings as BackupSettings;

class BackupProModule extends ModuleBase
{
    public string $name = 'backup-pro';
    public string $title = 'Backup & Restore Manager Pro';
    public string $description = 'Sitenizdeki dosya ve veritabanlarını yüksek performanslı, parçalı olarak yedekler ve geri yükler.';
    public string $version = '1.0.0';
    public string $author = 'Can MEMİŞ';

    public array $displayHooks = [];
    public array $defaultDisplayHooks = [];

    public array $apiActions = [
        'create' => 'api/create.php',
        'process' => 'api/process.php',
        'restore' => 'api/restore.php',
        'scan' => 'api/scan.php',
        'pause' => 'api/pause.php',
        'resume' => 'api/resume.php',
        'cancel' => 'api/cancel.php',
        'delete' => 'api/delete.php',
        'download' => 'api/download.php',
        'stats' => 'api/stats.php',
        'backups' => 'api/backups.php',
        'logs' => 'api/logs.php',
        'settings' => 'api/settings.php',
        'schedules' => 'api/schedules.php',
        'destinations' => 'api/destinations.php',
        'clean' => 'api/clean.php',
        'progress' => 'api/progress.php',
    ];

    public function boot(): void
    {
        $this->ensureSchema();
        $this->registerAdminAssets();
    }

    private function ensureSchema(): void
    {
        $tableExists = DB::getValue("SHOW TABLES LIKE 'backup_pro_backups'");
        if (!$tableExists) {
            $sqlFile = __DIR__ . '/install.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if ($sql) {
                    $queries = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($queries as $q) {
                        if ($q !== '') {
                            try {
                                DB::execute($q);
                            } catch (\Throwable $e) {
                                // Schema queries continuation
                            }
                        }
                    }
                }
            }
        }
    }

    private function registerAdminAssets(): void
    {
        global $container, $moduleAdminAssets;

        if (is_array($moduleAdminAssets) && isset($_GET['container']) && $_GET['container'] === 'module-backup-pro') {
            $domain = rtrim((string) Settings::get('DOMAIN'), '/') . '/';
            $moduleAdminAssets['css'][] = $domain . 'modules/backup-pro/assets/css/admin.css';
            $moduleAdminAssets['js'][] = $domain . 'modules/backup-pro/assets/js/admin.js';
        }
    }

    public function install(): bool
    {
        return $this->runSqlFile('install.sql');
    }

    public function uninstall(): bool
    {
        return $this->runSqlFile('uninstall.sql');
    }

    public function adminPage(): void
    {
        global $smarty, $adminToken;

        $settings = BackupSettings::getAll();

        $smarty->assign([
            'adminToken' => $adminToken ?? '',
            'bpSettings' => $settings,
        ]);
    }
}
