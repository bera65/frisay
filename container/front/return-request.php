<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idUser = Customer::getId();
	$flash = '';
	$flashType = 'danger';
	$selectedOrderId = (int) Tools::getValue('id_order');
	$eligibleOrders = ReturnRequest::getEligibleOrders($idUser);

	if (!ReturnRequest::isEnabled()) {
		$_SESSION['return_flash'] = translate('Return requests are disabled');
		$_SESSION['return_flash_type'] = 'warning';
		header('Location: ' . $domain . 'returns');
		exit;
	}

	if (Tools::isSubmit('submitReturn')) {
		$idOrder = (int) Tools::getValue('id_order');
		$message = (string) Tools::getValue('message');
		$result = ReturnRequest::create($idOrder, $idUser, $message, $_FILES['images'] ?? []);

		if ($result['success']) {
			$_SESSION['return_flash'] = $result['message'];
			$_SESSION['return_flash_type'] = 'success';
			header('Location: ' . $domain . 'returns?id=' . (int) $result['id_return']);
			exit;
		}

		$flash = $result['message'];
		$selectedOrderId = $idOrder;
	}

	if ($selectedOrderId > 0 && !ReturnRequest::isOrderEligible($selectedOrderId, $idUser)) {
		$selectedOrderId = 0;
	}

	$pageTitle = translate('New Return Request');
	$pageDesc = translate('Create a return request for your order');

	$smarty->assign([
		'eligibleOrders' => $eligibleOrders,
		'selectedOrderId' => $selectedOrderId,
		'returnDays' => ReturnRequest::getAllowedDays(),
		'maxImages' => ReturnRequest::MAX_IMAGES,
		'flash' => $flash,
		'flashType' => $flashType,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Returns'), 'url' => $domain . 'returns'],
			['name' => translate('New Return Request'), 'url' => ''],
		],
	]);
