<?php
	if (!defined('IN_SCRIPT')) {
		header('HTTP/1.0 404 Not Found');
		header('Location: ../404');
		exit;
	}

	$pageTitle = translate('My Account');
	$pageDesc = translate('Manage your account');
	$css = 'pages.css';
	$js = 'account.js';
	$customer = Customer::getCurrent();
	$idUser = (int) $customer['id_user'];
	$addresses = Address::getListForUser($idUser);
	$notifications = Notification::getListForUser($idUser);
	$orders = Order::getUserOrders($idUser);
	$fullName = trim((string) ($customer['user_full_name'] ?? ''));
	$accountInitial = $fullName !== ''
		? mb_strtoupper(mb_substr($fullName, 0, 1, 'UTF-8'))
		: '?';

	$smarty->assign([
		'customer' => $customer,
		'addresses' => $addresses,
		'notifications' => $notifications,
		'unreadNotificationCount' => Notification::getUnreadCount($idUser),
		'accountInitial' => $accountInitial,
		'accountStats' => [
			'orders' => count($orders),
			'support' => Notification::getUnreadCount($idUser),
			'addresses' => count($addresses),
			'coupons' => 0,
		],
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Account'), 'url' => ''],
		],
	]);
