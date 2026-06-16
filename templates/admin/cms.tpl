<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>Sayfa</th>
					<th>Slug</th>
					<th>Açıklama</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach $cmsPages as $page}
				<tr>
					<td>{$page.title|escape}</td>
					<td>{$page.slug|escape}</td>
					<td class="text-muted">{$page.desc|escape}</td>
					<td class="text-end">
						<a href="{$page.edit_url}" class="btn btn-sm btn-outline-dark">Düzenle</a>
						<a href="{$domain}{$page.slug}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Görüntüle</a>
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>

<p class="text-muted small mt-3 mb-0">CMS içerikleri Smarty şablon dosyalarında saklanır. Düzenleme HTML içerik olarak kaydedilir.</p>
