<?php

namespace BackupPro\Repository;

use DB;

class LogRepository
{
    public static function log(?int $backupId, string $level, string $action, string $message): int
    {
        $id = DB::insert('backup_pro_logs', [
            'backup_id' => $backupId,
            'level' => $level,
            'action' => $action,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return (int) $id;
    }

    public static function getLogs(int $limit = 50, int $offset = 0, ?string $search = null, ?string $level = null, ?int $backupId = null): array
    {
        $where = ["1=1"];
        $params = [];

        if ($backupId !== null && $backupId > 0) {
            $where[] = "`backup_id` = ?";
            $params[] = $backupId;
        }

        if ($level !== null && $level !== '') {
            $where[] = "`level` = ?";
            $params[] = $level;
        }

        if ($search !== null && $search !== '') {
            $where[] = "(`message` LIKE ? OR `action` LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM `backup_pro_logs` WHERE {$whereClause} ORDER BY `id` DESC LIMIT {$limit} OFFSET {$offset}";
        return DB::execute($sql, $params) ?: [];
    }

    public static function countLogs(?string $search = null, ?string $level = null, ?int $backupId = null): int
    {
        $where = ["1=1"];
        $params = [];

        if ($backupId !== null && $backupId > 0) {
            $where[] = "`backup_id` = ?";
            $params[] = $backupId;
        }

        if ($level !== null && $level !== '') {
            $where[] = "`level` = ?";
            $params[] = $level;
        }

        if ($search !== null && $search !== '') {
            $where[] = "(`message` LIKE ? OR `action` LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $whereClause = implode(' AND ', $where);
        return (int) DB::getValue("SELECT COUNT(*) FROM `backup_pro_logs` WHERE {$whereClause}", $params);
    }

    public static function clear(?int $backupId = null): void
    {
        if ($backupId !== null && $backupId > 0) {
            DB::execute("DELETE FROM `backup_pro_logs` WHERE `backup_id` = ?", [$backupId]);
        } else {
            DB::execute("DELETE FROM `backup_pro_logs`");
        }
    }
}
