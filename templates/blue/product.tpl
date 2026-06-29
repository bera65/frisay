<div class="panel boxShadow borderRadius571 mBottom20">
<div class="panelBody">
	<div class="row">
		<div class="col-lg-4">
			<div class="productLeftCloumn">
				{if $productLabel}
					<span class="product-label-badge">{$productLabel|escape}</span>
				{/if}
				<div class="product-gallery" data-image-count="{$images|@count}">
					<div class="product-gallery__main">
						{if $images|@count > 1}
						<button type="button" class="product-gallery__nav product-gallery__nav--prev" aria-label="{'Previous image'|translate}">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
						</button>
						{/if}
						<img loading="lazy" id="main-display" data-bs-toggle="modal" data-bs-target="#imageModal" class="img-responsives cursorPointer" src="{$imageUrl|escape}" alt="{$productName|escape}" />
						{if $images|@count > 1}
						<button type="button" class="product-gallery__nav product-gallery__nav--next" aria-label="{'Next image'|translate}">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
						</button>
						{/if}
					</div>
					{if $images|@count > 1}
					<div class="product-gallery__thumbs">
						{foreach $images as $img name=productGallery}
						<button type="button" class="product-gallery__thumb thumb-img{if $smarty.foreach.productGallery.first} active{/if}" data-image="{$img.url|escape}" data-index="{$smarty.foreach.productGallery.index}" aria-label="{$productName|escape}">
							<img loading="lazy" src="{$img.url|escape}" alt="{$productName|escape}">
						</button>
						{/foreach}
					</div>
					{/if}
				</div>
				<div class="imageButtons">
					<button class="btn like-button toggle-favorite{if $isFavorite} active{/if}" data-id="{$product.id_product}">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart-icon lucide-heart"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
					</button>
				</div>
			</div>
		</div>
		<div class="col-lg-5">
			<div class="productRightCloumn">
				<div class="mb-3">
					<h1 class="fs-4">{$productName|escape}</h1>
					{if $hooks.product_inf}{$hooks.product_inf nofilter}{/if}
				</div>
				<div class="d-flex align-items-center gap-3 my-4">
					{if $oldPrice > 0}<div class="discount-badge">%{Tools::getDiscount($oldPrice, $price)}</div>{/if}
					<div>
						{if $oldPrice > 0}<div class="old-price">{Tools::displayPrice($oldPrice)}</div>{/if}
						<div class="current-price">{Tools::displayPrice($price)}</div>
					</div>
				</div>
				<div class="productCenter">
					<div class="d-flex flex-wrap gap-2 mb-3">
						{if $inStock}
						<div class="qty-picker">
							<button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
							<input type="text" value="1" id="qty-input" class="qty-input" readonly data-max="{$stock}">
							<button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
						</div>
						<button class="btn btn-dark cart-button addtocart d-flex align-items-center justify-content-center gap-2" data-id="{$product.id_product}">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-basket-icon lucide-shopping-basket"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/></svg>
							{'Add To Cart'|translate}
						</button>
						{else}
						<button type="button" class="btn btn-secondary" disabled>{'Out Of Stock'|translate}</button>
						{/if}
					</div>
					<div class="productMiniDetail">
						<div><b>{'Brand'|translate}</b> : <a href="{$brandUrl|escape}" title="{$brandName|escape}">{$brandName|escape}</a></div>
						<div><b>{'Stock Code'|translate}</b> : {$stockCode}</div>
						<div><b>{'Cargo'|translate}</b> : {if $price >= $freeCargo}{'Free'|translate}{else}{Tools::displayPrice($cargoPrice)}{/if}</div>
						<div><b>{'Cargo Day'|translate}</b> : {$cargoDay} {'Day(s)'|translate}</div>
					</div>				
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="row otherProduct">
				<h6>{'Other Products'|translate}{if $brandName} — {$brandName|escape}{/if}</h6>
				{if $relatedProducts|@count}
					{foreach $relatedProducts as $rp}
					<a class="col-12" href="{$rp.url|escape}" title="{$rp.product_name|escape}">
						<div class="borderRadius571 bordergrey padding10 mBottom10">
							<div class="row">
								<div class="col-3">
									<img loading="lazy" class="img-responsive" src="{$rp.image_url|escape}" alt="{$rp.product_name|escape}" />
								</div>
								<div class="col-9">
									<p>{$rp.product_name|truncate:40:'...'|escape}</p>
									<p class="price">{$rp.price_formatted|escape}</p>
								</div>
							</div>
						</div>
					</a>
					{/foreach}
				{else}
					<p class="text-muted small col-12">{'No related products'|translate}</p>
				{/if}
			</div>
		</div>
	</div>
</div>
</div>
<ul class="nav nav-tabs justify-content-center" id="productTabs">
    <li class="nav-item" role="presentation">
        <button class="nav-link active"
                data-bs-toggle="tab"
                data-bs-target="#description"
                type="button">
            {'Description'|translate}
        </button>
    </li>
	{if $productVideoEmbed}
	<li class="nav-item" role="presentation">
        <button class="nav-link"
                data-bs-toggle="tab"
                data-bs-target="#video"
                type="button">
            {'Video'|translate}
        </button>
    </li>
	{/if}
	{if $hooks.product_tab}{$hooks.product_tab nofilter}{/if}
</ul>
<div class="tab-content border border-top-0 p-3 bg-white">

    <!-- Açıklama -->
    <div class="tab-pane fade show active" id="description">
        {if $description}
			{$description nofilter}
		{/if}
    </div>
	<div class="tab-pane" id="video">
        {if $productVideoEmbed}
			<div class="product-video ratio ratio-16x9 mt-3">
				<iframe src="{$productVideoEmbed|escape}" title="{$productName|escape} videosu" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
			</div>
		{/if}
    </div>
    {if $hooks.product_tab_content}{$hooks.product_tab_content nofilter}{/if}
</div>
{if $hooks.product}{$hooks.product nofilter}{/if}

<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-600 modal-dialog-centered">
    <div class="modal-content">
		<div class="modal-header">
        <h5 class="modal-title fs-5" id="imageModalLabel">{$productName|escape}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|translate}"></button>
      </div>
      <div class="modal-body">
        <img id="modal-display" src="{$imageUrl|escape}" class="imageFull" alt="{$productName|escape}" />
      </div>
    </div>
  </div>
</div>