{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel mb-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<h2 class="h6 mb-0">Sipariş Bilgileri</h2>
	<a href="{$adminUrl}order-print?id={$order.id_order}&amp;auto=1" class="btn btn-sm btn-outline-dark" target="_blank" rel="noopener">
		<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
		Yazdır
	</a>
</div>
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
							<td>
								<div>{$item.product_name|escape}</div>
								{if $item.is_virtual}
								<div class="small mt-2 text-muted">
									<span class="badge bg-info">{$item.virtual_kind_label|escape}</span>
									{if $item.virtual_kind == 'license'}
										{if $item.license_keys|@count}
										<div class="mt-2">
											<strong class="text-dark">Verilen lisanslar:</strong>
											{foreach $item.license_keys as $lic}
											<code class="d-block mt-1">{$lic.license_key|escape}</code>
											{if $lic.date_used}<span class="text-muted">({$lic.date_used|escape})</span>{/if}
											{/foreach}
										</div>
										{else}
										<div class="mt-1">Henüz lisans atanmadı (ödeme onayı bekleniyor olabilir).</div>
										{/if}
									{elseif $item.virtual_kind == 'download'}
										{if $item.has_download}
										<div class="mt-1">Dosya: <strong>{$item.virtual_delivery_admin|default:'Dijital dosya'|escape}</strong></div>
										{else}
										<div class="mt-1">İndirme henüz aktif değil.</div>
										{/if}
									{elseif $item.virtual_kind == 'text' && $item.virtual_delivery_admin}
										<div class="mt-1">{$item.virtual_delivery_admin|escape|nl2br nofilter}</div>
									{/if}
								</div>
								{/if}
							</td>
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
{if $adminHooks.admin_order_detail}
<div class="admin-panel mt-4">
	{$adminHooks.admin_order_detail nofilter}
</div>
{/if}

<p class="mt-3"><a href="{$adminUrl}orders">&larr; Sipariş listesine dön</a></p>
