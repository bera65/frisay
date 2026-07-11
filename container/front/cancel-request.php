<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idUser = Customer::getId();
	$flash = (string) ($_SESSION['cancel_flash'] ?? '');
	$flashType = (string) ($_SESSION['cancel_flash_type'] ?? 'danger');
	unset($_SESSION['cancel_flash'], $_SESSION['cancel_flash_type']);

	$selectedOrderId = (int) Tools::getValue('id_order');
	if ($selectedOrderId <= 0) {
		$selectedOrderId = (int) Tools::getValue('order');
	}

	if (Tools::isSubmit('submitCancel')) {
		$postToken = (string) Tools::getValue('token');

		if (!hash_equals($token, $postToken)) {
			$flash = translate('Invalid request, please refresh and try again');
		} else {
			$idOrder = (int) Tools::getValue('id_order');
			$message = (string) Tools::getValue('message');
			$result = CancelRequest::create($idOrder, $idUser, $message);

			if ($result['success']) {
				$_SESSION['cancel_flash'] = $result['message'];
				$_SESSION['cancel_flash_type'] = 'success';
				header('Location: ' . $domain . 'my-account?order=' . $idOrder);
				exit;
			}

			$flash = $result['message'];
			$selectedOrderId = $idOrder;
		}
	}

	if ($selectedOrderId > 0 && !CancelRequest::isOrderEligible($selectedOrderId, $idUser)) {
		$orderCheck = Order::getByIdForUser($selectedOrderId, $idUser);
		if (!$orderCheck || !Tools::isSubmit('submitCancel')) {
			// keep for viewing existing cancel request on detail
		}
	}

	$pageTitle = translate('Cancel request');
	$pageDesc = translate('Request order cancellation');

	$smarty->assign([
		'selectedOrderId' => $selectedOrderId,
		'eligible' => $selectedOrderId > 0 && CancelRequest::isOrderEligible($selectedOrderId, $idUser),
		'flash' => $flash,
		'flashType' => $flashType,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Account'), 'url' => $domain . 'my-account'],
			['name' => translate('Cancel request'), 'url' => ''],
		],
	]);
