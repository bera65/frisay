<?php

namespace BackupPro\Service;

use BackupPro\Repository\LogRepository;
use DB;

class DatabaseDumpService
{
    public function dumpTableStructure(string $tableName, string $outputFile): void
    {
        $handle = fopen($outputFile, 'a');
        if (!$handle) {
            throw new \Exception("SQL çıktısı oluşturulamadı: {$outputFile}");
        }

        fwrite($handle, "\n-- --------------------------------------------------------\n");
        fwrite($handle, "-- Table structure for table `{$tableName}`\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

        $rows = DB::execute("SHOW CREATE TABLE `{$tableName}`");
        if ($rows && isset($rows[0])) {
            $createSql = $rows[0]['Create Table'] ?? (isset($rows[0][1]) ? $rows[0][1] : '');
            if ($createSql) {
                fwrite($handle, $createSql . ";\n\n");
            }
        }

        fclose($handle);
    }

    public function dumpTableDataChunk(string $tableName, int $offset, int $limit, string $outputFile): int
    {
        $handle = fopen($outputFile, 'a');
        if (!$handle) {
            throw new \Exception("SQL çıktısı oluşturulamadı: {$outputFile}");
        }

        $rows = DB::execute("SELECT * FROM `{$tableName}` LIMIT {$limit} OFFSET {$offset}") ?: [];
        $count = count($rows);

        if ($count > 0) {
            fwrite($handle, "LOCK TABLES `{$tableName}` WRITE;\n");
            fwrite($handle, "ALTER TABLE `{$tableName}` DISABLE KEYS;\n");

            $columns = array_keys($rows[0]);
            $quotedColumns = array_map(fn($col) => "`{$col}`", $columns);
            $colHeader = "INSERT INTO `{$tableName}` (" . implode(', ', $quotedColumns) . ") VALUES\n";

            $valueLines = [];
            foreach ($rows as $row) {
                $escapedValues = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $escapedValues[] = 'NULL';
                    } else {
                        $escapedValues[] = "'" . addslashes((string)$val) . "'";
                    }
                }
                $valueLines[] = "(" . implode(', ', $escapedValues) . ")";
            }

            fwrite($handle, $colHeader . implode(",\n", $valueLines) . ";\n");
            fwrite($handle, "ALTER TABLE `{$tableName}` ENABLE KEYS;\n");
            fwrite($handle, "UNLOCK TABLES;\n\n");
        }

        fclose($handle);
        return $count;
    }

    public function initSqlHeader(string $outputFile): void
    {
        $handle = fopen($outputFile, 'w');
        if ($handle) {
            fwrite($handle, "-- Backup & Restore Manager Pro - MySQL Dump\n");
            fwrite($handle, "-- Created: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Host: localhost\n");
            fwrite($handle, "-- Server version: " . (DB::getValue("SELECT VERSION()") ?: '8.0') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET AUTOCOMMIT = 0;\n");
            fwrite($handle, "START TRANSACTION;\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
            fclose($handle);
        }
    }

    public function finalizeSqlHeader(string $outputFile): void
    {
        $handle = fopen($outputFile, 'a');
        if ($handle) {
            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($handle, "COMMIT;\n");
            fclose($handle);
        }
    }
}
