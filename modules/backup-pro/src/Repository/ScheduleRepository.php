<?php

namespace BackupPro\Repository;

use DB;

class ScheduleRepository
{
    public static function save(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        if (isset($data['id']) && (int)$data['id'] > 0) {
            $id = (int)$data['id'];
            unset($data['id']);
            DB::update('backup_pro_schedules', $data, 'id = :id', ['id' => $id]);
            return $id;
        } else {
            $data['created_at'] = $now;
            return (int) DB::insert('backup_pro_schedules', $data);
        }
    }

    public static function findById(int $id): ?array
    {
        return DB::getRowSafe('backup_pro_schedules', 'id = ?', [$id]) ?: null;
    }

    public static function getAll(): array
    {
        return DB::execute("SELECT * FROM `backup_pro_schedules` ORDER BY `id` DESC") ?: [];
    }

    public static function getActiveDue(): array
    {
        $now = date('Y-m-d H:i:s');
        return DB::execute("SELECT * FROM `backup_pro_schedules` WHERE `active` = 1 AND (`next_run` IS NULL OR `next_run` <= ?)", [$now]) ?: [];
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `backup_pro_schedules` WHERE `id` = ?", [$id]) !== false;
    }
}
