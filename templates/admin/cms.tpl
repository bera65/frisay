<div class="admin-panel mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
	<div class="d-flex gap-2">
		<a href="{$adminUrl}cms-edit" class="btn btn-dark btn-sm">Yeni Sayfa Ekle</a>
		<a href="{$adminUrl}languages" class="btn btn-outline-secondary btn-sm">Dilleri Yönet</a>
	</div>
</div>

{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>Sayfa</th>
					<th>Slug</th>
					<th>Durum</th>
					<th>Footer</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach $cmsPages as $page}
				<tr>
					<td>{$page.title|default:$page.slug|escape}</td>
					<td><code>{$page.slug|escape}</code></td>
					<td>{if $page.active}<span class="badge bg-success">Aktif</span>{else}<span class="badge bg-secondary">Pasif</span>{/if}</td>
					<td>{if $page.show_footer}Evet{else}Hayır{/if}</td>
					<td class="text-end">
						<a href="{$page.edit_url}" class="btn btn-sm btn-outline-dark">Düzenle</a>
						<a href="{$domain}{$page.slug|escape}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Görüntüle</a>
						<form method="post" class="d-inline" onsubmit="return confirm('Bu sayfa silinsin mi?');">
							<input type="hidden" name="deleteCms" value="1">
							<input type="hidden" name="id" value="{$page.id_cms}">
							<input type="hidden" name="token" value="{$adminToken}">
							<button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
						</form>
					</td>
				</tr>
				{foreachelse}
				<tr><td colspan="5" class="text-muted">Henüz CMS sayfası yok.</td></tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>

<p class="text-muted small mt-3 mb-0">CMS içerikleri veritabanında saklanır. Her dil için ayrı başlık ve içerik girebilirsiniz.</p>
