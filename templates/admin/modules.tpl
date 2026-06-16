{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel module-toolbar mb-3">
	<div class="row g-2 align-items-center">
		<div class="col-md-8">
			<div class="input-group">
				<input type="search" class="form-control" id="moduleSearch" placeholder="Modül ara…" autocomplete="off">
				<button type="button" class="btn btn-primary" tabindex="-1" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</button>
			</div>
		</div>
		<div class="col-md-4">
			<select class="form-select" id="moduleStatusFilter">
				<option value="all">Tüm modülleri göster</option>
				<option value="installed">Kurulu modüller</option>
				<option value="active">Aktif modüller</option>
				<option value="not_installed">Kurulu olmayanlar</option>
			</select>
		</div>
	</div>
</div>

<h2 class="h6 text-muted mb-2">Yönetim</h2>

<div class="admin-panel p-0 overflow-hidden" id="moduleList">
{if $modules|@count}
	{foreach $modules as $mod}
	<div class="module-row d-flex flex-wrap align-items-center gap-3"
		data-module-search="{$mod.title|escape} {$mod.name|escape} {$mod.description|escape}"
		data-module-status="{if $mod.installed && $mod.active}active{elseif $mod.installed}installed{else}not_installed{/if}">
		<div class="module-row-icon flex-shrink-0">
			{if $mod.icon_url}
			<img src="{$mod.icon_url|escape}" alt="" width="48" height="48">
			{else}
			<span class="module-row-letter">{$mod.icon_letter|escape}</span>
			{/if}
		</div>
		<div class="module-row-body flex-grow-1 min-w-0">
			<div class="module-row-title">{$mod.title|escape}</div>
			<div class="module-row-meta">
				v{$mod.version|escape} — {$mod.author|escape} tarafından
				{if $mod.installed && $mod.active}
				· <span class="text-success">Aktif</span>
				{elseif $mod.installed}
				· <span class="text-secondary">Pasif</span>
				{/if}
				{if $mod.assigned_hooks|@count}
				· Hook: {foreach $mod.assigned_hooks as $hk name=hkLoop}<code>{$hk|escape}</code>{if !$smarty.foreach.hkLoop.last}, {/if}{/foreach}
				{/if}
			</div>
			<div class="module-row-desc text-muted">{$mod.description|escape}</div>
		</div>
		<div class="module-row-actions d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
			{if $mod.installed}
			<div class="btn-group">
				<a href="{$mod.configure_url}" class="btn btn-outline-primary module-btn-configure">Yapılandır</a>
				<button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
					<span class="visually-hidden">Diğer işlemler</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-end">
					<li><a class="dropdown-item" href="{$mod.detail_url}">Modül detayı</a></li>
					{if $mod.active}
					<li>
						<form method="post" class="px-3 py-1">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="disable" class="dropdown-item px-0">Devre dışı bırak</button>
						</form>
					</li>
					{else}
					<li>
						<form method="post" class="px-3 py-1">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="enable" class="dropdown-item px-0">Etkinleştir</button>
						</form>
					</li>
					{/if}
					<li><hr class="dropdown-divider"></li>
					<li>
						<form method="post" class="px-3 py-1" onsubmit="return confirm('Modül kaldırılsın mı?');">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="uninstall" class="dropdown-item text-danger px-0">Kaldır</button>
						</form>
					</li>
				</ul>
			</div>
			{else}
			<form method="post" class="d-inline">
				<input type="hidden" name="moduleAction" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="name" value="{$mod.name|escape}">
				<button type="submit" name="action" value="install" class="btn btn-outline-primary module-btn-configure">Kur</button>
			</form>
			{/if}
		</div>
	</div>
	{/foreach}
{else}
	<div class="p-4 text-muted">Henüz modül bulunamadı. <code>modules/</code> klasörüne modül ekleyin.</div>
{/if}
</div>

<script>
(function () {
	var search = document.getElementById('moduleSearch');
	var filter = document.getElementById('moduleStatusFilter');
	var rows = document.querySelectorAll('#moduleList .module-row');

	function applyFilters() {
		var q = (search && search.value || '').toLowerCase().trim();
		var status = filter ? filter.value : 'all';
		rows.forEach(function (row) {
			var text = (row.getAttribute('data-module-search') || '').toLowerCase();
			var rowStatus = row.getAttribute('data-module-status') || '';
			var matchQ = !q || text.indexOf(q) !== -1;
			var matchS = status === 'all' || rowStatus === status;
			row.style.display = matchQ && matchS ? '' : 'none';
		});
	}

	if (search) search.addEventListener('input', applyFilters);
	if (filter) filter.addEventListener('change', applyFilters);
})();
</script>
