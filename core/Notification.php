<?php

class Notification
{
	public static function create(int $idUser, string $type, string $title, string $message, string $link = ''): ?int
	{
		if ($idUser <= 0) {
			return null;
		}

		$id = DB::insert('user_notifications', [
			'id_user' => $idUser,
			'type' => $type,
			'title' => $title,
			'message' => $message,
			'link' => $link,
			'is_read' => 0,
		]);

		return $id ? (int) $id : null;
	}

	public static function notifyUser(int $idUser, string $type, string $title, string $message, string $link = ''): void
	{
		self::create($idUser, $type, $title, $message, $link);

		$user = DB::getRowSafe('users', 'id_user = ?', [$idUser]);
		$email = trim((string) ($user['email'] ?? ''));

		if ($email !== '') {
			global $domain;
			$body = '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>';

			if ($link !== '') {
				$body .= '<p><a href="' . htmlspecialchars($domain . ltrim($link, '/'), ENT_QUOTES, 'UTF-8') . '">Detayları görüntüle</a></p>';
			}

			Mail::send($email, $title, $body);
		}
	}

	public static function welcome(int $idUser, string $fullName): void
	{
		$title = 'Hoş geldiniz!';
		$message = 'Merhaba ' . $fullName . ",\n\nFShop'a kayıt olduğunuz için teşekkür ederiz. Hesabınızdan siparişlerinizi takip edebilirsiniz.";

		self::notifyUser($idUser, 'welcome', $title, $message, 'my-account');
	}

	public static function orderPlaced(int $idUser, string $reference, float $total): void
	{
		$title = 'Siparişiniz alındı';
		$message = 'Siparişiniz başarıyla oluşturuldu.' . "\n\n"
			. 'Sipariş No: ' . $reference . "\n"
			. 'Toplam: ' . Tools::displayPrice($total);

		self::notifyUser($idUser, 'order_placed', $title, $message, 'orders');
	}

	public static function orderStatusChanged(array $order, int $oldStatus, int $newStatus): void
	{
		$idUser = (int) ($order['id_user'] ?? 0);
		$reference = (string) ($order['reference'] ?? '');
		$payment = (string) ($order['payment_method'] ?? '');

		if ($idUser <= 0 || $oldStatus === $newStatus) {
			return;
		}

		$title = 'Sipariş durumu güncellendi';
		$message = self::buildStatusMessage($reference, $oldStatus, $newStatus, $payment);

		self::notifyUser($idUser, 'order_status', $title, $message, 'orders');
	}

	private static function buildStatusMessage(string $reference, int $oldStatus, int $newStatus, string $payment): string
	{
		$refLine = 'Sipariş No: ' . $reference . "\n\n";

		if ($newStatus === Order::STATUS_PROCESSING && $oldStatus === Order::STATUS_PENDING) {
			if ($payment === 'bank_transfer') {
				return $refLine . 'Havale ödemeniz onaylandı. Siparişiniz hazırlanmaya başlandı.';
			}

			return $refLine . 'Siparişiniz onaylandı ve hazırlanmaya başlandı.';
		}

		if ($newStatus === Order::STATUS_SHIPPED) {
			return $refLine . 'Siparişiniz kargoya verildi.';
		}

		if ($newStatus === Order::STATUS_DELIVERED) {
			return $refLine . 'Siparişiniz teslim edildi. Bizi tercih ettiğiniz için teşekkürler!';
		}

		if ($newStatus === Order::STATUS_CANCELLED) {
			return $refLine . 'Siparişiniz iptal edildi.';
		}

		return $refLine . 'Yeni durum: ' . Order::getStatusLabel($newStatus);
	}

	public static function getUnreadCount(int $idUser): int
	{
		return (int) DB::getValue(
			'SELECT COUNT(*) FROM user_notifications WHERE id_user = ? AND is_read = 0',
			[$idUser]
		);
	}

	public static function getListForUser(int $idUser, int $limit = 50): array
	{
		$rows = DB::execute(
			'SELECT * FROM user_notifications WHERE id_user = ? ORDER BY date_add DESC LIMIT ' . (int) $limit,
			[$idUser]
		) ?: [];

		foreach ($rows as &$row) {
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
			$row['is_read'] = (int) $row['is_read'];
		}
		unset($row);

		return $rows;
	}

	public static function markRead(int $idNotification, int $idUser): bool
	{
		$updated = DB::update(
			'user_notifications',
			['is_read' => 1],
			'id_notification = :id_notification AND id_user = :id_user',
			['id_notification' => $idNotification, 'id_user' => $idUser]
		);

		return $updated !== false && $updated > 0;
	}

	public static function markAllRead(int $idUser): void
	{
		DB::update(
			'user_notifications',
			['is_read' => 1],
			'id_user = :id_user AND is_read = 0',
			['id_user' => $idUser]
		);
	}
}
