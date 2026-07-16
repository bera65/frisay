<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	if (Tools::isSubmit('markRead')) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			AdminNotification::markRead((int) Tools::getValue('id'));
		}

		header('Location: ' . Admin::url('notifications'));
		exit;
	}

	if (Tools::isSubmit('markAllRead')) {
		$postToken = (string) Tools::getValue('token');

		if (hash_equals($adminToken, $postToken)) {
			AdminNotification::markAllRead();
		}

		header('Location: ' . Admin::url('notifications'));
		exit;
	}

	$notifications = AdminNotification::getList(100);

	$smarty->assign([
		'notifications' => $notifications,
		'notificationsTotal' => count($notifications),
		'unreadCount' => AdminNotification::countUnread(),
	]);

	AdminPage::add('notifications', 'Notifications');
