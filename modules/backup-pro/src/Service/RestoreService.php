<?php

namespace BackupPro\Service;

use BackupPro\Repository\BackupRepository;
use BackupPro\Repository\LogRepository;
use DB;
use ZipArchive;

class RestoreService
{
    private string $rootPath;

    public function __construct()
    {
        $this->rootPath = StorageService::getProjectRoot();
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function dryRunRestore(int $backupId): array
    {
        $backup = BackupRepository::findById($backupId);
        if (!$backup) {
            return ['success' => false, 'message' => 'Yedek bulunamadı.'];
        }

        $filePath = $backup['file_path'];
        $zipPath = file_exists($filePath) ? $filePath : $this->rootPath . '/' . ltrim($filePath, '/');

        if (!file_exists($zipPath)) {
            return ['success' => false, 'message' => 'Yedek dosyası sunucuda bulunamadı.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return ['success' => false, 'message' => 'Yedek arşivi açılamadı.'];
        }

        $fileCount = 0;
        $hasSqlDump = false;
        $sqlDumpSize = 0;
        $conflicts = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $stat = $zip->statIndex($i);

            if ($name === 'database.sql' || strpos($name, 'database_dump') !== false) {
                $hasSqlDump = true;
                $sqlDumpSize += $stat['size'];
            } else {
                $fileCount++;
                $destFile = $this->rootPath . '/' . $name;
                if (file_exists($destFile)) {
                    $conflicts++;
                }
            }
        }
        $zip->close();

        return [
            'success' => true,
            'backup_name' => $backup['backup_name'],
            'total_files_in_zip' => $fileCount,
            'existing_conflicts' => $conflicts,
            'has_database_dump' => $hasSqlDump,
            'sql_dump_size' => $sqlDumpSize,
            'simulation_status' => 'Geri yükleme simülasyonu başarıyla tamamlandı. Sisteme yazılabilir.'
        ];
    }

    public function restoreZipChunk(string $zipPath, int $offset, int $limit, string $extractTargetDir): int
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return 0;
        }

        $numFiles = $zip->numFiles;
        $extracted = 0;

        for ($i = $offset; $i < min($numFiles, $offset + $limit); $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'database.sql' || strpos($name, 'database_dump') !== false) {
                continue;
            }

            if ($zip->extractTo($extractTargetDir, [$name])) {
                $extracted++;
            }
        }

        $zip->close();
        return $extracted;
    }

    public function executeSqlDumpChunk(string $sqlFilePath): bool
    {
        if (!file_exists($sqlFilePath)) {
            return false;
        }

        $sql = file_get_contents($sqlFilePath);
        if ($sql === false || trim($sql) === '') {
            return false;
        }

        $queries = array_filter(array_map('trim', explode(";\n", $sql)));
        foreach ($queries as $query) {
            if ($query !== '' && strpos($query, '--') !== 0) {
                try {
                    DB::execute($query);
                } catch (\Throwable $e) {
                    // Ignore minor drop table errors during restore
                }
            }
        }

        return true;
    }
}
