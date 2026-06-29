{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<span class="badge bg-success align-self-center">Aktif abone: {$activeCount}</span>
	<a href="{$moduleDetailUrl}" class="btn btn-sm btn-outline-secondary ms-auto">Modül Detayı</a>
</div>

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>E-posta</th>
					<th>Durum</th>
					<th>Tarih</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $subscribers|@count}
				{foreach $subscribers as $row}
				<tr>
					<td>{$row.email|escape}</td>
					<td>{if $row.active}Aktif{else}<span class="text-danger">Pasif</span>{/if}</td>
					<td>{$row.date_add|escape}</td>
					<td class="text-end">
						<form method="post" class="d-inline">
							<input type="hidden" name="toggleSubscriber" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="id_subscriber" value="{$row.id_subscriber}">
							<button type="submit" class="btn btn-sm btn-outline-dark">
								{if $row.active}Pasifleştir{else}Aktifleştir{/if}
							</button>
						</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="4" class="text-muted">Henüz abone yok.</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{include file='admin/plugin/pagination.tpl'}
