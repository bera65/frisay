{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<a href="{$adminUrl}module-reviews?filter=pending" class="btn btn-sm {if $filter == 'pending'}btn-dark{else}btn-outline-dark{/if}">
		Onay Bekleyen ({$pendingCount})
	</a>
	<a href="{$adminUrl}module-reviews?filter=approved" class="btn btn-sm {if $filter == 'approved'}btn-dark{else}btn-outline-dark{/if}">Onaylı</a>
	<a href="{$adminUrl}module-reviews?filter=all" class="btn btn-sm {if $filter == 'all'}btn-dark{else}btn-outline-dark{/if}">Tümü</a>
</div>

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>Ürün</th>
					<th>Yazar</th>
					<th>Puan</th>
					<th>Yorum</th>
					<th>Durum</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $reviews|@count}
				{foreach $reviews as $row}
				<tr>
					<td>{$row.product_name|escape}</td>
					<td>{$row.author_name|escape}</td>
					<td>{$row.rating}/5</td>
					<td class="small" style="max-width:280px">
						{if $row.title}<strong>{$row.title|escape}</strong><br>{/if}
						{$row.comment|escape|truncate:120}
					</td>
					<td>{if $row.active}<span class="badge bg-success">Onaylı</span>{else}<span class="badge bg-warning text-dark">Bekliyor</span>{/if}</td>
					<td class="text-end text-nowrap">
						{if !$row.active}
						<form method="post" class="d-inline">
							<input type="hidden" name="reviewAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="id_review" value="{$row.id_review}">
							<button type="submit" name="action" value="approve" class="btn btn-sm btn-dark">Onayla</button>
						</form>
						{/if}
						<form method="post" class="d-inline" onsubmit="return confirm('Silinsin mi?');">
							<input type="hidden" name="reviewAction" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="id_review" value="{$row.id_review}">
							<button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger">Sil</button>
						</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="6" class="text-muted">Kayıt yok.</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{include file='admin/plugin/pagination.tpl'}
