<?php

namespace BackupPro\Core;

use DB;

class Settings
{
    private static ?array $cached = null;

    public static function getAll(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $rows = DB::execute("SELECT `setting_key`, `setting_value` FROM `backup_pro_settings`") ?: [];
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        self::$cached = $settings;
        return $settings;
    }

    public static function get(string $key, $default = '')
    {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, $value): bool
    {
        if (is_array($value)) {
            $value = json_encode($value);
        } else {
            $value = (string) $value;
        }

        $exists = DB::getValue("SELECT 1 FROM `backup_pro_settings` WHERE `setting_key` = ?", [$key]);
        if ($exists) {
            $res = DB::update('backup_pro_settings', ['setting_value' => $value], '`setting_key` = :key_where', ['key_where' => $key]);
        } else {
            $res = DB::insert('backup_pro_settings', ['setting_key' => $key, 'setting_value' => $value]);
        }

        self::$cached = null;
        return $res !== false;
    }

    public static function saveAll(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function formatBytes(float $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 Bytes';
        }
        $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), $precision) . ' ' . ($units[$i] ?? 'Bytes');
    }
}
