<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<a href="{$adminUrl}coupon" class="btn btn-sm btn-primary ms-auto">+ Yeni Kupon</a>
</div>

{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>Kod</th>
					<th>İndirim</th>
					<th>Min. Sepet</th>
					<th>Kullanım</th>
					<th>Geçerlilik</th>
					<th>Durum</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $coupons|@count}
				{foreach $coupons as $row}
				<tr>
					<td><strong>{$row.code|escape}</strong></td>
					<td>{$row.discount_label}</td>
					<td>{$row.min_cart_formatted}</td>
					<td>{$row.used_count}{if $row.max_uses > 0} / {$row.max_uses}{/if}</td>
					<td class="small">
						{if $row.date_from}{$row.date_from|escape}{else}—{/if}
						→
						{if $row.date_to}{$row.date_to|escape}{else}—{/if}
					</td>
					<td>{if $row.active}Aktif{else}<span class="text-danger">Pasif</span>{/if}</td>
					<td class="text-end">
						<a href="{$adminUrl}coupon?id={$row.id_coupon}" class="btn btn-sm btn-outline-dark">Düzenle</a>
						<form method="post" class="d-inline" onsubmit="return confirm('Bu kupon silinsin mi?');">
							<input type="hidden" name="deleteCoupon" value="1">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="id_coupon" value="{$row.id_coupon}">
							<button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
						</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="7" class="text-muted">Henüz kupon yok.</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>
