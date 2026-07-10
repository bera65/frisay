{if $flash}
<div class="alert alert-{$flashType|default:'info'} py-2">{$flash|escape}</div>
{/if}

<ul class="nav nav-tabs mb-4">
	<li class="nav-item">
		<a class="nav-link{if $wapioView == 'settings'} active{/if}" href="{$adminUrl}module-wapio">Ayarlar</a>
	</li>
	<li class="nav-item">
		<a class="nav-link{if $wapioView == 'report'} active{/if}" href="{$adminUrl}module-wapio?view=report">Mesaj Raporu</a>
	</li>
</ul>

{if $wapioView == 'report'}
<div class="admin-panel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="h6 mb-0">Gönderilen mesajlar</h2>
		<span class="badge bg-secondary">Toplam: {$wapioReportTotal}</span>
	</div>

	{if $wapioReport|@count}
	<div class="table-responsive">
		<table class="table table-sm table-hover align-middle mb-0">
			<thead class="table-light">
				<tr>
					<th>Tarih</th>
					<th>Sipariş</th>
					<th>Telefon</th>
					<th>Durum</th>
					<th>Mesaj</th>
					<th>Sonuç</th>
				</tr>
			</thead>
			<tbody>
				{foreach $wapioReport as $row}
				<tr>
					<td class="small text-nowrap">{$row.date_formatted|escape}</td>
					<td>
						{if $row.id_order > 0}
						<a href="{$adminUrl}order?id={$row.id_order}">#{$row.order_reference|escape}</a>
						{else}
						<span class="text-muted">Test</span>
						{/if}
					</td>
					<td class="small">{$row.customer_phone|escape}</td>
					<td class="small">{$row.status_label|escape}</td>
					<td class="small" style="max-width:280px;white-space:pre-wrap;">{$row.message_short|escape}</td>
					<td>
						{if $row.success}
						<span class="badge bg-success">OK</span>
						{else}
						<span class="badge bg-danger" title="{$row.api_message|escape}">Hata</span>
						{/if}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>

	{if $wapioReportPages > 1}
	<nav class="mt-3">
		<ul class="pagination pagination-sm mb-0">
			{section name=p loop=$wapioReportPages}
			{assign var=pnum value=$smarty.section.p.index+1}
			<li class="page-item{if $pnum == $wapioReportPage} active{/if}">
				<a class="page-link" href="{$adminUrl}module-wapio?view=report&page={$pnum}">{$pnum}</a>
			</li>
			{/section}
		</ul>
	</nav>
	{/if}
	{else}
	<p class="text-muted mb-0">Henüz mesaj kaydı yok.</p>
	{/if}
</div>

{else}

<form method="post" action="{$adminUrl}module-wapio">
	<input type="hidden" name="token" value="{$adminToken}">

	<div class="row g-4">
		<div class="col-lg-5">
			<div class="admin-panel h-100">
				<h2 class="h6 mb-3">API bağlantısı</h2>
				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" id="wapioEnabled" name="wapio_enabled" value="1"{if $wapioEnabled} checked{/if}>
					<label class="form-check-label" for="wapioEnabled">WhatsApp bildirimleri aktif</label>
				</div>
				<div class="mb-3">
					<label class="form-label" for="wapioSessionId">Wapio Session ID</label>
					<input type="text" class="form-control" id="wapioSessionId" name="wapio_session_id" value="{$wapioSessionId|escape}" placeholder="Wapio panelinden alın">
					<div class="form-text">API isteğinde <code>session_id</code> header olarak gönderilir.</div>
				</div>
				<button type="submit" name="saveWapio" value="1" class="btn btn-dark">Kaydet</button>
			</div>
		</div>

		<div class="col-lg-7">
			<div class="admin-panel h-100">
				<h2 class="h6 mb-3">Test mesajı</h2>
				<div class="mb-3">
					<label class="form-label">Telefon</label>
					<input type="text" name="test_phone" class="form-control" placeholder="05551234567">
				</div>
				<div class="mb-3">
					<label class="form-label">Mesaj</label>
					<textarea name="test_message" class="form-control" rows="3" placeholder="Boş bırakılırsa varsayılan test metni gönderilir"></textarea>
				</div>
				<button type="submit" name="testWapio" value="1" class="btn btn-outline-dark">Test gönder</button>
			</div>
		</div>
	</div>

	<div class="admin-panel mt-4">
		<h2 class="h6 mb-2">Durum mesajları</h2>
		<p class="text-muted small mb-3">
			Her sipariş durumu için ayrı mesaj şablonu. Kullanılabilir değişkenler:
			{foreach $wapioPlaceholders as $ph name=phLoop}
			{if !$smarty.foreach.phLoop.first}, {/if}<code>{$ph|escape}</code>
			{/foreach}
		</p>

		{foreach $wapioTemplateRows as $row}
		<div class="border rounded p-3 mb-3 bg-light-subtle">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
				<strong>{$row.status_label|escape}</strong>
				<div class="form-check form-switch mb-0">
					<input type="hidden" name="templates[{$row.status_id}][enabled]" value="0">
					<input class="form-check-input" type="checkbox" name="templates[{$row.status_id}][enabled]" value="1"{if $row.enabled} checked{/if} id="wapioTpl{$row.status_id}">
					<label class="form-check-label small" for="wapioTpl{$row.status_id}">Gönder</label>
				</div>
			</div>
			<textarea name="templates[{$row.status_id}][message]" class="form-control form-control-sm" rows="4">{$row.message|escape}</textarea>
		</div>
		{/foreach}

		<button type="submit" name="saveWapio" value="1" class="btn btn-dark">Şablonları kaydet</button>
	</div>
</form>

<div class="alert alert-info mt-4 small mb-0">
	<strong>Nasıl çalışır?</strong>
	Sipariş verildiğinde <em>Beklemede</em> şablonu, admin panelden durum değişince ilgili durum şablonu gönderilir.
	Aynı sipariş + durum için başarılı mesaj bir kez gönderilir. Tüm denemeler <a href="{$adminUrl}module-wapio?view=report">Mesaj Raporu</a> sekmesinde listelenir.
</div>

{/if}
