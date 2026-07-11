<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
	<p class="text-muted small mb-0">{if $unreadCount > 0}{$unreadCount} okunmamış bildirim{else}Tüm bildirimler okundu{/if}</p>
	{if $unreadCount > 0}
	<form method="post" class="mb-0">
		<input type="hidden" name="token" value="{$adminToken}">
		<button type="submit" name="markAllRead" value="1" class="btn btn-sm btn-outline-secondary">Tümünü okundu işaretle</button>
	</form>
	{/if}
</div>

<div class="ps-panel">
	<div class="ps-panel__body p-0">
		{if $notifications|@count}
		<div class="list-group list-group-flush">
			{foreach $notifications as $n}
			<div class="list-group-item{if !$n.is_read} list-group-item-warning{/if}">
				<div class="d-flex flex-wrap justify-content-between gap-2">
					<div class="flex-grow-1">
						<strong>{$n.title|escape}</strong>
						{if $n.message}<p class="small text-muted mb-1 mt-1">{$n.message|escape|nl2br}</p>{/if}
						<span class="small text-muted">{$n.date_formatted}</span>
					</div>
					<div class="d-flex flex-column gap-1 align-items-end">
						{if $n.link}
						<a href="{$n.link|escape}" class="btn btn-sm btn-outline-dark">Görüntüle</a>
						{/if}
						{if !$n.is_read}
						<form method="post" class="mb-0">
							<input type="hidden" name="token" value="{$adminToken}">
							<input type="hidden" name="id" value="{$n.id_notification}">
							<button type="submit" name="markRead" value="1" class="btn btn-sm btn-link">Okundu</button>
						</form>
						{/if}
					</div>
				</div>
			</div>
			{/foreach}
		</div>
		{else}
		<p class="text-muted p-4 mb-0">Bildirim bulunmuyor.</p>
		{/if}
	</div>
</div>
