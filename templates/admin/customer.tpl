{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-4">
		<div class="admin-panel p-3">
			<h2 class="h6 mb-3">Müşteri Bilgileri</h2>
			<p class="mb-1 text-muted small">Ad Soyad</p>
			<p class="fw-semibold">{$customer.user_full_name|escape}</p>
			<p class="mb-1 text-muted small">Telefon</p>
			<p>{$customer.phone|escape}</p>
			<p class="mb-1 text-muted small">E-posta</p>
			<p>{if $customer.email}{$customer.email|escape}{else}<span class="text-muted">Belirtilmemiş</span>{/if}</p>
			<p class="mb-1 text-muted small">Kayıt Tarihi</p>
			<p>{$customer.date_formatted}</p>
			<p class="mb-3">
				{if $customer.active}
				<span class="badge bg-success">Aktif</span>
				{else}
				<span class="badge bg-danger">Pasif</span>
				{/if}
			</p>
			<form method="post">
				<input type="hidden" name="toggleActive" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<button type="submit" class="btn btn-sm {if $customer.active}btn-outline-danger{else}btn-outline-success{/if}">
					{if $customer.active}Hesabı Pasifleştir{else}Hesabı Aktifleştir{/if}
				</button>
			</form>
		</div>
	</div>

	<div class="col-lg-8">
		<div class="admin-panel p-3">
			<h2 class="h6 mb-3">Sipariş Geçmişi</h2>
			{if $customer.orders|@count}
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>Referans</th>
							<th>Durum</th>
							<th>Toplam</th>
							<th>Tarih</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $customer.orders as $order}
						<tr>
							<td>{$order.reference|escape}</td>
							<td>{$order.status_label|escape}</td>
							<td>{$order.total_formatted}</td>
							<td>{$order.date_formatted}</td>
							<td class="text-end"><a href="{$adminUrl}order?id={$order.id_order}" class="btn btn-sm btn-outline-dark">Görüntüle</a></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<p class="text-muted mb-0">Henüz sipariş yok.</p>
			{/if}
		</div>
	</div>
</div>
