<div class="discount-timer-admin-fields row g-2" id="discountTimerAdminFields">
	<div class="col-md-6">
		<label class="form-label small mb-1" for="discountTimerStart">İndirim başlangıç</label>
		<input type="datetime-local" id="discountTimerStart" name="discount_timer_start" class="form-control form-control-sm" value="{$starts_at_input|escape}">
	</div>
	<div class="col-md-6">
		<label class="form-label small mb-1" for="discountTimerEnd">İndirim bitiş</label>
		<input type="datetime-local" id="discountTimerEnd" name="discount_timer_end" class="form-control form-control-sm" value="{$ends_at_input|escape}">
	</div>
	<div class="col-12">
		<div class="form-text">İndirim yalnızca bu tarihler arasında mağazada görünür. Bitişte eski fiyat otomatik normale döner.</div>
	</div>
</div>
{literal}
<script>
(function () {
	var fields = document.getElementById('discountTimerAdminFields');
	if (!fields) return;
	var priceInput = document.getElementById('productOldPrice');
	if (!priceInput) return;
	var row = priceInput.closest('.row');
	if (!row) return;
	row.appendChild(fields);
})();
</script>
{/literal}
