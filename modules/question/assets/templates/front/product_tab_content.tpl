<div class="tab-pane fade" id="question">
<section class="product-questions" id="productQuestions" data-product-id="{$id_product}">
	<h2 class="questions-title">Sorular ve Cevaplar</h2>

	{if $questions|@count}
	<div class="questions-list">
		{foreach $questions as $item}
		<article class="question-card">
			<div class="question-card-head">
				<span class="question-author">{$item.author_masked|escape}</span>
				<span class="question-date">{$item.date_formatted|escape}</span>
			</div>
			<div class="question-bubble question-bubble--ask">
				<span class="question-label">Soru</span>
				<p>{$item.question|escape}</p>
			</div>
			<div class="question-bubble question-bubble--answer">
				<span class="question-label">Cevap</span>
				<p>{$item.answer|escape}</p>
				{if $item.answer_formatted}
				<small class="question-answer-date">{$item.answer_formatted|escape}</small>
				{/if}
			</div>
		</article>
		{/foreach}
	</div>
	{else}
	<p class="questions-empty text-muted">Bu ürün için henüz cevaplanmış soru yok. İlk soruyu siz sorun.</p>
	{/if}

	<div class="question-form-card">
		<h3 class="question-form-title">Soru Sor</h3>

		{if $isLoggedIn}
		<p class="question-form-note mb-3">{$askerName|escape} olarak soru soruyorsunuz.</p>
		<form id="productQuestionForm" class="question-form" data-api-url="{$questionApiUrl|escape}">
			<input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="question-form-honeypot">

			<div class="mb-3">
				<label class="form-label">Sorunuz</label>
				<textarea name="question" class="form-control" rows="4" required minlength="10" maxlength="1000" placeholder="Ürün hakkında merak ettiğinizi yazın…"></textarea>
			</div>

			<button type="submit" class="btn btn-dark">Soruyu Gönder</button>
			<p class="question-form-note">Sorularınız yanıtlandıktan sonra burada yayınlanır.</p>
		</form>
		{else}
		<p class="mb-3">Soru sorabilmek için giriş yapmalı veya üye olmalısınız.</p>
		<div class="d-flex flex-wrap gap-2">
			<a href="{$domain}login" class="btn btn-dark">Giriş Yap</a>
			<a href="{$domain}register" class="btn btn-outline-secondary">Üye Ol</a>
		</div>
		{/if}
	</div>
</section>
</div>
