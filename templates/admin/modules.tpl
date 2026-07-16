{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel module-toolbar mb-3">
	<div class="row g-2 align-items-center">
		<div class="col-md-8">
			<div class="input-group">
				<input type="search" class="form-control" id="moduleSearch" placeholder="{'Search modules…'|adminT}" autocomplete="off">
				<button type="button" class="btn btn-primary" tabindex="-1" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
				</button>
			</div>
		</div>
		<div class="col-md-4">
			<select class="form-select" id="moduleStatusFilter">
				<option value="all">{'Show all modules'|adminT}</option>
				<option value="installed">{'Installed modules'|adminT}</option>
				<option value="active">{'Active modules'|adminT}</option>
				<option value="not_installed">{'Not installed'|adminT}</option>
			</select>
		</div>
	</div>
</div>

<h2 class="h6 text-muted mb-2">{'Management'|adminT}</h2>

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
				v{$mod.version|escape} — {$mod.author|escape}{' by'|adminT}
				{if $mod.installed && $mod.active}
				· <span class="text-success">{'Active'|adminT}</span>
				{elseif $mod.installed}
				· <span class="text-secondary">{'Inactive'|adminT}</span>
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
				<a href="{$mod.configure_url}" class="btn btn-outline-primary module-btn-configure">{'Configure'|adminT}</a>
				<button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
					<span class="visually-hidden">{'More actions'|adminT}</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-end">
					<li><a class="dropdown-item" href="{$mod.detail_url}">{'Module details'|adminT}</a></li>
					{if $mod.active}
					<li>
						<form method="post" class="px-3 py-1">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="disable" class="dropdown-item px-0">{'Disable'|adminT}</button>
						</form>
					</li>
					{else}
					<li>
						<form method="post" class="px-3 py-1">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="enable" class="dropdown-item px-0">{'Enable'|adminT}</button>
						</form>
					</li>
					{/if}
					<li><hr class="dropdown-divider"></li>
					<li>
						<form method="post" class="px-3 py-1" onsubmit="return confirm('{'Uninstall this module?'|adminT}');">
							<input type="hidden" name="moduleAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="name" value="{$mod.name|escape}">
							<button type="submit" name="action" value="uninstall" class="dropdown-item text-danger px-0">{'Uninstall'|adminT}</button>
						</form>
					</li>
				</ul>
			</div>
			{else}
			<form method="post" class="d-inline">
				<input type="hidden" name="moduleAction" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<input type="hidden" name="name" value="{$mod.name|escape}">
				<button type="submit" name="action" value="install" class="btn btn-outline-primary module-btn-configure">{'Install'|adminT}</button>
			</form>
			{/if}
		</div>
	</div>
	{/foreach}
{else}
	<div class="p-4 text-muted">{'No modules found yet.'|adminT} {'Add modules to the'|adminT} <code>modules/</code> {'folder.'|adminT}</div>
{/if}
	<div class="p-4 text-muted d-none" id="moduleListEmpty">{'No modules match your search or filter.'|adminT}</div>
</div>
