<div class="bankwire-pay mt-3">
	<div class="bankwire-pay__head">
		<span class="bankwire-pay__badge">Havale / EFT</span>
		<strong class="bankwire-pay__title">Ödeme Bilgileri</strong>
		<p class="bankwire-pay__hint mb-0">Aşağıdaki tutarı, açıklama alanında sipariş numaranızı yazarak gönderin.</p>
	</div>

	<div class="bankwire-pay__amount">
		<span class="bankwire-pay__amount-label">Ödenecek tutar</span>
		<span class="bankwire-pay__amount-value">{$orderTotal}</span>
		{if $orderPaymentDiscountLabel}
		<span class="bankwire-pay__amount-note">{$orderPaymentDiscountLabel|escape} uygulandı</span>
		{/if}
	</div>

	<div class="bankwire-pay__rows">
		{if $bankwireBank}
		<div class="bankwire-pay__row">
			<span class="bankwire-pay__label">Banka</span>
			<span class="bankwire-pay__value">{$bankwireBank|escape}</span>
		</div>
		{/if}

		{if $bankwireHolder}
		<div class="bankwire-pay__row">
			<span class="bankwire-pay__label">Hesap sahibi</span>
			<div class="bankwire-pay__value-wrap">
				<span class="bankwire-pay__value">{$bankwireHolder|escape}</span>
				<button type="button" class="bankwire-pay__copy" data-copy="{$bankwireHolder|escape}" data-copy-label="Ad kopyalandı">Kopyala</button>
			</div>
		</div>
		{/if}

		<div class="bankwire-pay__row">
			<span class="bankwire-pay__label">IBAN</span>
			<div class="bankwire-pay__value-wrap">
				<span class="bankwire-pay__value bankwire-pay__value--iban">{if $bankwireIbanDisplay}{$bankwireIbanDisplay|escape}{else}IBAN henüz girilmedi{/if}</span>
				{if $bankwireIbanCopy}
				<button type="button" class="bankwire-pay__copy" data-copy="{$bankwireIbanCopy|escape}" data-copy-label="IBAN kopyalandı">Kopyala</button>
				{/if}
			</div>
		</div>

		<div class="bankwire-pay__row">
			<span class="bankwire-pay__label">Açıklama</span>
			<div class="bankwire-pay__value-wrap">
				<span class="bankwire-pay__value">{$orderReference|escape}</span>
				<button type="button" class="bankwire-pay__copy" data-copy="{$orderReference|escape}" data-copy-label="Açıklama kopyalandı">Kopyala</button>
			</div>
		</div>
	</div>
</div>

{literal}
<style>
.bankwire-pay{
	border:1px solid #e5e7eb;
	border-radius:14px;
	overflow:hidden;
	background:#fff;
	box-shadow:0 8px 24px rgba(15,23,42,.06);
}
.bankwire-pay__head{
	padding:1.1rem 1.25rem .85rem;
	background:linear-gradient(180deg,#f8fafc,#fff);
	border-bottom:1px solid #eef2f7;
}
.bankwire-pay__badge{
	display:inline-block;
	font-size:.72rem;
	font-weight:700;
	letter-spacing:.04em;
	text-transform:uppercase;
	color:#0f766e;
	background:#ccfbf1;
	border-radius:999px;
	padding:.2rem .55rem;
	margin-bottom:.45rem;
}
.bankwire-pay__title{display:block;font-size:1.05rem;color:#0f172a}
.bankwire-pay__hint{margin-top:.35rem;font-size:.875rem;color:#64748b;line-height:1.45}
.bankwire-pay__amount{
	display:flex;
	flex-direction:column;
	gap:.15rem;
	padding:1rem 1.25rem;
	background:#0f172a;
	color:#fff;
}
.bankwire-pay__amount-label{font-size:.8rem;opacity:.75}
.bankwire-pay__amount-value{font-size:1.75rem;font-weight:700;letter-spacing:-.02em;line-height:1.2}
.bankwire-pay__amount-note{font-size:.8rem;color:#99f6e4}
.bankwire-pay__rows{padding:.35rem 0}
.bankwire-pay__row{
	display:flex;
	flex-direction:column;
	gap:.35rem;
	padding:.85rem 1.25rem;
	border-bottom:1px solid #f1f5f9;
}
.bankwire-pay__row:last-child{border-bottom:0}
.bankwire-pay__label{
	font-size:.75rem;
	font-weight:600;
	text-transform:uppercase;
	letter-spacing:.04em;
	color:#94a3b8;
}
.bankwire-pay__value-wrap{display:flex;align-items:center;justify-content:space-between;gap:.75rem}
.bankwire-pay__value{font-size:1rem;font-weight:600;color:#0f172a;word-break:break-word}
.bankwire-pay__value--iban{
	font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;
	letter-spacing:.04em;
	font-size:.95rem;
}
.bankwire-pay__copy{
	flex:0 0 auto;
	border:1px solid #cbd5e1;
	background:#fff;
	color:#0f172a;
	border-radius:8px;
	padding:.4rem .75rem;
	font-size:.8rem;
	font-weight:600;
	cursor:pointer;
}
.bankwire-pay__copy:hover{background:#f1f5f9;border-color:#94a3b8}
.bankwire-pay__copy.is-copied{background:#ecfdf5;border-color:#6ee7b7;color:#047857}
@media (max-width:575.98px){
	.bankwire-pay__amount-value{font-size:1.45rem}
	.bankwire-pay__value-wrap{align-items:flex-start}
}
</style>

<script>
(function () {
	function copyText(text) {
		if (navigator.clipboard && window.isSecureContext) {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			var area = document.createElement('textarea');
			area.value = text;
			area.setAttribute('readonly', '');
			area.style.position = 'fixed';
			area.style.left = '-9999px';
			document.body.appendChild(area);
			area.select();
			try {
				document.execCommand('copy');
				resolve();
			} catch (err) {
				reject(err);
			}
			document.body.removeChild(area);
		});
	}

	document.querySelectorAll('.bankwire-pay__copy').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var text = btn.getAttribute('data-copy') || '';
			var label = btn.getAttribute('data-copy-label') || 'Kopyalandı';
			if (!text) return;
			copyText(text).then(function () {
				var prev = btn.textContent;
				btn.textContent = label;
				btn.classList.add('is-copied');
				setTimeout(function () {
					btn.textContent = prev;
					btn.classList.remove('is-copied');
				}, 1600);
			});
		});
	});
})();
</script>
{/literal}
