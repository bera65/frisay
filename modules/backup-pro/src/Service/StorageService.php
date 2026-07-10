<?php

namespace BackupPro\Service;

use BackupPro\Repository\DestinationRepository;
use BackupPro\Repository\LogRepository;

class StorageService
{
    private string $backupDir;

    public static function getProjectRoot(): string
    {
        return rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');
    }

    public function __construct()
    {
        $this->backupDir = self::getProjectRoot() . '/backups/';
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }

        // Web erişimini engelle
        $htaccess = $this->backupDir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\nOptions -Indexes\n");
        }
    }

    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    public function getScanFilePath(int $backupId): string
    {
        return $this->backupDir . 'scan_' . $backupId . '.json';
    }

    public function resolveFilePath(string $filePath): string
    {
        $normalized = str_replace('\\', '/', $filePath);
        if ($normalized !== '' && file_exists($normalized)) {
            return $normalized;
        }

        $candidate = $this->backupDir . basename($normalized);
        if (file_exists($candidate)) {
            return $candidate;
        }

        return $normalized;
    }

    public function uploadToDestinations(int $backupId, string $localFilePath, array $destinationIds): array
    {
        $results = [];
        foreach ($destinationIds as $destId) {
            $dest = DestinationRepository::findById((int)$destId);
            if (!$dest || !$dest['active']) {
                continue;
            }

            $driver = $dest['driver'];
            $config = $dest['config_decoded'];

            if ($driver === 'ftp' || $driver === 'ftps') {
                $res = $this->uploadViaFtp($localFilePath, $config);
                $results[$dest['name']] = $res;
                LogRepository::log($backupId, $res ? 'success' : 'error', 'STORAGE', "FTP yüklemesi ({$dest['name']}): " . ($res ? 'Başarılı' : 'Başarısız'));
            } elseif ($driver === 'sftp') {
                $res = $this->uploadViaSftp($localFilePath, $config);
                $results[$dest['name']] = $res;
                LogRepository::log($backupId, $res ? 'success' : 'error', 'STORAGE', "SFTP yüklemesi ({$dest['name']}): " . ($res ? 'Başarılı' : 'Başarısız'));
            } else {
                // Local or generic driver fallback
                $results[$dest['name']] = true;
            }
        }
        return $results;
    }

    private function uploadViaFtp(string $filePath, array $config): bool
    {
        if (!function_exists('ftp_connect')) {
            return false;
        }

        $host = $config['host'] ?? '';
        $port = (int)($config['port'] ?? 21);
        $user = $config['user'] ?? '';
        $pass = $config['password'] ?? '';
        $path = rtrim($config['remote_path'] ?? '', '/') . '/';

        if ($host === '' || $user === '') return false;

        $conn = @ftp_connect($host, $port, 30);
        if (!$conn) return false;

        if (!@ftp_login($conn, $user, $pass)) {
            @ftp_close($conn);
            return false;
        }

        @ftp_pasv($conn, true);
        $remoteFile = $path . basename($filePath);
        $res = @ftp_put($conn, $remoteFile, $filePath, FTP_BINARY);
        @ftp_close($conn);

        return $res;
    }

    private function uploadViaSftp(string $filePath, array $config): bool
    {
        if (!function_exists('ssh2_connect')) {
            return false;
        }

        $host = $config['host'] ?? '';
        $port = (int)($config['port'] ?? 22);
        $user = $config['user'] ?? '';
        $pass = $config['password'] ?? '';
        $path = rtrim($config['remote_path'] ?? '', '/') . '/';

        $ssh = @ssh2_connect($host, $port);
        if (!$ssh) return false;

        if (!@ssh2_auth_password($ssh, $user, $pass)) return false;

        $sftp = @ssh2_sftp($ssh);
        if (!$sftp) return false;

        $remoteFile = $path . basename($filePath);
        $stream = @fopen("ssh2.sftp://{$sftp}{$remoteFile}", 'w');
        if (!$stream) return false;

        $data = @file_get_contents($filePath);
        $written = @fwrite($stream, $data);
        @fclose($stream);

        return $written !== false;
    }
}
