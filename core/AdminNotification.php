<?php

class AdminNotification
{
	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'admin_notifications'");

		if (empty($table)) {
			DB::execute(
				"CREATE TABLE `admin_notifications` (
					`id_notification` int(11) NOT NULL AUTO_INCREMENT,
					`type` varchar(32) NOT NULL DEFAULT 'info',
					`title` varchar(255) NOT NULL DEFAULT '',
					`message` text NOT NULL,
					`link` varchar(255) NOT NULL DEFAULT '',
					`is_read` tinyint(1) NOT NULL DEFAULT 0,
					`date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`id_notification`),
					KEY `is_read` (`is_read`),
					KEY `date_add` (`date_add`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	public static function add(string $title, string $message, string $link = '', string $type = 'info'): ?int
	{
		self::ensureSchema();

		$title = trim($title);
		$message = trim($message);

		if ($title === '') {
			return null;
		}

		$id = DB::insert('admin_notifications', [
			'type' => mb_substr($type, 0, 32),
			'title' => mb_substr($title, 0, 255),
			'message' => $message,
			'link' => mb_substr($link, 0, 255),
			'is_read' => 0,
		]);

		return $id ? (int) $id : null;
	}

	public static function countUnread(): int
	{
		self::ensureSchema();

		return (int) DB::getValue(
			'SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0'
		);
	}

	public static function getList(int $limit = 50, bool $unreadOnly = false): array
	{
		self::ensureSchema();

		$where = $unreadOnly ? 'WHERE is_read = 0' : '';
		$rows = DB::execute(
			'SELECT * FROM admin_notifications ' . $where . ' ORDER BY id_notification DESC LIMIT ' . (int) $limit
		) ?: [];

		foreach ($rows as &$row) {
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
			$row['is_read'] = (int) $row['is_read'];
		}
		unset($row);

		return $rows;
	}

	public static function markRead(int $idNotification): bool
	{
		self::ensureSchema();

		$updated = DB::update(
			'admin_notifications',
			['is_read' => 1],
			'id_notification = :id_notification',
			['id_notification' => $idNotification]
		);

		return $updated !== false && $updated > 0;
	}

	public static function markAllRead(): void
	{
		self::ensureSchema();

		DB::update(
			'admin_notifications',
			['is_read' => 1],
			'is_read = 0',
			[]
		);
	}
}
