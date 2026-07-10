CREATE TABLE IF NOT EXISTS `backup_pro_backups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(32) NOT NULL DEFAULT 'full',
  `archive_format` VARCHAR(16) NOT NULL DEFAULT 'zip',
  `file_path` VARCHAR(512) NOT NULL,
  `file_size` BIGINT NOT NULL DEFAULT 0,
  `checksum_sha256` VARCHAR(64) DEFAULT NULL,
  `checksum_crc32` VARCHAR(16) DEFAULT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `encrypted` TINYINT NOT NULL DEFAULT 0,
  `storage_destinations` TEXT DEFAULT NULL,
  `duration_seconds` INT NOT NULL DEFAULT 0,
  `total_files` INT NOT NULL DEFAULT 0,
  `total_tables` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_pro_queue` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_id` INT NOT NULL,
  `job_type` VARCHAR(32) NOT NULL,
  `current_step` INT NOT NULL DEFAULT 0,
  `total_steps` INT NOT NULL DEFAULT 0,
  `progress_percentage` FLOAT NOT NULL DEFAULT 0,
  `locked` TINYINT NOT NULL DEFAULT 0,
  `payload` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_locked` (`locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_pro_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `backup_id` INT DEFAULT NULL,
  `level` VARCHAR(16) NOT NULL DEFAULT 'info',
  `action` VARCHAR(64) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  KEY `idx_backup_id` (`backup_id`),
  KEY `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `backup_pro_settings`;
CREATE TABLE `backup_pro_settings` (
  `setting_key` VARCHAR(64) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_pro_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL,
  `backup_type` VARCHAR(32) NOT NULL DEFAULT 'full',
  `cron_expression` VARCHAR(64) NOT NULL DEFAULT '0 2 * * *',
  `keep_count` INT NOT NULL DEFAULT 7,
  `storage_destinations` TEXT DEFAULT NULL,
  `active` TINYINT NOT NULL DEFAULT 1,
  `last_run` DATETIME DEFAULT NULL,
  `next_run` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_pro_destinations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL,
  `driver` VARCHAR(32) NOT NULL DEFAULT 'local',
  `config` TEXT NOT NULL,
  `active` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `backup_pro_settings` (`setting_key`, `setting_value`) VALUES
('compression_level', 'balanced'),
('archive_format', 'zip'),
('chunk_size_files', '1000'),
('chunk_size_db_rows', '5000'),
('max_backup_size_mb', '102400'),
('exclude_folders', 'cache,logs,backup,vendor,node_modules,tmp,temp,.git'),
('exclude_files', ''),
('regex_exclusions', ''),
('auto_prune_keep_count', '10'),
('email_notifications', 'no'),
('notification_email', ''),
('encryption_enabled', 'no'),
('encryption_password', '');
