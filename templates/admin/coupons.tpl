<div class="admin-toolbar d-flex flex-wrap gap-2 mb-3">
	<ul class="nav nav-pills">
		<li class="nav-item">
			<a class="nav-link active" href="#couponCodes" data-bs-toggle="tab">Kupon Kodları</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="#cartPromotions" data-bs-toggle="tab">Sepet Kampanyaları</a>
		</li>
	</ul>
</div>

{if $flash}
<div class="alert alert-info">{$flash|escape}</div>
{/if}

<div class="tab-content">
	<div class="tab-pane fade show active" id="couponCodes">
		<div class="d-flex justify-content-end mb-3">
			<a href="{$adminUrl}coupon" class="btn btn-sm btn-primary">+ Yeni Kupon</a>
		</div>

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
	</div>

	<div class="tab-pane fade" id="cartPromotions">
		<div class="alert alert-light border small mb-3">
			Sepet kampanyaları otomatik uygulanır; müşteri kod girmek zorunda değildir.
			<strong>N. ürüne indirim:</strong> örn. 2. ürüne 10 TL veya %5.
			<strong>X al Y öde:</strong> örn. 3 al 2 öde (en ucuz ürün bedava).
			Aynı anda birden fazla kampanya varsa öncelik değeri yüksek olan uygulanır.
		</div>

		<div class="d-flex justify-content-end mb-3">
			<a href="{$adminUrl}cart-promotion" class="btn btn-sm btn-primary">+ Yeni Sepet Kampanyası</a>
		</div>

		<div class="admin-panel">
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0">
					<thead>
						<tr>
							<th>Kampanya</th>
							<th>Kural</th>
							<th>Min. Sepet</th>
							<th>Öncelik</th>
							<th>Geçerlilik</th>
							<th>Durum</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{if $promotions|@count}
						{foreach $promotions as $row}
						<tr>
							<td><strong>{$row.name|escape}</strong></td>
							<td>{$row.rule_label|escape}</td>
							<td>{$row.min_cart_formatted}</td>
							<td>{$row.priority}</td>
							<td class="small">
								{if $row.date_from}{$row.date_from|escape}{else}—{/if}
								→
								{if $row.date_to}{$row.date_to|escape}{else}—{/if}
							</td>
							<td>{if $row.active}Aktif{else}<span class="text-danger">Pasif</span>{/if}</td>
							<td class="text-end">
								<a href="{$adminUrl}cart-promotion?id={$row.id_promotion}" class="btn btn-sm btn-outline-dark">Düzenle</a>
								<form method="post" class="d-inline" onsubmit="return confirm('Bu kampanya silinsin mi?');">
									<input type="hidden" name="deletePromotion" value="1">
									<input type="hidden" name="token" value="{$adminToken}">
									<input type="hidden" name="id_promotion" value="{$row.id_promotion}">
									<button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
								</form>
							</td>
						</tr>
						{/foreach}
						{else}
						<tr><td colspan="7" class="text-muted">Henüz sepet kampanyası yok.</td></tr>
						{/if}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var hash = window.location.hash;
	if (hash === '#cartPromotions') {
		var tab = document.querySelector('[href="#cartPromotions"]');
		if (tab && window.bootstrap) {
			new bootstrap.Tab(tab).show();
		}
	}
});
</script>
