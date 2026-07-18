<div class="ai-assist-panel border rounded p-3"{if !$configured} data-locked="1"{/if}>
	<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
		<strong class="small">Yapay Zeka</strong>
		{if $configured}
		<span class="badge text-bg-success">Hazır</span>
		{else}
		<a href="{$settingsUrl|escape}" class="badge text-bg-warning text-decoration-none">API ayarla</a>
		{/if}
	</div>
	<p class="small text-muted mb-2">Başlık, kısa/uzun açıklama ve SEO alanlarını AI ile öner. Sonuçlar büyük pencerede açılır.</p>
	{if $is_new}
	<p class="small text-warning mb-2">Önce ürünü kaydedin; ardından AI paneli aktif olur. (Form alanları doluysa yeni üründe de kullanılabilir.)</p>
	{/if}
	<button type="button" class="btn btn-sm btn-dark w-100" id="aiImproveProductBtn"{if !$configured} disabled{/if}>Alanları iyileştir</button>
	<p class="small mb-0 mt-2" id="aiProductStatus"></p>
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
<script src="{$domain}modules/ai-assistant/assets/js/modal.js?v={$smarty.now}"></script>
<script src="{$domain}modules/ai-assistant/assets/js/product.js?v={$smarty.now}"></script>
<link rel="stylesheet" href="{$domain}modules/ai-assistant/assets/css/admin.css?v={$smarty.now}">
