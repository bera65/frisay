<?php

	if (!defined('IN_SCRIPT')) {
		exit;
	}

	header('Content-Type: application/json; charset=utf-8');

	$token = md5(Tools::getValue('token'));

	if ($token != md5(Settings::get('BASITKARGO_LINK_TOKEN'))) {
		http_response_code(401);
		echo json_encode([
			'success' => false,
			'message' => 'Token gerekli'
		]);
		exit;
	}

	$rawBody = file_get_contents('php://input');
	$data = json_decode($rawBody, true);

	if (!is_array($data)) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Geçersiz JSON'
		]);
		exit;
	}

	$id 		= $data['id'] ?? 0;
	$status 	= $data['status'] ?? '';
	$cargo 		= $data['handler']['name'] ?? '';
	$cargoCode	= $data['handlerShipmentCode'] ?? '';
	$isHave		= DB::getRow('orders', 'id_order = '.(int)$id.'', 'id_order');
	if ($isHave)
	{
		if ($status == 'SHIPPED')
			$statusID = 3;
		else if ($status == 'DELIVERED')
			$statusID = 4;
		else if ($status == 'READY_TO_SHIP')
			$statusID = 2;
		else if ($status == 'RETURNING' OR $status == 'RETURNED' OR $status == 'LOST' OR $status == 'NEEDS_SUPPORT')
			$statusID = 3;
		DB::update('orders', array(
			'status' 			=> (int)$statusID,
			'cargo_company' 	=> clearSQL($cargo),
			'tracking_number' 	=> clearSQL($cargoCode),
		), 'id_order = '.(int)$id.'');
		
		echo json_encode([
			'success' => true,
			'message' => 'Sipariş var',
		]);
	}
	else
	{
		echo json_encode([
			'success' => false,
			'message' => 'Sipariş bulunamadı',
		]);
	}
