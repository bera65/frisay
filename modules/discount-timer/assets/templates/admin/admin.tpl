{if $flash}
<div class="alert alert-info py-2">{$flash|escape}</div>
{/if}

<div class="admin-panel p-3" style="max-width: 640px;">
	<h2 class="h6 mb-3">İndirim Sayacı Ayarları</h2>
	<form method="post">
		<input type="hidden" name="saveDiscountTimer" value="1">
		<input type="hidden" name="token" value="{$adminToken}">

		<div class="mb-3">
			<label class="form-label">Başlık</label>
			<input type="text" name="timer_title" class="form-control" value="{$timerTitle|escape}">
		</div>
		<div class="mb-3">
			<label class="form-label">Alt başlık</label>
			<input type="text" name="timer_subtitle" class="form-control" value="{$timerSubtitle|escape}">
		</div>
		<div class="mb-3">
			<label class="form-label">Konum</label>
			<select name="timer_position" class="form-select">
				<option value="top"{if $timerPosition == 'top'} selected{/if}>Ürün kutusunun üstü (önerilen)</option>
				<option value="inf"{if $timerPosition == 'inf'} selected{/if}>Ürün başlığının altı</option>
			</select>
		</div>

		<button type="submit" class="btn btn-dark">Kaydet</button>
	</form>

	<div class="alert alert-light border small mt-3 mb-0">
		Ürün düzenleme → <strong>Fiyat</strong> bölümünde <em>İndirim başlangıç / bitiş</em> tarihlerini girin.
		<ul class="mb-0 mt-2">
			<li>Başlangıçtan önce indirim görünmez (normal fiyat gösterilir)</li>
			<li>Tarihler arasında indirim + sayaç aktif olur</li>
			<li>Bitişte <strong>eski fiyat</strong> satış fiyatına taşınır, indirim kalkar</li>
		</ul>
		<div class="mt-2">Cron (isteğe bağlı): <code>{$cronUrl|escape}</code></div>
	</div>
</div>
