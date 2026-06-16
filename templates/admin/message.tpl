<div class="admin-panel">
	<div class="mb-3">
		<p class="mb-1"><strong>Gönderen:</strong> {$message.full_name|escape}</p>
		<p class="mb-1"><strong>E-posta:</strong> <a href="mailto:{$message.email|escape}">{$message.email|escape}</a></p>
		{if $message.phone}<p class="mb-1"><strong>Telefon:</strong> {$message.phone|escape}</p>{/if}
		<p class="mb-1"><strong>Konu:</strong> {$message.subject|escape}</p>
		<p class="mb-0"><strong>Tarih:</strong> {$message.date_formatted}</p>
	</div>
	<hr>
	<div class="cms-content" style="white-space:pre-wrap;">{$message.message|escape}</div>
</div>

<p class="mt-3"><a href="{$adminUrl}messages">&larr; Mesaj listesine dön</a></p>
