<?php

namespace BackupPro\Service;

use BackupPro\Core\Settings;
use BackupPro\Repository\LogRepository;
use DB;

class ScannerService
{
    private string $rootPath;

    public function __construct()
    {
        $this->rootPath = rtrim(str_replace('\\', '/', dirname(__DIR__, 4)), '/');
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function scanFiles(?array $customFolders = null): array
    {
        $excludeFoldersRaw = Settings::get('exclude_folders', 'cache,logs,backup,vendor,node_modules,tmp,temp,.git');
        $excludeFolders = array_map('trim', explode(',', $excludeFoldersRaw));
        
        // Ensure backups and backup are excluded to prevent recursive self-backups
        if (!in_array('backups', $excludeFolders, true)) {
            $excludeFolders[] = 'backups';
        }
        if (!in_array('backup', $excludeFolders, true)) {
            $excludeFolders[] = 'backup';
        }

        $excludeFilesRaw = Settings::get('exclude_files', '');
        $excludeFiles = array_filter(array_map('trim', explode(',', $excludeFilesRaw)));

        $regexExclusions = Settings::get('regex_exclusions', '');

        $fileList = [];
        $totalBytes = 0;
        $unreadableFiles = [];

        $targetDirs = [];
        if ($customFolders !== null && count($customFolders) > 0) {
            foreach ($customFolders as $folder) {
                $p = $this->rootPath . '/' . ltrim(str_replace('\\', '/', $folder), '/');
                if (is_dir($p)) {
                    $targetDirs[] = $p;
                }
            }
        } else {
            $targetDirs[] = $this->rootPath;
        }

        foreach ($targetDirs as $dir) {
            $this->traverseDirectory($dir, $excludeFolders, $excludeFiles, $regexExclusions, $fileList, $totalBytes, $unreadableFiles);
        }

        return [
            'total_files' => count($fileList),
            'total_bytes' => $totalBytes,
            'unreadable_count' => count($unreadableFiles),
            'files' => $fileList,
            'unreadable_files' => $unreadableFiles
        ];
    }

    private function traverseDirectory(
        string $dir,
        array $excludeFolders,
        array $excludeFiles,
        string $regexExclusions,
        array &$fileList,
        float &$totalBytes,
        array &$unreadableFiles
    ): void {
        $normalizedDir = str_replace('\\', '/', $dir);
        $files = @scandir($normalizedDir);
        if ($files === false) {
            $unreadableFiles[] = $normalizedDir;
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = str_replace('\\', '/', $normalizedDir . '/' . $file);
            $relativePath = ltrim(str_replace($this->rootPath, '', $fullPath), '/');

            if (is_dir($fullPath)) {
                if (in_array($file, $excludeFolders, true)) {
                    continue;
                }
                $this->traverseDirectory($fullPath, $excludeFolders, $excludeFiles, $regexExclusions, $fileList, $totalBytes, $unreadableFiles);
            } else {
                if (in_array($file, $excludeFiles, true)) {
                    continue;
                }

                if ($regexExclusions !== '' && @preg_match($regexExclusions, $relativePath)) {
                    continue;
                }

                $size = @filesize($fullPath);
                if ($size === false || !is_readable($fullPath)) {
                    $unreadableFiles[] = $relativePath;
                    continue;
                }

                $fileList[] = [
                    'relative_path' => $relativePath,
                    'full_path' => $fullPath,
                    'size' => $size,
                    'mtime' => @filemtime($fullPath) ?: time()
                ];
                $totalBytes += $size;
            }
        }
    }

    public function scanDatabase(): array
    {
        $tables = DB::execute("SHOW TABLES") ?: [];
        $tableList = [];
        $totalRows = 0;
        $totalBytes = 0;

        foreach ($tables as $tRow) {
            $tableName = reset($tRow);
            $statusRow = DB::getRowSafe('information_schema.TABLES', 'TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$tableName]);

            $rows = (int) ($statusRow['TABLE_ROWS'] ?? 0);
            $dataLength = (float) ($statusRow['DATA_LENGTH'] ?? 0);
            $indexLength = (float) ($statusRow['INDEX_LENGTH'] ?? 0);
            $bytes = $dataLength + $indexLength;

            $tableList[] = [
                'name' => $tableName,
                'rows' => $rows,
                'bytes' => $bytes,
                'formatted_bytes' => \BackupPro\Core\Settings::formatBytes($bytes)
            ];

            $totalRows += $rows;
            $totalBytes += $bytes;
        }

        return [
            'total_tables' => count($tableList),
            'total_rows' => $totalRows,
            'total_bytes' => $totalBytes,
            'tables' => $tableList
        ];
    }
}
