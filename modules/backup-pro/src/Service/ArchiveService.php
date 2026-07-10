<?php

namespace BackupPro\Service;

use ZipArchive;
use BackupPro\Core\Settings;
use BackupPro\Repository\LogRepository;

class ArchiveService
{
    public function addFilesToZipChunk(string $zipPath, array $fileList, string $compressionLevel = 'balanced'): int
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception("Sunucuda PHP 'zip' eklentisi (ZipArchive) aktif değil. Lütfen php.ini dosyasından extension=zip eklentisini etkinleştirin.");
        }

        $zip = new ZipArchive();
        $flags = file_exists($zipPath) ? ZipArchive::CHECKCONS : ZipArchive::CREATE;

        if ($zip->open($zipPath, $flags) !== true) {
            throw new \Exception("Zip arşivi açılamadı: {$zipPath}");
        }

        // Sıkıştırma seviyesini ayarla (PHP 7.4.3+ ve libzip 1.1.2+ desteği ile 3. parametre compression level'dır)
        $compMethod = ZipArchive::CM_DEFLATE;
        $compLevel  = 6; // Default / Dengeli
        
        if ($compressionLevel === 'fast') {
            $compLevel = 1; // En hızlı, en az sıkıştırma
        } elseif ($compressionLevel === 'balanced') {
            $compLevel = 6; // Dengeli sıkıştırma / varsayılan
        } elseif ($compressionLevel === 'maximum') {
            $compLevel = 9; // En yüksek sıkıştırma, en yavaş
        } elseif ($compressionLevel === 'store') {
            $compMethod = ZipArchive::CM_STORE;
            $compLevel  = 0;
        }

        $addedCount = 0;
        foreach ($fileList as $item) {
            $fullPath = $item['full_path'];
            $relPath = $item['relative_path'];

            if (file_exists($fullPath) && is_readable($fullPath)) {
                if ($zip->addFile($fullPath, $relPath)) {
                    if ($compMethod !== ZipArchive::CM_STORE) {
                        $zip->setCompressionName($relPath, $compMethod, $compLevel);
                    }
                    $addedCount++;
                }
            }
        }

        $zip->close();
        return $addedCount;
    }

    public function extractZipChunk(string $zipPath, string $extractDir, array $selectedFiles = []): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        if (empty($selectedFiles)) {
            $res = $zip->extractTo($extractDir);
        } else {
            $res = $zip->extractTo($extractDir, $selectedFiles);
        }

        $zip->close();
        return $res;
    }
}
