			</div>
		</div>
	</div>
</div>
<script src="{$domain}templates/admin/js/admin.js?v={$smarty.now}"></script>
<script src="{$domain}templates/admin/js/popper.min.js"></script>
<script src="{$domain}templates/admin/js/bootstrap.min.js"></script>
{if $moduleAdminAssets.js|@count}
{foreach $moduleAdminAssets.js as $moduleJs}
<script src="{$moduleJs}?v={$smarty.now}"></script>
{/foreach}
{/if}
{if $adminUseCharts}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{$domain}templates/admin/js/admin-charts.js?v={$smarty.now}"></script>
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
