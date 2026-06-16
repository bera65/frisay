{if $reviewCount > 0}
<div class="product-inf-rating d-flex align-items-center gap-2">
	<div class="review-stars review-stars--sm" style="--rating: {$averageRating}">
		<span class="review-stars-track" aria-hidden="true"></span>
		<span class="review-stars-fill" aria-hidden="true"></span>
	</div>
	<span class="small text-muted">{$averageRatingLabel} · {$reviewCount} değerlendirme</span>
</div>
{/if}
