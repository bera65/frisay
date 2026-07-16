			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="admin-confirm-modal" tabindex="-1" aria-hidden="true" aria-labelledby="admin-confirm-title">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header py-2 bg-light">
				<h5 class="modal-title h6 mb-0 text-dark" id="admin-confirm-title">{'Confirm action'|adminT}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
			</div>
			<div class="modal-body py-3" id="admin-confirm-message">
				{'Are you sure you want to perform this action?'|adminT}
			</div>
			<div class="modal-footer py-2">
				<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
				<button type="button" class="btn btn-danger btn-sm" id="admin-confirm-btn">{'Yes, confirm'|adminT}</button>
			</div>
		</div>
	</div>
</div>

<p class="admin-footer-meta text-center">
	<a href="https://frisay.com/" target="_blank" rel="noopener">FriSay E-Commerce</a>
	<span class="text-muted"> · {$fshopName|escape} v{$fshopVersion|escape}</span>
</p>
<script src="{$domain}templates/admin/js/popper.min.js"></script>
<script src="{$domain}templates/admin/js/bootstrap.min.js"></script>
<script src="{$domain}templates/admin/js/admin.js?v={$smarty.now}"></script>
<script>
window.__adminI18n = {$adminI18n|@json_encode nofilter};
</script>
{if $moduleAdminAssets.js|@count}
{foreach $moduleAdminAssets.js as $moduleJs}
<script src="{$moduleJs}?v={$smarty.now}"></script>
{/foreach}
{/if}
{if $adminUseCharts}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{$domain}templates/admin/js/admin-charts.js?v={$smarty.now}"></script>
{/if}
{if $adminUseOrderStatus}
<script>
window.__adminOrderStatus = {
	apiUrl: {$orderStatusApiUrl|@json_encode nofilter},
	token: {$adminToken|@json_encode nofilter}
};
</script>
<script src="{$domain}templates/admin/js/order-status.js?v={$smarty.now}"></script>
{/if}
{if $adminUseEditor}
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js"></script>
<script src="{$domain}templates/admin/js/admin-editor.js?v={$smarty.now}"></script>
{/if}
<script type="text/javascript">
if (window.history.replaceState) { window.history.replaceState(null, null, window.location.href); }
</script>
</body>
</html>
