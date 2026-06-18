<?php
define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';
require_once dirname(__DIR__) . '/core/Address.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

if (!Customer::isLoggedIn()) {
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız']);
	exit;
}

$token = Tools::getValue('token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $token)) {
	http_response_code(403);
	echo json_encode(['success' => false, 'message' => 'Geçersiz istek']);
	exit;
}

$idUser = Customer::getId();
$action = Tools::getValue('action');

switch ($action) {
	case 'update_profile':
		$result = Customer::updateProfile(
			(string) Tools::getValue('full_name'),
			(string) Tools::getValue('phone'),
			(string) Tools::getValue('email')
		);
		if ($result['success']) {
			$result['user'] = Customer::publicUser(Customer::getCurrent());
		}
		echo json_encode($result);
		break;

	case 'update_password':
		echo json_encode(Customer::updatePassword(
			(string) Tools::getValue('current_password'),
			(string) Tools::getValue('new_password')
		));
		break;

	case 'save_address':
		echo json_encode(Address::save($idUser, [
			'label' => Tools::getValue('label'),
			'full_name' => Tools::getValue('full_name'),
			'phone' => Tools::getValue('phone'),
			'company_name' => Tools::getValue('company_name'),
			'tax_office' => Tools::getValue('tax_office'),
			'tax_number' => Tools::getValue('tax_number'),
			'city' => Tools::getValue('city'),
			'district' => Tools::getValue('district'),
			'address_text' => Tools::getValue('address_text'),
			'is_default' => Tools::getValue('is_default'),
		], (int) Tools::getValue('id_address')));
		break;

	case 'delete_address':
		echo json_encode(Address::delete((int) Tools::getValue('id_address'), $idUser));
		break;

	case 'set_default_address':
		echo json_encode(Address::setDefault((int) Tools::getValue('id_address'), $idUser));
		break;

	case 'get_notifications':
		echo json_encode([
			'success' => true,
			'notifications' => Notification::getListForUser($idUser),
			'unread_count' => Notification::getUnreadCount($idUser),
		]);
		break;

	case 'mark_notification_read':
		$ok = Notification::markRead((int) Tools::getValue('id_notification'), $idUser);
		echo json_encode([
			'success' => $ok,
			'message' => $ok ? 'Bildirim okundu' : 'Bildirim bulunamadı',
			'unread_count' => Notification::getUnreadCount($idUser),
		]);
		break;

	case 'mark_all_notifications_read':
		Notification::markAllRead($idUser);
		echo json_encode([
			'success' => true,
			'message' => 'Tüm bildirimler okundu',
			'unread_count' => 0,
		]);
		break;

	default:
		http_response_code(400);
		echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
}
