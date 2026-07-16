<div class="ai-assist-panel border rounded p-3"{if !$configured} data-locked="1"{/if}>
	<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
		<strong class="small">Yapay Zeka</strong>
		{if $configured}
		<span class="badge text-bg-success">Hazır</span>
		{else}
		<a href="{$settingsUrl|escape}" class="badge text-bg-warning text-decoration-none">API ayarla</a>
		{/if}
	</div>
	<p class="small text-muted mb-2">Başlık, kısa/uzun açıklama ve SEO alanlarını AI ile öner.</p>
	{if $is_new}
	<p class="small text-warning mb-0">Önce ürünü kaydedin; ardından AI paneli aktif olur. (Form alanları doluysa yeni üründe de kullanılabilir.)</p>
	{/if}
	<div class="d-flex flex-wrap gap-2 mb-2">
		<button type="button" class="btn btn-sm btn-dark" id="aiImproveProductBtn"{if !$configured} disabled{/if}>Alanları iyileştir</button>
		<button type="button" class="btn btn-sm btn-outline-secondary" id="aiApplySuggestionsBtn" disabled>Önerileri forma yaz</button>
	</div>
	<p class="small mb-0" id="aiProductStatus"></p>
	<div id="aiProductPreview" class="ai-assist-preview small mt-2" style="display:none;"></div>
</div>

<script>
window.AiAssistantProduct = {
	apiUrl: {$apiUrl|@json_encode nofilter},
	token: {$adminToken|@json_encode nofilter},
	tone: {$tone|@json_encode nofilter},
	configured: {if $configured}true{else}false{/if},
	settingsUrl: {$settingsUrl|@json_encode nofilter}
};
</script>
<script src="{$domain}modules/ai-assistant/assets/js/product.js?v={$smarty.now}"></script>
<link rel="stylesheet" href="{$domain}modules/ai-assistant/assets/css/admin.css?v={$smarty.now}">
