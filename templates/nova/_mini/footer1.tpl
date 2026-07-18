<footer class="nova-footer" role="contentinfo">
	<div class="container custom-container">
		<div class="row g-4">
			<div class="col-lg-4 col-md-6">
				<a href="{$domain}" class="d-inline-block mb-3">
					<img src="{$domain}img/logoFooter.png" alt="{$siteName|escape}" height="56" loading="lazy">
				</a>
				<p class="small mb-3">{'Footer description'|translate}</p>
				<div class="d-flex gap-2 flex-wrap">
					{if $facebookLink}<a href="{$facebookLink|escape}" class="nova-header__action" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>{/if}
					{if $instagramLink}<a href="{$instagramLink|escape}" class="nova-header__action" target="_blank" rel="noopener" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="20" x="2" y="2" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg></a>{/if}
					{if $youtubeLink}<a href="{$youtubeLink|escape}" class="nova-header__action" target="_blank" rel="noopener" aria-label="Youtube"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg></a>{/if}
					{if $xLink}<a href="{$xLink|escape}" class="nova-header__action" target="_blank" rel="noopener" aria-label="X"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l11.5 16h4.5L8.5 4z"/><path d="M4 20 16.5 4"/></svg></a>{/if}
				</div>
			</div>
			<div class="col-lg-2 col-md-6 col-6">
				<h3 class="nova-footer__title">{'Corporate'|translate}</h3>
				{foreach $cmsFooterLinks as $cmsLink}
				<a href="{$cmsLink.url|escape}" class="nova-footer__link">{$cmsLink.title|escape}</a>
				{/foreach}
				<a href="{$domain}contact" class="nova-footer__link">{'Contact Us'|translate}</a>
				<a href="{$domain}truck" class="nova-footer__link">{'Order Traking'|translate}</a>
			</div>
			<div class="col-lg-3 col-md-6 col-6">
				<h3 class="nova-footer__title">{'Popular Categories'|translate}</h3>
				{foreach $menuCategories as $cat name=footerCats}
				{if $smarty.foreach.footerCats.iteration > 6}{break}{/if}
				<a href="{$domain}{$cat.category_link|escape}" class="nova-footer__link">{$cat.category_name|escape}</a>
				{/foreach}
			</div>
			<div class="col-lg-3 col-md-6">
				<h3 class="nova-footer__title">{'Subscribe to newsletter'|translate}</h3>
				<p class="small mb-2">{'Newsletter description'|translate}</p>
				<form id="footerNewsletterFormFooter" data-api-url="{$newsletterApiUrl|escape}" method="post" action="#">
					<div class="input-group">
						<input type="email" name="email" class="form-control form-control-sm" placeholder="{'Your Email'|translate}" required>
						<button class="btn btn-sm nova-btn nova-btn--primary" type="submit">{'Register'|translate}</button>
					</div>
				</form>
			</div>
		</div>
		<div class="nova-footer__bottom">
			<span>&copy; {$year} {$siteName|escape}. {'All rights reserved.'|translate}</span>
			<img src="{$img_dir}odemeLogo.png" alt="{'Payment logos'|translate}" height="20" loading="lazy">
		</div>
	</div>
</footer>
