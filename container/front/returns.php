<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	$idUser = Customer::getId();
	$idReturn = (int) Tools::getValue('id');
	$flash = (string) ($_SESSION['return_flash'] ?? '');
	$flashType = (string) ($_SESSION['return_flash_type'] ?? 'info');
	unset($_SESSION['return_flash'], $_SESSION['return_flash_type']);

	if ($idReturn > 0) {
		$return = ReturnRequest::getByIdForUser($idReturn, $idUser);

		if (!$return) {
			http_response_code(404);
			$skipPageRender = true;
			$page->add('404', translate('Page Not Found'));
			return;
		}

		$pageTitle = translate('Return Request') . ' #' . $return['id_return'];
		$pageDesc = translate('Return request detail');

		$smarty->assign([
			'returnItem' => $return,
			'view' => 'detail',
			'flash' => $flash,
			'flashType' => $flashType,
			'returnDays' => ReturnRequest::getAllowedDays(),
			'breadcrumb' => [
				['name' => translate('Home Page'), 'url' => $domain],
				['name' => translate('My Returns'), 'url' => $domain . 'returns'],
				['name' => '#' . $return['id_return'], 'url' => ''],
			],
		]);
		return;
	}

	$pageTitle = translate('My Returns');
	$pageDesc = translate('My return requests');
	$returns = ReturnRequest::getUserList($idUser);
	$canCreate = ReturnRequest::isEnabled() && count(ReturnRequest::getEligibleOrders($idUser)) > 0;

	$smarty->assign([
		'returns' => $returns,
		'view' => 'list',
		'canCreate' => $canCreate,
		'returnDays' => ReturnRequest::getAllowedDays(),
		'flash' => $flash,
		'flashType' => $flashType,
		'breadcrumb' => [
			['name' => translate('Home Page'), 'url' => $domain],
			['name' => translate('My Returns'), 'url' => ''],
		],
	]);
