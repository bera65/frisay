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
	$allOrders = Order::enrichUserOrderRows(Order::getUserOrders($idUser));

	$orderFilter = (string) Tools::getValue('filter');
	if ($orderFilter === '') {
		$orderFilter = 'all';
	}

	$selectedOrderId = (int) Tools::getValue('order');
	$activeTab = (string) Tools::getValue('tab');
	$orderContactSuccess = Tools::getValue('contact_sent') === '1'
		? translate('Your message has been received. We will get back to you soon.')
		: '';
	$orderContactError = '';

	if (Tools::isSubmit('sendOrderContact')) {
		$postOrderId = (int) Tools::getValue('id_order');
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$orderContactError = translate('Invalid request, please refresh and try again');
			$selectedOrderId = $postOrderId;
		} else {
			$result = Contact::submitOrder($postOrderId, $idUser, (string) Tools::getValue('message'), [
				'website' => (string) Tools::getValue('website'),
			]);

			if ($result['success']) {
				header('Location: ' . $domain . 'my-account?order=' . $postOrderId . '&contact_sent=1#order-contact');
				exit;
			}

			$orderContactError = $result['message'];
			$selectedOrderId = $postOrderId;
		}
	}

	if ($activeTab === '' && $selectedOrderId <= 0) {
		$activeTab = 'orders';
	}
	if ($selectedOrderId > 0) {
		$activeTab = 'orders';
	}

	$orders = [];
	foreach ($allOrders as $orderRow) {
		if ($orderFilter === 'ongoing' && empty($orderRow['is_ongoing'])) {
			continue;
		}
		if ($orderFilter === 'cancelled' && empty($orderRow['is_cancelled'])) {
			continue;
		}
		if ($orderFilter === 'returns' && empty($orderRow['is_returned'])) {
			continue;
		}
		$orders[] = $orderRow;
	}

	$selectedOrder = null;
	$orderContactThread = [];
	if ($selectedOrderId > 0) {
		$selectedOrder = Order::getByIdForUser($selectedOrderId, $idUser);
		if ($selectedOrder) {
			$selectedOrder['can_return'] = ReturnRequest::isOrderEligible($selectedOrderId, $idUser);
			$selectedOrder['can_cancel'] = CancelRequest::isOrderEligible($selectedOrderId, $idUser);
			$selectedOrder['return_request'] = ReturnRequest::getForOrder($selectedOrderId, $idUser);
			$selectedOrder['cancel_request'] = CancelRequest::getForOrder($selectedOrderId, $idUser);
			$selectedOrder['is_delivered'] = (int) $selectedOrder['status'] === Order::STATUS_DELIVERED;
			$orderContactThread = Contact::getOrderThread($selectedOrderId, $idUser);
			Module::refreshHook($smarty, 'order_confirmation', ['order' => $selectedOrder]);
		}
	}

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
		'orders' => $orders,
		'allOrdersCount' => count($allOrders),
		'orderFilter' => $orderFilter,
		'selectedOrder' => $selectedOrder,
		'selectedOrderId' => $selectedOrderId,
		'orderContactThread' => $orderContactThread,
		'orderContactSuccess' => $orderContactSuccess,
		'orderContactError' => $orderContactError,
		'activeTab' => $activeTab,
		'accountStats' => [
			'orders' => count($allOrders),
			'support' => Notification::getUnreadCount($idUser),
			'addresses' => count($addresses),
			'coupons' => 0,
		],
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Account'), 'url' => ''],
		],
	]);
