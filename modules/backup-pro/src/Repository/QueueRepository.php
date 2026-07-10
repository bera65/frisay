<?php

namespace BackupPro\Repository;

use DB;

class QueueRepository
{
    public static function add(int $backupId, string $jobType, int $totalSteps = 1, array $payload = []): int
    {
        $id = DB::insert('backup_pro_queue', [
            'backup_id' => $backupId,
            'job_type' => $jobType,
            'current_step' => 0,
            'total_steps' => $totalSteps,
            'progress_percentage' => 0,
            'locked' => 0,
            'payload' => json_encode($payload),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return (int) $id;
    }

    public static function getNext(): ?array
    {
        $rows = DB::execute("SELECT * FROM `backup_pro_queue` WHERE `locked` = 0 ORDER BY `id` ASC LIMIT 1");
        if ($rows && isset($rows[0])) {
            return $rows[0];
        }

        // Çökme sonrası kilitli kalan işi yeniden dene
        $stale = DB::execute("SELECT * FROM `backup_pro_queue` WHERE `locked` = 1 ORDER BY `id` ASC LIMIT 1");
        if ($stale && isset($stale[0])) {
            self::unlock((int)$stale[0]['id']);
            return $stale[0];
        }

        return null;
    }

    public static function recoverLockedJobsForBackup(int $backupId): void
    {
        $locked = (int)DB::getValue(
            "SELECT COUNT(*) FROM `backup_pro_queue` WHERE `backup_id` = ? AND `locked` = 1",
            [$backupId]
        );
        $unlocked = (int)DB::getValue(
            "SELECT COUNT(*) FROM `backup_pro_queue` WHERE `backup_id` = ? AND `locked` = 0",
            [$backupId]
        );

        if ($locked > 0 && $unlocked === 0) {
            DB::execute(
                "UPDATE `backup_pro_queue` SET `locked` = 0 WHERE `backup_id` = ? AND `locked` = 1",
                [$backupId]
            );
        }
    }

    public static function getProgress(int $backupId): array
    {
        $backup = BackupRepository::findById($backupId);
        if (!$backup) {
            return ['pending' => 0, 'current_pct' => 0];
        }

        if ($backup['status'] === 'completed') {
            return ['pending' => 0, 'current_pct' => 100];
        }
        if ($backup['status'] === 'failed') {
            return ['pending' => 0, 'current_pct' => 0];
        }

        $queueJobs = DB::execute("SELECT * FROM `backup_pro_queue` WHERE `backup_id` = ?", [$backupId]) ?: [];
        $pending = count($queueJobs);

        if ($pending === 0) {
            return ['pending' => 0, 'current_pct' => 99];
        }

        $hasScan = false;
        $hasDump = false;
        $hasCompress = false;
        $hasFinalize = false;

        $dumpJob = null;
        $compressJob = null;

        foreach ($queueJobs as $job) {
            if ($job['job_type'] === 'scan_files') $hasScan = true;
            if ($job['job_type'] === 'dump_db') {
                $hasDump = true;
                $dumpJob = $job;
            }
            if ($job['job_type'] === 'compress_files') {
                $hasCompress = true;
                $compressJob = $job;
            }
            if ($job['job_type'] === 'finalize') $hasFinalize = true;
        }

        $type = $backup['type'];
        $pct = 5; // Scan finished by default

        if ($hasScan) {
            $pct = 2; // scan phase is running
        } else {
            if ($type === 'full') {
                // DB Contribution: 0 to 25
                $dbContribution = 0;
                if ($hasDump) {
                    $payload = json_decode($dumpJob['payload'] ?? '[]', true);
                    $totalTables = isset($payload['tables']) ? count($payload['tables']) : 0;
                    $currentTable = (int)($payload['current_table_index'] ?? 0);
                    $dbRatio = $totalTables > 0 ? ($currentTable / $totalTables) : 0;
                    $dbContribution = $dbRatio * 25;
                } else {
                    $dbContribution = 25; // DB dump is completed
                }

                // File Compression Contribution: 0 to 65
                $compressContribution = 0;
                if ($hasCompress) {
                    $payload = json_decode($compressJob['payload'] ?? '[]', true) ?: [];
                    $totalChunks = (int)($compressJob['total_steps'] ?? 0);
                    if ($totalChunks <= 0 && isset($payload['chunks'])) {
                        $totalChunks = count($payload['chunks']);
                    }
                    $currentChunk = (int)($compressJob['current_step'] ?? ($payload['current_chunk_index'] ?? 0));
                    $compressRatio = $totalChunks > 0 ? ($currentChunk / $totalChunks) : 0;
                    $compressContribution = $compressRatio * 65;
                } elseif ($hasFinalize || $backup['status'] === 'completed') {
                    $compressContribution = 65; // Compression completed
                }

                $pct = 5 + $dbContribution + $compressContribution;

                if ($hasFinalize) {
                    $pct += 3;
                }
            } elseif ($type === 'db_only') {
                // DB Contribution: 0 to 90
                $dbContribution = 0;
                if ($hasDump) {
                    $payload = json_decode($dumpJob['payload'] ?? '[]', true);
                    $totalTables = isset($payload['tables']) ? count($payload['tables']) : 0;
                    $currentTable = (int)($payload['current_table_index'] ?? 0);
                    $dbRatio = $totalTables > 0 ? ($currentTable / $totalTables) : 0;
                    $dbContribution = $dbRatio * 90;
                } else {
                    $dbContribution = 90;
                }

                $pct = 5 + $dbContribution;

                if ($hasFinalize) {
                    $pct += 4;
                }
            } else { // files_only or custom
                // File Compression Contribution: 0 to 90
                $compressContribution = 0;
                if ($hasCompress) {
                    $payload = json_decode($compressJob['payload'] ?? '[]', true) ?: [];
                    $totalChunks = (int)($compressJob['total_steps'] ?? 0);
                    if ($totalChunks <= 0 && isset($payload['chunks'])) {
                        $totalChunks = count($payload['chunks']);
                    }
                    $currentChunk = (int)($compressJob['current_step'] ?? ($payload['current_chunk_index'] ?? 0));
                    $compressRatio = $totalChunks > 0 ? ($currentChunk / $totalChunks) : 0;
                    $compressContribution = $compressRatio * 90;
                } else {
                    $compressContribution = 90;
                }

                $pct = 5 + $compressContribution;

                if ($hasFinalize) {
                    $pct += 4;
                }
            }
        }

        return [
            'pending' => $pending,
            'current_pct' => round(min(99, max(5, $pct)), 1),
        ];
    }

    public static function lock(int $queueId): bool
    {
        return DB::update('backup_pro_queue', ['locked' => 1], 'id = :id', ['id' => $queueId]) !== false;
    }

    public static function unlock(int $queueId): bool
    {
        return DB::update('backup_pro_queue', ['locked' => 0], 'id = :id', ['id' => $queueId]) !== false;
    }

    public static function updateProgress(int $queueId, int $currentStep, int $totalSteps, float $progressPct, array $payload = []): bool
    {
        $data = [
            'current_step' => $currentStep,
            'total_steps' => $totalSteps,
            'progress_percentage' => round($progressPct, 2),
        ];
        if (!empty($payload)) {
            $data['payload'] = json_encode($payload);
        }

        return DB::update('backup_pro_queue', $data, 'id = :id', ['id' => $queueId]) !== false;
    }

    public static function delete(int $queueId): bool
    {
        return DB::execute("DELETE FROM `backup_pro_queue` WHERE `id` = ?", [$queueId]) !== false;
    }

    public static function deleteByBackupId(int $backupId): bool
    {
        return DB::execute("DELETE FROM `backup_pro_queue` WHERE `backup_id` = ?", [$backupId]) !== false;
    }

    public static function clear(): void
    {
        DB::execute("TRUNCATE TABLE `backup_pro_queue`");
    }

    public static function countPending(): int
    {
        return (int) DB::getValue("SELECT COUNT(*) FROM `backup_pro_queue`");
    }

    public static function countPendingByBackupId(int $backupId): int
    {
        return (int) DB::getValue("SELECT COUNT(*) FROM `backup_pro_queue` WHERE `backup_id` = ?", [$backupId]);
    }
}
