<div class="tab-pane fade" id="reviews">
<section class="product-reviews" id="productReviews" data-product-id="{$id_product}">
	<div class="reviews-layout">
		<div class="reviews-summary-panel">
			{if $reviewCount > 0}
			<div class="reviews-score-block">
				<div class="reviews-score-value">{$averageRatingLabel}</div>
				<div class="reviews-score-label">YILDIZ</div>
				<div class="review-stars review-stars--lg" style="--rating: {$averageRating}">
					<span class="review-stars-track" aria-hidden="true"></span>
					<span class="review-stars-fill" aria-hidden="true"></span>
				</div>
				<div class="reviews-score-count">{$reviewCount} Değerlendirme</div>
			</div>

			<div class="reviews-bars">
				{foreach $ratingBars as $bar}
				<div class="reviews-bar-row">
					<span class="reviews-bar-star">{$bar.star}</span>
					<div class="reviews-bar-track">
						<div class="reviews-bar-fill" style="width: {$bar.percent}%"></div>
					</div>
				</div>
				{/foreach}
			</div>
			{else}
			<div class="reviews-empty-summary">
				<div class="reviews-score-value reviews-score-value--muted">—</div>
				<p class="mb-0 small text-muted">Henüz değerlendirme yok.<br>İlk yorumu siz yazın.</p>
			</div>
			{/if}
		</div>

		<div class="reviews-main-panel">
			<h2 class="reviews-title">Müşteri Yorumları</h2>

			{if $reviews|@count}
			<div class="reviews-list">
				{foreach $reviews as $review}
				<article class="review-card">
					<div class="review-avatar" aria-hidden="true">
						<span>{$review.author_initials|escape}</span>
					</div>
					<div class="review-content">
						<div class="review-meta">
							<div class="review-stars review-stars--sm" style="--rating: {$review.rating}">
								<span class="review-stars-track" aria-hidden="true"></span>
								<span class="review-stars-fill" aria-hidden="true"></span>
							</div>
							<span class="review-date">{$review.date_formatted|escape}</span>
							<span class="review-sep">|</span>
							<span class="review-author">{$review.author_masked|escape}</span>
						</div>
						<div class="review-bubble">
							{if $review.title}<div class="review-bubble-title">{$review.title|escape}</div>{/if}
							<p class="review-bubble-text">{$review.comment|escape}</p>
						</div>
					</div>
				</article>
				{/foreach}
			</div>
			{/if}

			<div class="review-form-card">
				<h3 class="review-form-title">Değerlendirme Yap</h3>

				{if $isLoggedIn}
				<p class="review-form-note mb-3">{$reviewerName|escape} olarak yorum yapıyorsunuz.</p>
				<form id="productReviewForm" class="review-form" data-api-url="{$reviewApiUrl|escape}">
					<input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="review-form-honeypot">

					<div class="review-form-stars-wrap mb-3">
						<label class="form-label d-block mb-2">Puanınız</label>
						<div class="review-star-picker" data-rating="5">
							{section name=picker loop=5}
							<button type="button" class="review-star-picker-btn is-active" data-value="{$smarty.section.picker.iteration}" aria-label="{$smarty.section.picker.iteration} yıldız">
								<svg viewBox="0 0 24 24" width="28" height="28"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
							</button>
							{/section}
						</div>
						<input type="hidden" name="rating" value="5">
					</div>

					<div class="mb-3">
						<label class="form-label">Başlık <span class="text-muted fw-normal">(isteğe bağlı)</span></label>
						<input type="text" name="title" class="form-control" maxlength="255" placeholder="Kısa başlık">
					</div>

					<div class="mb-3">
						<label class="form-label">Yorumunuz</label>
						<textarea name="comment" class="form-control" rows="4" required minlength="10" maxlength="2000" placeholder="Ürün hakkındaki deneyiminizi paylaşın…"></textarea>
					</div>

					<button type="submit" class="btn btn-dark">Yorumu Gönder</button>
					<p class="review-form-note">Yorumlar moderasyon sonrası yayınlanır. Her ürün için bir yorum yazabilirsiniz.</p>
				</form>
				{else}
				<p class="mb-3">Yorum yazabilmek için giriş yapmalı veya üye olmalısınız.</p>
				<div class="d-flex flex-wrap gap-2">
					<a href="{$domain}login" class="btn btn-dark">Giriş Yap</a>
					<a href="{$domain}register" class="btn btn-outline-secondary">Üye Ol</a>
				</div>
				{/if}
			</div>
		</div>
	</div>
</section>
</div>