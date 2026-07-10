<?php

if (!defined('IN_SCRIPT')) {
	exit;
}

header('Content-Type: application/json; charset=utf-8');

$pos = new PosModule();
$pos->requireApiAuth();
$pos->requireApiToken();

$query = trim((string) Tools::getValue('q'));
$customers = $pos->searchCustomers($query);

echo json_encode([
	'success' => true,
	'customers' => $customers,
]);
