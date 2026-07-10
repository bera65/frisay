<div class="discount-timer-banner" data-discount-timer data-ends="{$ends_ts}" data-position="{$position|escape}" aria-live="polite">
	<div class="discount-timer-banner__inner">
		<div class="discount-timer-banner__text">
			<div class="discount-timer-banner__title">
				<svg class="discount-timer-banner__icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
				<span>{$title|escape}</span>
				{if $discount_pct > 0}<span class="discount-timer-banner__pct">-%{$discount_pct}</span>{/if}
			</div>
			{if $subtitle}<div class="discount-timer-banner__subtitle">{$subtitle|escape}</div>{/if}
		</div>
		<div class="discount-timer-banner__clock">
			<span class="discount-timer-banner__clock-label">Kalan Süre</span>
			<div class="discount-timer-banner__digits">
				<span class="discount-timer-digit" data-part="hours">00</span>
				<span class="discount-timer-sep">:</span>
				<span class="discount-timer-digit" data-part="minutes">00</span>
				<span class="discount-timer-sep">:</span>
				<span class="discount-timer-digit" data-part="seconds">00</span>
			</div>
		</div>
	</div>
</div>
