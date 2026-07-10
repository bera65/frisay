{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-8">
		<div class="admin-panel mb-4">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
				<h2 class="h6 mb-0">İade Talebi #{$returnItem.id_return}</h2>
				<span class="badge {$returnItem.status_badge}">{$returnItem.status_label|escape}</span>
			</div>
			<div class="row g-2 small">
				<div class="col-md-6"><strong>Sipariş:</strong> <a href="{$adminUrl}order?id={$returnItem.id_order}">#{$returnItem.reference|escape}</a></div>
				<div class="col-md-6"><strong>Talep tarihi:</strong> {$returnItem.date_formatted}</div>
				<div class="col-md-6"><strong>Müşteri:</strong> {$returnItem.user_name|escape}</div>
				<div class="col-md-6"><strong>Telefon:</strong> {$returnItem.user_phone|escape}</div>
				{if $returnItem.user_email}<div class="col-md-6"><strong>E-posta:</strong> {$returnItem.user_email|escape}</div>{/if}
				<div class="col-md-6"><strong>Sipariş durumu:</strong> {$returnItem.order_status_label|escape}</div>
				<div class="col-md-6"><strong>Sipariş tutarı:</strong> {$returnItem.total_formatted}</div>
			</div>
		</div>

		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">Müşteri Mesajı</h2>
			<p class="mb-0">{$returnItem.customer_message|escape|nl2br}</p>
		</div>

		{if $returnItem.images|@count}
		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">Görseller</h2>
			<div class="d-flex flex-wrap gap-2">
				{foreach $returnItem.images as $img}
				<a href="{$img.url|escape}" target="_blank" rel="noopener">
					<img src="{$img.url|escape}" alt="" class="rounded border" style="width:120px;height:120px;object-fit:cover;">
				</a>
				{/foreach}
			</div>
		</div>
		{/if}

		{if $returnItem.admin_message && $returnItem.status != $statusPending}
		<div class="admin-panel{if $returnItem.admin_receipt_url} mb-4{else}{/if}">
			<h2 class="h6 mb-3">Mağaza Yanıtı</h2>
			<p class="mb-0">{$returnItem.admin_message|escape|nl2br}</p>
			{if $returnItem.resolved_formatted}
			<p class="small text-muted mt-2 mb-0">{$returnItem.resolved_formatted}</p>
			{/if}
		</div>
		{/if}

		{if $returnItem.admin_receipt_url}
		<div class="admin-panel">
			<h2 class="h6 mb-3">İade Dekontu</h2>
			<a href="{$returnItem.admin_receipt_url|escape}" target="_blank" rel="noopener">
				<img src="{$returnItem.admin_receipt_url|escape}" alt="İade dekontu" class="rounded border" style="max-width:280px;max-height:280px;object-fit:contain;">
			</a>
		</div>
		{/if}
	</div>

	<div class="col-lg-4">
		{if $returnItem.status == $statusPending}
		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">Talebi İşle</h2>
			<p class="small text-muted">Onayladığınızda müşteriye mesajınız gider ve sipariş durumu <strong>İade edildi</strong> olur.</p>
			<form method="post" class="mb-3">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="mb-3">
					<label class="form-label">Müşteriye mesaj</label>
					<textarea name="admin_message" class="form-control" rows="4" required maxlength="5000" placeholder="İade onayı ve talimatlarınızı yazın"></textarea>
				</div>
				<div class="d-grid gap-2">
					<button type="submit" name="approveReturn" value="1" class="btn btn-primary">Onayla ve süreci başlat</button>
					<button type="submit" name="rejectReturn" value="1" class="btn btn-outline-danger" onclick="return confirm('İade talebi reddedilsin mi?');">Reddet</button>
				</div>
			</form>
		</div>
		{elseif $returnItem.status == $statusApproved}
		<div class="admin-panel mb-4">
			<h2 class="h6 mb-3">İadeyi Tamamla</h2>
			<p class="small text-muted">İade işlemi bittiğinde müşteriye dekont yükleyebilir ve ek mesaj yazabilirsiniz.</p>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="mb-3">
					<label class="form-label">Ek mesaj (isteğe bağlı)</label>
					<textarea name="admin_message" class="form-control" rows="3" maxlength="5000" placeholder="İade tamamlandı bilgisi"></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label">İade dekontu (isteğe bağlı)</label>
					<input type="file" name="admin_receipt" class="form-control" accept="image/jpeg,image/png,image/webp">
					<div class="form-text">Müşteri bu görseli iade detayında görebilir. JPG, PNG veya WEBP — en fazla 5 MB.</div>
				</div>
				<button type="submit" name="completeReturn" value="1" class="btn btn-success w-100">İadeyi tamamlandı olarak işaretle</button>
			</form>
		</div>
		{/if}

		<div class="admin-panel">
			<a href="{$adminUrl}returns" class="btn btn-outline-secondary btn-sm w-100">← İade listesine dön</a>
		</div>
	</div>
</div>
