<?php

namespace BackupPro\Service;

use BackupPro\Core\Settings;
use BackupPro\Repository\BackupRepository;
use BackupPro\Repository\QueueRepository;
use BackupPro\Repository\LogRepository;

class QueueService
{
    private ScannerService $scannerService;
    private DatabaseDumpService $dbDumpService;
    private ArchiveService $archiveService;
    private ValidationService $validationService;
    private StorageService $storageService;
    private RestoreService $restoreService;
    private bool $paused = false;

    public function __construct()
    {
        $this->scannerService = new ScannerService();
        $this->dbDumpService = new DatabaseDumpService();
        $this->archiveService = new ArchiveService();
        $this->validationService = new ValidationService();
        $this->storageService = new StorageService();
        $this->restoreService = new RestoreService();

        $this->paused = Settings::get('queue_paused', '0') === '1';
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function pause(): void
    {
        Settings::set('queue_paused', '1');
        $this->paused = true;
    }

    public function resume(): void
    {
        Settings::set('queue_paused', '0');
        $this->paused = false;
    }

    public function processBatch(): array
    {
        // Raise memory limit for this batch run
        @ini_set('memory_limit', '512M');
        @set_time_limit(120);

        if ($this->isPaused()) {
            return ['processed' => 0, 'remaining' => QueueRepository::countPending(), 'paused' => true];
        }

        $job = QueueRepository::getNext();
        if (!$job) {
            return ['processed' => 0, 'remaining' => 0, 'paused' => false];
        }

        $queueId = (int)$job['id'];
        $backupId = (int)$job['backup_id'];
        $jobType = $job['job_type'];
        $payload = json_decode($job['payload'] ?? '[]', true) ?: [];

        QueueRepository::lock($queueId);

        $backup = BackupRepository::findById($backupId);
        if (!$backup) {
            QueueRepository::delete($queueId);
            return ['processed' => 0, 'remaining' => QueueRepository::countPending(), 'paused' => false];
        }

        $startTime = microtime(true);
        $finishedJob = false;

        try {
            if ($jobType === 'scan_files') {
                $customFolders = $payload['custom_folders'] ?? null;
                $scanData = $this->scannerService->scanFiles($customFolders);

                $backup['total_files'] = $scanData['total_files'];
                BackupRepository::save($backup);

                // Queue next jobs: dump_db (if full or db_only) and compress_files
                if ($backup['type'] === 'full' || $backup['type'] === 'db_only') {
                    $dbScan = $this->scannerService->scanDatabase();
                    $backup['total_tables'] = $dbScan['total_tables'];
                    BackupRepository::save($backup);

                    // Add db dump job
                    QueueRepository::add($backupId, 'dump_db', count($dbScan['tables']), [
                        'tables' => $dbScan['tables'],
                        'current_table_index' => 0,
                        'current_row_offset' => 0
                    ]);
                }

                if ($backup['type'] === 'full' || $backup['type'] === 'files_only' || $backup['type'] === 'custom') {
                    $configuredChunkSize = (int)Settings::get('chunk_size_files', 150);
                    $chunkSize = max(30, min(150, $configuredChunkSize));
                    $fileCount = count($scanData['files']);
                    $totalChunks = $fileCount > 0 ? (int)ceil($fileCount / $chunkSize) : 1;

                    $scanFile = $this->storageService->getScanFilePath($backupId);
                    file_put_contents($scanFile, json_encode($scanData['files'], JSON_UNESCAPED_UNICODE));

                    QueueRepository::add($backupId, 'compress_files', $totalChunks, [
                        'scan_file' => $scanFile,
                        'chunk_size' => $chunkSize,
                        'current_chunk_index' => 0,
                    ]);
                }

                $finishedJob = true;
                LogRepository::log($backupId, 'info', 'SCAN', "Tarama tamamlandı. Toplam dosya: {$scanData['total_files']}");

            } elseif ($jobType === 'dump_db') {
                $tables = $payload['tables'] ?? [];
                $tableIndex = (int)($payload['current_table_index'] ?? 0);
                $rowOffset = (int)($payload['current_row_offset'] ?? 0);

                $tempSqlFile = $this->storageService->getBackupDir() . "temp_db_{$backupId}.sql";

                if ($tableIndex === 0 && $rowOffset === 0) {
                    $this->dbDumpService->initSqlHeader($tempSqlFile);
                }

                if (isset($tables[$tableIndex])) {
                    $tName = $tables[$tableIndex]['name'];

                    if ($rowOffset === 0) {
                        $this->dbDumpService->dumpTableStructure($tName, $tempSqlFile);
                    }

                    $chunkSize = max(500, (int)Settings::get('chunk_size_db_rows', 5000));
                    $dumpedRows = $this->dbDumpService->dumpTableDataChunk($tName, $rowOffset, $chunkSize, $tempSqlFile);

                    if ($dumpedRows < $chunkSize) {
                        // Move to next table
                        $payload['current_table_index'] = $tableIndex + 1;
                        $payload['current_row_offset'] = 0;
                    } else {
                        // Continue current table next chunk
                        $payload['current_row_offset'] = $rowOffset + $dumpedRows;
                    }

                    $pct = (($tableIndex + 1) / max(1, count($tables))) * 100;
                    QueueRepository::updateProgress($queueId, $tableIndex + 1, count($tables), $pct, $payload);
                } else {
                    // All tables finished
                    $this->dbDumpService->finalizeSqlHeader($tempSqlFile);

                    // Add database.sql to zip archive — must use absolute path
                    $zipPath = $this->resolveBackupZipPath($backup['file_path']);

                    $zip = new \ZipArchive();
                    $openFlag = file_exists($zipPath) ? \ZipArchive::CHECKCONS : \ZipArchive::CREATE;
                    $openResult = $zip->open($zipPath, $openFlag);
                    if ($openResult === true) {
                        $zip->addFile($tempSqlFile, 'database.sql');
                        $zip->close();
                    } elseif ($openFlag === \ZipArchive::CHECKCONS) {
                        // Existing zip is corrupt — recreate
                        @unlink($zipPath);
                        $zip2 = new \ZipArchive();
                        if ($zip2->open($zipPath, \ZipArchive::CREATE) === true) {
                            $zip2->addFile($tempSqlFile, 'database.sql');
                            $zip2->close();
                        }
                    } else {
                        throw new \Exception("Zip arşivi oluşturulamadı: {$zipPath} (Hata kodu: {$openResult})");
                    }
                    @unlink($tempSqlFile);

                    $finishedJob = true;
                    LogRepository::log($backupId, 'info', 'DUMP_DB', "Veritabanı dökümü tamamlandı.");
                }

            } elseif ($jobType === 'compress_files') {
                $chunkIndex = (int)($payload['current_chunk_index'] ?? 0);
                $totalChunks = max(1, (int)$job['total_steps']);
                $chunkFiles = $this->loadCompressChunkFiles($payload, $chunkIndex);

                if ($chunkFiles !== []) {
                    $zipPath = $this->resolveBackupZipPath($backup['file_path']);
                    if ($zipPath !== $backup['file_path']) {
                        $backup['file_path'] = $zipPath;
                        BackupRepository::save($backup);
                    }

                    $compLevel = Settings::get('compression_level', 'balanced');
                    $this->archiveService->addFilesToZipChunk($zipPath, $chunkFiles, $compLevel);

                    $payload['current_chunk_index'] = $chunkIndex + 1;
                    $pct = (($chunkIndex + 1) / $totalChunks) * 100;
                    QueueRepository::updateProgress($queueId, $chunkIndex + 1, $totalChunks, $pct, $payload);

                    if ($chunkIndex + 1 >= $totalChunks) {
                        if (!empty($payload['scan_file'])) {
                            @unlink($payload['scan_file']);
                        }
                        $finishedJob = true;
                        LogRepository::log($backupId, 'info', 'COMPRESS', "Dosya arşivleme işlemi tamamlandı.");
                    }
                } elseif ($chunkIndex >= $totalChunks) {
                    if (!empty($payload['scan_file'])) {
                        @unlink($payload['scan_file']);
                    }
                    $finishedJob = true;
                }

            } elseif ($jobType === 'finalize') {
                $zipPath = $backup['file_path'];
                if (file_exists($zipPath)) {
                    $backup['file_size'] = filesize($zipPath);
                    $backup['checksum_sha256'] = $this->validationService->calculateSha256($zipPath);
                    $backup['checksum_crc32'] = $this->validationService->calculateCrc32($zipPath);
                }

                $backup['status'] = 'completed';
                $backup['error_message'] = null;
                $backup['duration_seconds'] = max(1, time() - strtotime($backup['created_at']));
                BackupRepository::save($backup);

                $finishedJob = true;
                LogRepository::log($backupId, 'success', 'FINALIZE', "Yedekleme başarıyla tamamlandı. Dosya boyutu: " . \BackupPro\Core\Settings::formatBytes($backup['file_size']));
            }

            if ($finishedJob) {
                QueueRepository::delete($queueId);

                // Only finalize when ALL jobs for THIS backup are done
                $remainingForThisBackup = QueueRepository::countPendingByBackupId($backupId);
                if ($remainingForThisBackup === 0 && $backup['status'] !== 'completed') {
                    QueueRepository::add($backupId, 'finalize', 1, []);
                }
            } else {
                QueueRepository::unlock($queueId);
            }

        } catch (\Throwable $e) {
            QueueRepository::unlock($queueId);
            $backup['status'] = 'failed';
            $backup['error_message'] = $e->getMessage();
            BackupRepository::save($backup);

            LogRepository::log($backupId, 'error', 'QUEUE', "Kuyruk adımında hata: " . $e->getMessage());
        }

        return [
            'processed' => 1,
            'remaining' => QueueRepository::countPending(),
            'paused' => false,
            'memory_used' => memory_get_usage(true)
        ];
    }

    private function resolveBackupZipPath(string $filePath): string
    {
        return $this->storageService->resolveFilePath($filePath);
    }

    private function loadCompressChunkFiles(array $payload, int $chunkIndex): array
    {
        if (!empty($payload['scan_file']) && is_file($payload['scan_file'])) {
            $allFiles = json_decode((string)file_get_contents($payload['scan_file']), true) ?: [];
            $chunkSize = max(1, (int)($payload['chunk_size'] ?? 150));
            $offset = $chunkIndex * $chunkSize;
            return array_slice($allFiles, $offset, $chunkSize);
        }

        $chunks = $payload['chunks'] ?? [];
        return $chunks[$chunkIndex] ?? [];
    }
}
