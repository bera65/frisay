{if $flash}
<div class="alert alert-{$flashType|escape}">{$flash|escape}</div>
{/if}

<div class="admin-panel mb-3">
	<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
		<div>
			<h2 class="h5 mb-2">
				{if $thread.is_order_thread}
				Sipariş #{$thread.order_reference|escape}
				{else}
				{$thread.subject|escape}
				{/if}
			</h2>
			<p class="mb-1"><strong>Müşteri:</strong> {$thread.full_name|escape}</p>
			<p class="mb-1"><strong>E-posta:</strong> <a href="mailto:{$thread.email|escape}">{$thread.email|escape}</a></p>
			{if $thread.phone}<p class="mb-1"><strong>Telefon:</strong> {$thread.phone|escape}</p>{/if}
			{if $thread.is_order_thread}
			<p class="mb-0">
				<strong>Sipariş:</strong>
				<a href="{$adminUrl}order?id={$thread.id_order}">#{$thread.order_reference|escape}</a>
				· {$thread.message_count} müşteri mesajı · {$thread.reply_count} yanıt
			</p>
			{/if}
		</div>
		{if $thread.is_order_thread}
		<a href="{$adminUrl}order?id={$thread.id_order}" class="btn btn-outline-dark btn-sm">Siparişi aç</a>
		{/if}
	</div>

	<div class="contact-admin-thread">
		{foreach $thread.timeline as $item}
		<div class="contact-admin-thread__item contact-admin-thread__item--{$item.type|escape} mb-3">
			<div class="d-flex justify-content-between gap-2 small text-muted mb-1">
				<span>
					{if $item.type == 'customer'}
					<strong>Müşteri</strong>{if $thread.is_order_thread && $thread.message_count > 1} · Mesaj #{$item.id_message}{/if}
					{else}
					<strong>{$item.author|escape}</strong>
					{/if}
				</span>
				<span>{$item.date_formatted}</span>
			</div>
			<div class="contact-admin-thread__body" style="white-space:pre-wrap;">{$item.message|escape}</div>
		</div>
		{/foreach}
	</div>
</div>

<form method="post" class="admin-panel">
	<h3 class="fs-6 mb-3">Müşteriye yanıt yaz</h3>
	<input type="hidden" name="replyMessage" value="1">
	<input type="hidden" name="token" value="{$adminToken}">
	<input type="hidden" name="reply_to_message_id" value="{$thread.reply_to_message_id}">
	<div class="mb-3">
		<label class="form-label">Yanıt</label>
		<textarea name="reply" class="form-control" rows="5" required minlength="5" placeholder="Önceki mesajları okuyup yanıtınızı yazın..."></textarea>
	</div>
	<button type="submit" class="btn btn-dark">Yanıtı gönder</button>
	<p class="small text-muted mt-2 mb-0">Yanıt, son müşteri mesajına bağlanır ve müşteriye e-posta + bildirim olarak iletilir.</p>
</form>

<p class="mt-3"><a href="{$adminUrl}messages">&larr; Mesaj listesine dön</a></p>
