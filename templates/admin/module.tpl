{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<p class="mb-3"><a href="{$adminUrl}modules">&larr; Modül listesine dön</a></p>

<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel mb-4">
			<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
				<div>
					<h2 class="h5 mb-1">{$mod.title|escape}</h2>
					<p class="text-muted small mb-0">{$mod.name|escape} · v{$mod.version|escape}{if $mod.author} · {$mod.author|escape}{/if}</p>
				</div>
				{if $mod.installed && $mod.active}
				<span class="badge bg-success">Aktif</span>
				{elseif $mod.installed}
				<span class="badge bg-secondary">Pasif</span>
				{else}
				<span class="badge bg-light text-dark">Kurulu değil</span>
				{/if}
			</div>
			<p class="mb-0">{$mod.description|escape}</p>
		</div>

		{if $mod.display_hooks|@count}
		<div class="admin-panel mb-4">
			<h3 class="h6 mb-3">Görünür hook ataması</h3>
			<p class="text-muted small mb-3">
				Modülün sitede hangi alanda görüneceğini seçin. Mağazada
				<code>{ldelim}$hooks.footer{rdelim}</code>, admin panelde
				<code>{ldelim}$adminHooks.admin_product_button{rdelim}</code> gibi hook noktaları kullanılır.
			</p>
			{if $mod.installed}
			<form method="post" class="mb-0">
				<input type="hidden" name="moduleAction" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="d-flex flex-column gap-2 mb-3">
					{foreach $displayHookCatalog as $hookKey => $hookTpl}
					<label class="d-flex align-items-start gap-2 border rounded p-3 mb-0{if !isset($mod.display_hooks[$hookKey])} opacity-50{/if}">
						<input type="checkbox" name="displayHooks[]" value="{$hookKey|escape}"
							{if isset($mod.assigned_hooks_map[$hookKey])}checked{/if}
							{if !isset($mod.display_hooks[$hookKey])}disabled{/if}
							class="mt-1">
						<span>
							<strong><code>{$hookKey|escape}</code></strong>
							<span class="d-block small text-muted">{$hookTpl|escape}</span>
							{if isset($mod.display_hooks[$hookKey])}
							<span class="d-block small">{$mod.display_hooks[$hookKey]|escape}</span>
							{else}
							<span class="d-block small text-muted">Bu modül bu hook'u desteklemiyor</span>
							{/if}
						</span>
					</label>
					{/foreach}
				</div>
				<button type="submit" name="action" value="save_hooks" class="btn btn-sm btn-dark">Hook atamalarını kaydet</button>
			</form>
			{else}
			<ul class="list-unstyled mb-0">
				{foreach $mod.display_hooks as $hookKey => $hookLabel}
				<li class="mb-2">
					<code>{$hookKey|escape}</code>
					<span class="text-muted">— {$hookLabel|escape}</span>
				</li>
				{/foreach}
			</ul>
			<p class="text-muted small mt-2 mb-0">Kurulumda varsayılan hook'lar otomatik atanır; kurduktan sonra buradan değiştirebilirsiniz.</p>
			{/if}
		</div>
		{/if}

		{if $mod.hooks_meta|@count}
		<div class="admin-panel mb-4">
			<h3 class="h6 mb-3">Kullandığı hook'lar</h3>
			<p class="text-muted small">Hook'lar modülün <code>boot()</code> metodunda kayıt olur ve mağaza/admin akışına müdahale eder.</p>
			<ul class="list-unstyled mb-0">
				{foreach $mod.hooks_meta as $hookName => $hookDesc}
				<li class="mb-2">
					<strong><code>{$hookName|escape}</code></strong>
					{if $hookCatalog[$hookName]}<span class="text-muted small d-block">{$hookCatalog[$hookName]|escape}</span>{/if}
					<span class="d-block small">{$hookDesc|escape}</span>
				</li>
				{/foreach}
			</ul>
		</div>
		{/if}

		{if $mod.installed}
		<div class="admin-panel">
			<h3 class="h6 mb-3">Yapılandırma</h3>
			<p class="text-muted small mb-3">
				Modül ayarları <code>{$mod.name|escape}</code> klasöründeki
				<code>adminPage()</code> ve <code>assets/templates/admin/admin.tpl</code> ile yönetilir.
			</p>
			<a href="{$mod.configure_url}" class="btn btn-sm btn-dark">{$mod.title|escape} — Yapılandır</a>
		</div>
		{/if}
	</div>

	<div class="col-lg-4">
		{if $mod.installed}
		<div class="admin-panel mb-4">
			{if $mod.logo_url}
			<img src="{$mod.logo_url|escape}" alt="" width="48" height="48" class="rounded mb-3">
			{/if}
			<a href="{$mod.configure_url}" class="btn btn-primary w-100">Yapılandır</a>
			<p class="small text-muted mt-2 mb-0"><code>/admin/module-{$mod.name|escape}</code></p>
		</div>
		{/if}

		<div class="admin-panel mb-4">
			<h3 class="h6 mb-3">Durum ve işlemler</h3>
			<form method="post" class="d-flex flex-column gap-2">
				<input type="hidden" name="moduleAction" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				{if !$mod.installed}
				<button type="submit" name="action" value="install" class="btn btn-dark">Kur ve etkinleştir</button>
				{else}
					{if !$mod.active}
					<button type="submit" name="action" value="enable" class="btn btn-dark">Etkinleştir</button>
					{else}
					<button type="submit" name="action" value="disable" class="btn btn-outline-secondary">Devre dışı bırak</button>
					{/if}
					<button type="submit" name="action" value="uninstall" class="btn btn-outline-danger" onclick="return confirm('Modül kaldırılsın mı? Veriler silinebilir.');">Kaldır</button>
				{/if}
			</form>
		</div>

		{if $mod.api_actions|@count}
		<div class="admin-panel">
			<h3 class="h6 mb-3">API uçları</h3>
			<ul class="list-unstyled small mb-0">
				{foreach $mod.api_actions as $api}
				<li class="mb-2">
					<code>{$api.action|escape}</code>
					<div class="text-muted text-break">{$api.endpoint|escape}</div>
				</li>
				{/foreach}
			</ul>
		</div>
		{/if}
	</div>
</div>
