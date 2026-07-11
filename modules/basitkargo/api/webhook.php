<?php

	if (!defined('IN_SCRIPT')) {
		exit;
	}

	header('Content-Type: application/json; charset=utf-8');

	$provided = trim((string) Tools::getValue('token'));
	$stored = (string) Settings::get('BASITKARGO_LINK_TOKEN');

	if ($stored === '' || !hash_equals($stored, $provided)) {
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

	$id = (int) ($data['id'] ?? 0);
	$status = $data['status'] ?? '';
	$cargo = $data['handler']['name'] ?? '';
	$cargoCode = $data['handlerShipmentCode'] ?? '';
	$isHave = $id > 0
		? (int) DB::getValue('SELECT id_order FROM orders WHERE id_order = ? LIMIT 1', [$id])
		: 0;

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

		DB::execute(
			'UPDATE orders SET status = ?, cargo_company = ?, tracking_number = ? WHERE id_order = ?',
			[(int) $statusID, clearSQL($cargo), clearSQL($cargoCode), $id]
		);
		
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
