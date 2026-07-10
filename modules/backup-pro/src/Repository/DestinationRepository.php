<?php

namespace BackupPro\Repository;

use DB;

class DestinationRepository
{
    public static function save(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        if (isset($data['id']) && (int)$data['id'] > 0) {
            $id = (int)$data['id'];
            unset($data['id']);
            DB::update('backup_pro_destinations', $data, 'id = :id', ['id' => $id]);
            return $id;
        } else {
            $data['created_at'] = $now;
            return (int) DB::insert('backup_pro_destinations', $data);
        }
    }

    public static function findById(int $id): ?array
    {
        $row = DB::getRowSafe('backup_pro_destinations', 'id = ?', [$id]);
        if ($row && !empty($row['config'])) {
            $row['config_decoded'] = json_decode($row['config'], true) ?: [];
        }
        return $row ?: null;
    }

    public static function getAll(): array
    {
        $rows = DB::execute("SELECT * FROM `backup_pro_destinations` ORDER BY `id` DESC") ?: [];
        foreach ($rows as &$row) {
            if (!empty($row['config'])) {
                $row['config_decoded'] = json_decode($row['config'], true) ?: [];
            }
        }
        return $rows;
    }

    public static function getActive(): array
    {
        $rows = DB::execute("SELECT * FROM `backup_pro_destinations` WHERE `active` = 1 ORDER BY `id` DESC") ?: [];
        foreach ($rows as &$row) {
            if (!empty($row['config'])) {
                $row['config_decoded'] = json_decode($row['config'], true) ?: [];
            }
        }
        return $rows;
    }

    public static function delete(int $id): bool
    {
        return DB::execute("DELETE FROM `backup_pro_destinations` WHERE `id` = ?", [$id]) !== false;
    }
}
