<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	header('Content-Type: application/json; charset=utf-8');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		http_response_code(405);
		echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
		exit;
	}

	$postToken = (string) Tools::getValue('token');

	if (!hash_equals($adminToken, $postToken)) {
		http_response_code(403);
		echo json_encode(['success' => false, 'message' => adminT('Invalid request')], JSON_UNESCAPED_UNICODE);
		exit;
	}

	$idOrder = (int) Tools::getValue('id_order');
	$status = (int) Tools::getValue('status');
	$result = Order::updateFromApi($idOrder, ['status' => $status]);

	if (!empty($result['success'])) {
		$result['status'] = $status;
		$result['status_label'] = Order::getStatusLabel($status);
		$result['status_class'] = Order::getStatusBadgeClass($status);
	}

	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	exit;
