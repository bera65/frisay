<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>{$siteName|escape} — Bakım</title>
	<style>
		* { box-sizing: border-box; }
		body {
			margin: 0;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
			font-family: Arial, Helvetica, sans-serif;
			background: #f4f4f5;
			color: #222;
		}
		.maintenance-card {
			width: 100%;
			max-width: 560px;
			background: #fff;
			border: 1px solid #e8e8e8;
			border-radius: 12px;
			padding: 40px 32px;
			text-align: center;
			box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
		}
		.maintenance-logo {
			max-width: 160px;
			height: auto;
			margin: 0 auto 24px;
			display: block;
		}
		.maintenance-card h1 {
			margin: 0 0 16px;
			font-size: 1.5rem;
		}
		.maintenance-content {
			font-size: 1rem;
			line-height: 1.65;
			color: #444;
			text-align: left;
		}
		.maintenance-content p:last-child { margin-bottom: 0; }
	</style>
</head>
<body>
	<div class="maintenance-card">
		<img src="{$logoUrl|escape}" alt="{$siteName|escape}" class="maintenance-logo">
		<h1>{$siteName|escape}</h1>
		<div class="maintenance-content">{$maintenanceMessage nofilter}</div>
	</div>
</body>
</html>
