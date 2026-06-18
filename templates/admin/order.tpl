{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">Sipariş Bilgileri</h2>
			<div class="row g-2 small">
				<div class="col-md-6"><strong>Sipariş No:</strong> {$order.reference|escape}</div>
				<div class="col-md-6"><strong>Tarih:</strong> {$order.date_formatted}</div>
				<div class="col-md-6"><strong>Müşteri:</strong> {$order.customer_name|escape}</div>
				<div class="col-md-6"><strong>Telefon:</strong> {$order.customer_phone|escape}</div>
				{if $order.company_name}<div class="col-md-6"><strong>Firma:</strong> {$order.company_name|escape}</div>{/if}
				{if $order.tax_office}<div class="col-md-6"><strong>Vergi Dairesi:</strong> {$order.tax_office|escape}</div>{/if}
				{if $order.tax_number}<div class="col-md-6"><strong>Vergi No / TCKN:</strong> {$order.tax_number|escape}</div>{/if}
				<div class="col-12"><strong>Adres:</strong> {$order.address_city|escape} / {$order.address_district|escape} — {$order.address_text|escape}</div>
				{if $order.note}<div class="col-12"><strong>Not:</strong> {$order.note|escape}</div>{/if}
				{if $order.cargo_company}<div class="col-md-6"><strong>Kargo:</strong> {$order.cargo_company|escape}</div>{/if}
				{if $order.tracking_number}<div class="col-md-6"><strong>Takip No:</strong> {$order.tracking_number|escape}</div>{/if}
			</div>
		</div>

		<div class="admin-panel">
			<h2 class="h6 mb-3">Ürünler</h2>
			<div class="table-responsive">
				<table class="table table-sm mb-0">
					<thead>
						<tr><th>Ürün</th><th>Adet</th><th>Birim</th><th>Toplam</th></tr>
					</thead>
					<tbody>
						{foreach $order.items as $item}
						<tr>
							<td>{$item.product_name|escape}</td>
							<td>{$item.qty}</td>
							<td>{$item.price_formatted}</td>
							<td>{$item.total_formatted}</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">Özet</h2>
			<p class="mb-1 d-flex justify-content-between"><span>Ara Toplam</span><span>{$order.subtotal_formatted}</span></p>
			<p class="mb-1 d-flex justify-content-between"><span>Kargo</span><span>{$order.shipping_formatted}</span></p>
			<p class="mb-3 d-flex justify-content-between fw-bold"><span>Toplam</span><span>{$order.total_formatted}</span></p>
			<p class="mb-0 small text-muted">Ödeme: {$order.payment_label|escape}</p>
		</div>

		<div class="admin-panel">
			<h2 class="h6 mb-3">Durum ve Kargo</h2>
			<form method="post">
				<input type="hidden" name="updateStatus" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<select name="status" class="form-select mb-3">
					{foreach $statusOptions as $statusId => $statusLabel}
					<option value="{$statusId}"{if $order.status == $statusId} selected{/if}>{$statusLabel|escape}</option>
					{/foreach}
				</select>
				<label class="form-label small mb-1">Kargo Firması</label>
				<input type="text" name="cargo_company" class="form-control mb-3" value="{$order.cargo_company|default:''|escape}" placeholder="Yurtiçi Kargo">
				<label class="form-label small mb-1">Takip Numarası</label>
				<input type="text" name="tracking_number" class="form-control mb-3" value="{$order.tracking_number|default:''|escape}" placeholder="YT123456789">
				<button type="submit" class="btn btn-dark w-100">Kaydet</button>
			</form>
		</div>
	</div>
</div>

<p class="mt-3"><a href="{$adminUrl}orders">&larr; Sipariş listesine dön</a></p>
