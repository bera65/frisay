<?php
	if (!defined('IN_SCRIPT')) {
		header('HTTP/1.0 404 Not Found');
		header('Location: ../404');
		exit;
	}

	$pageTitle = 'Hesabım';
	$pageDesc = 'Hesap bilgilerinizi yönetin';
	$js = 'account.js';
	$customer = Customer::getCurrent();
	$idUser = (int) $customer['id_user'];
	$addresses = Address::getListForUser($idUser);
	$notifications = Notification::getListForUser($idUser);

	$smarty->assign([
		'customer' => $customer,
		'addresses' => $addresses,
		'notifications' => $notifications,
		'unreadNotificationCount' => Notification::getUnreadCount($idUser),
		'breadcrumb' => [
			['name' => 'Anasayfa', 'url' => $domain],
			['name' => 'Hesabım', 'url' => ''],
		],
	]);
