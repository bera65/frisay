<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<a href="{$adminUrl}messages" class="btn btn-sm {if $readFilter === null}btn-dark{else}btn-outline-dark{/if}">Tümü</a>
	<a href="{$adminUrl}messages?read=0" class="btn btn-sm {if $readFilter === 0}btn-dark{else}btn-outline-dark{/if}">Okunmamış</a>
	<a href="{$adminUrl}messages?read=1" class="btn btn-sm {if $readFilter === 1}btn-dark{else}btn-outline-dark{/if}">Okunmuş</a>
</div>

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>Gönderen</th>
					<th>Konu</th>
					<th>E-posta</th>
					<th>Durum</th>
					<th>Tarih</th>
				</tr>
			</thead>
			<tbody>
				{if $messages|@count}
				{foreach $messages as $row}
				<tr class="{if !$row.is_read}fw-semibold{/if}">
					<td><a href="{$adminUrl}message?id={$row.id_message}">{$row.full_name|escape}</a></td>
					<td>{$row.subject|escape}</td>
					<td>{$row.email|escape}</td>
					<td>{if $row.is_read}Okundu{else}Yeni{/if}</td>
					<td>{$row.date_formatted}</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="5" class="text-muted">Mesaj bulunamadı.</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{include file='admin/plugin/pagination.tpl'}
