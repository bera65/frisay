<?php

define('IN_SCRIPT', true);
require_once dirname(__DIR__) . '/config/settings.php';

$file = basename((string) Tools::getValue('file'));
$isAdmin = !empty($_SESSION['id_admin']);
$idUser = Customer::isLoggedIn() ? Customer::getId() : 0;

if (!Contact::canAccessAttachment($file, $idUser, $isAdmin)) {
	http_response_code(403);
	exit;
}

Contact::serveAttachment($file);
