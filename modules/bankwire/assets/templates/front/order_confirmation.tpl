<hr>
<p class="mb-1 fw-semibold">Havale Bilgileri</p>
<p class="small text-muted mb-0">
	{if $bankwireBank}{$bankwireBank|escape}<br>{/if}
	{if $bankwireHolder}{$bankwireHolder|escape} — {/if}{if $bankwireIban}{$bankwireIban|escape}{else}IBAN bilgisi henüz girilmedi{/if}<br>
	Tutar: <strong>{$orderTotal}</strong><br>
	Açıklama: <strong>{$orderReference|escape}</strong>
</p>
