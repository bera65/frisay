<?php
	if (!defined('IN_SCRIPT')) {
		exit;
	}

	header('Location: ' . $domain . 'my-account', true, 301);
	exit;
