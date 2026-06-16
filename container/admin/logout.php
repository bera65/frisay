<?php
	if (!defined('IN_ADMIN')) {
		exit;
	}

	Admin::logout();
	header('Location: ' . Admin::url('login'));
	exit;
