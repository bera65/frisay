<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	$file = basename((string) Tools::getValue('file'));

	if (!Admin::isLoggedIn() || !ReturnRequest::canAccessProtectedFile($file, 0, true)) {
		http_response_code(403);
		exit;
	}

	ReturnRequest::serveProtectedFile($file);
