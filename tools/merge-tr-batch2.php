<?php
/**
 * Merge Turkish translations from batch2 patch map into lang/admin/tr.php
 */
$root = dirname(__DIR__);
$content = file_get_contents($root . '/tools/patch-admin-i18n-batch2.php');
if (!preg_match('/\$map\s*=\s*\[(.*?)\];/s', $content, $m)) {
	fwrite(STDERR, "Could not parse batch2 map\n");
	exit(1);
}

$block = $m[1];
preg_match_all("/\t'((?:\\\\'|[^'])+)'\s*=>\s*\"\{'((?:\\\\'|[^'])+)'\|adminT\}\"/", $block, $pairs, PREG_SET_ORDER);

$fromBatch = [];
foreach ($pairs as $p) {
	$tr = str_replace("\\'", "'", $p[1]);
	$en = str_replace("\\'", "'", $p[2]);
	$fromBatch[$en] = $tr;
}

$enPath = $root . '/lang/admin/en.php';
$trPath = $root . '/lang/admin/tr.php';
$en = require $enPath;
$tr = require $trPath;

foreach ($en as $key => $val) {
	if (isset($fromBatch[$key])) {
		$tr[$key] = $fromBatch[$key];
	} elseif (!isset($tr[$key])) {
		$tr[$key] = $key;
	}
}

ksort($tr);
$lines = ["<?php", "\treturn ["];
foreach ($tr as $k => $v) {
	$lines[] = "\t\t" . var_export($k, true) . ' => ' . var_export($v, true) . ',';
}
$lines[] = "\t];";
file_put_contents($trPath, implode("\n", $lines) . "\n");

echo 'Updated tr.php with ' . count($tr) . ' keys (' . count($fromBatch) . " from batch2)\n";
