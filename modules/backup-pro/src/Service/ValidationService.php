<?php

namespace BackupPro\Service;

use ZipArchive;

class ValidationService
{
    public function calculateSha256(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }
        return hash_file('sha256', $filePath) ?: null;
    }

    public function calculateCrc32(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }
        return hash_file('crc32b', $filePath) ?: null;
    }

    public function verifyZipIntegrity(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            return ['valid' => false, 'error' => 'Zip dosyası bulunamadı.'];
        }

        $zip = new ZipArchive();
        $res = $zip->open($zipPath, ZipArchive::CHECKCONS);

        if ($res !== true) {
            return ['valid' => false, 'error' => "Zip arşivi bozuk veya okunamıyor (Hata kodu: {$res})."];
        }

        $numFiles = $zip->numFiles;
        $zip->close();

        return [
            'valid' => true,
            'num_files' => $numFiles,
            'size' => filesize($zipPath)
        ];
    }
}
