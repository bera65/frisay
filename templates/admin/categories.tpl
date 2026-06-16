{if $sonuc}
	<div class="alert alert-success">{$sonuc}</div>
{/if}
<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<a href="{$adminUrl}categories" class="btn btn-sm {if $activeFilter == -1}btn-dark{else}btn-outline-dark{/if}">Tümü</a>
	<a href="{$adminUrl}categories?active=1" class="btn btn-sm {if $activeFilter == 1}btn-dark{else}btn-outline-dark{/if}">Aktif</a>
	<a href="{$adminUrl}categories?active=0" class="btn btn-sm {if $activeFilter == 0}btn-dark{else}btn-outline-dark{/if}">Pasif</a>
	<a href="{$adminUrl}category" class="btn btn-sm btn-primary ms-auto">+ Yeni Kategori</a>
</div>

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>ID</th>
					<th>Ad</th>
					<th>URL</th>
					<th>Üst Kategori</th>
					<th>Durum</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $categories|@count}
				{foreach $categories as $row}
				<tr>
					<td>{$row.id_category}</td>
					<td>{$row.category_name|escape}</td>
					<td>{$row.category_link|escape}</td>
					<td>{if $row.parent_name}{$row.parent_name|escape}{else}—{/if}</td>
					<td>{if $row.active}Aktif{else}<span class="text-danger">Pasif</span>{/if}</td>
					<td class="text-end">
						<form action="" method="POST">
							<a href="{$adminUrl}category?id={$row.id_category}" class="btn btn-sm btn-outline-dark">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
							</a>
							{if $row.id_category > 1}
							<input type="hidden" name="idCategory" value="{$row.id_category}" />
							<button type="submit" name="deleteCategory" value="{$adminToken}" class="btn btn-danger btn-sm">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
							</button>
							{/if}
						</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="6" class="text-muted">Kayıt bulunamadı.</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>
