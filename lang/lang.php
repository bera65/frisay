<?php

global $selectLang;

$file = __DIR__ . '/' . $selectLang . '.php';

$loaded = file_exists($file) ? require $file : [];
$lang = is_array($loaded) ? $loaded : [];