<div class="admin-panel ai-dash-panel mb-4" id="aiDashboardPanel">
	<div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
		<div>
			<h3 class="h6 mb-1">Yapay Zeka Analizi</h3>
			<p class="small text-muted mb-0">Satışlar, çok satan ürünler ve aksiyon önerileri — sonuçlar pencerede açılır.</p>
		</div>
		{if $configured}
		<button type="button" class="btn btn-sm btn-dark" id="aiAnalyzeDashboardBtn">Sayfayı analiz et</button>
		{else}
		<a href="{$settingsUrl|escape}" class="btn btn-sm btn-outline-warning">API anahtarı ekle</a>
		{/if}
	</div>
	<p class="small mb-0" id="aiDashStatus"></p>
</div>

<script>
window.AiAssistantDashboard = {
	apiUrl: {$apiUrl|@json_encode nofilter},
	token: {$adminToken|@json_encode nofilter},
	configured: {if $configured}true{else}false{/if},
	settingsUrl: {$settingsUrl|@json_encode nofilter}
};
</script>
<script src="{$domain}modules/ai-assistant/assets/js/modal.js?v={$smarty.now}"></script>
<script src="{$domain}modules/ai-assistant/assets/js/dashboard.js?v={$smarty.now}"></script>
<link rel="stylesheet" href="{$domain}modules/ai-assistant/assets/css/admin.css?v={$smarty.now}">
