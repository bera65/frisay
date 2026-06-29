<div class="prime-container prime-page">
	<h1 class="prime-page__title">{'Complete Checkout'|translate}</h1>

{if $orderError}
<div class="alert alert-danger">{$orderError}</div>
{/if}

<div class="row g-4">
	<div class="col-lg-7">
		<form method="post" action="{$domain}checkout" class="checkout-form page-card" id="checkoutForm">
			<input type="hidden" name="placeOrder" value="1">
			<input type="hidden" name="token" value="{$token}">

			<h2 class="fs-6 mb-3">{'Delivery Information'|translate}</h2>

			{if $addresses|@count}
			<div class="saved-address-list mb-4">
				{foreach $addresses as $addr}
				<label class="address-option border rounded p-3 mb-2 d-block">
					<div class="d-flex gap-2">
						<input type="radio" name="id_address" value="{$addr.id_address}" class="checkout-address-radio mt-1"
							data-full-name="{$addr.full_name|escape}"
							data-phone="{$addr.phone|escape}"
							data-city="{$addr.city|escape}"
							data-district="{$addr.district|escape}"
							data-address-text="{$addr.address_text|escape}"
							data-company-name="{$addr.company_name|escape}"
							data-tax-office="{$addr.tax_office|escape}"
							data-tax-number="{$addr.tax_number|escape}"
							{if $selectedAddressId == $addr.id_address} checked{/if}>
						<span>
							<strong>{if $addr.label}{$addr.label|escape}{else}{'Address'|translate}{/if}</strong>
							{if $addr.is_default}<span class="badge bg-dark ms-1">{'Default'|translate}</span>{/if}
							<span class="d-block small text-muted mt-1">
								{$addr.full_name|escape} · {$addr.phone|escape}<br>
								{$addr.city|escape} / {$addr.district|escape} — {$addr.address_text|escape}
							</span>
						</span>
					</div>
				</label>
				{/foreach}
				<label class="address-option border rounded p-3 mb-2 d-block">
					<div class="d-flex gap-2">
						<input type="radio" name="id_address" value="0" class="checkout-address-radio mt-1"
							{if $selectedAddressId == 0} checked{/if}>
						<span><strong>{'Use new address'|translate}</strong></span>
					</div>
				</label>
			</div>
			{else}
			<input type="hidden" name="id_address" value="0">
			{/if}

			<div id="checkoutAddressFields">
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">{'Full Name'|translate}</label>
						<input type="text" name="customer_name" id="checkoutCustomerName" class="form-control checkout-field" value="{$formData.customer_name|escape}" required>
					</div>
					<div class="col-md-6">
						<label class="form-label">{'Phone'|translate}</label>
						<input type="tel" name="customer_phone" id="checkoutCustomerPhone" class="form-control phone-input checkout-field" value="{$formData.customer_phone|escape}" required>
					</div>
					<div class="col-md-6">
						<label class="form-label">{'City'|translate}</label>
						<input type="text" name="address_city" id="checkoutCity" class="form-control checkout-field" value="{$formData.address_city|escape}" placeholder="{'City placeholder'|translate}" required>
					</div>
					<div class="col-md-6">
						<label class="form-label">{'District'|translate}</label>
						<input type="text" name="address_district" id="checkoutDistrict" class="form-control checkout-field" value="{$formData.address_district|escape}" placeholder="{'District placeholder'|translate}" required>
					</div>
					<div class="col-12">
						<label class="form-label">{'Street Address'|translate}</label>
						<textarea name="address_text" id="checkoutAddressText" class="form-control checkout-field" rows="3" placeholder="{'Street placeholder'|translate}" required>{$formData.address_text|escape}</textarea>
					</div>
					<div class="col-12" id="saveAddressBlock">
						<div class="form-check mb-2">
							<input class="form-check-input" type="checkbox" name="save_address" id="saveAddressCheck" value="1">
							<label class="form-check-label" for="saveAddressCheck">{'Save this address'|translate}</label>
						</div>
						<div id="saveAddressExtra" class="d-none">
							<label class="form-label">{'Address Title'|translate}</label>
							<input type="text" name="address_label" class="form-control mb-2" placeholder="{'Address label placeholder'|translate}" value="{$formData.address_label|escape}">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="set_default_address" id="setDefaultAddressCheck" value="1">
								<label class="form-check-label" for="setDefaultAddressCheck">{'Set as default address'|translate}</label>
							</div>
						</div>
					</div>
					<div class="col-12">
						<label class="form-label">{'Order Note Optional'|translate}</label>
						<textarea name="note" class="form-control" rows="2" placeholder="{'Order note placeholder'|translate}">{$formData.note|escape}</textarea>
					</div>
				</div>
			</div>

			<h2 class="fs-6 mb-3 mt-4">{'Billing Information'|translate} <span class="text-muted fw-normal">({'Billing Information Optional'|translate})</span></h2>
			<p class="small text-muted mb-2">{'Billing invoice hint'|translate}</p>
			<div class="row g-3">
				<div class="col-12">
					<label class="form-label">{'Company Name'|translate}</label>
					<input type="text" name="company_name" class="form-control" value="{$formData.company_name|escape}" placeholder="{'Company placeholder'|translate}">
				</div>
				<div class="col-md-6">
					<label class="form-label">{'Tax Office'|translate}</label>
					<input type="text" name="tax_office" class="form-control" value="{$formData.tax_office|escape}" placeholder="{'Tax office placeholder'|translate}">
				</div>
				<div class="col-md-6">
					<label class="form-label">{'Tax ID'|translate}</label>
					<input type="text" name="tax_number" class="form-control" value="{$formData.tax_number|escape}" placeholder="{'Tax id placeholder'|translate}" maxlength="20" inputmode="numeric">
				</div>
			</div>

			<h2 class="fs-6 mb-3 mt-4">{'Payment Method'|translate}</h2>

			{if $hooks.order_payment}
			{$hooks.order_payment nofilter}
			{else}
			{* Hiç ödeme modülü kurulu değilse sabit seçenekler *}
			<div class="payment-option mb-2">
				<label class="d-flex gap-2 align-items-start border rounded p-3 w-100">
					<input type="radio" name="payment_method" value="bank_transfer"{if $formData.payment_method == 'bank_transfer'} checked{/if}>
					<span>
						<strong>{'Bank Transfer'|translate}</strong>
						<small class="d-block text-muted">{'Bank transfer hint'|translate}</small>
					</span>
				</label>
			</div>
			{if !$cartHasVirtual}
			<div class="payment-option mb-3">
				<label class="d-flex gap-2 align-items-start border rounded p-3 w-100">
					<input type="radio" name="payment_method" value="cash_on_delivery"{if $formData.payment_method == 'cash_on_delivery'} checked{/if}>
					<span>
						<strong>{'Cash on Delivery'|translate}</strong>
						<small class="d-block text-muted">{'Cash on delivery hint'|translate}</small>
					</span>
				</label>
			</div>
			{/if}
			{/if}
			<div class="form-check mb-3">
				<input class="form-check-input" type="checkbox" name="accept_terms" id="acceptTerms" value="1" required>
				<label class="form-check-label small" for="acceptTerms">
					<a href="{$domain}mesafeli-satis" target="_blank" rel="noopener">{'Distance Selling Agreement'|translate}</a> {'and'|translate}
					<a href="{$domain}gizlilik" target="_blank" rel="noopener">{'Privacy Policy'|translate}</a>. {'Accept terms'|translate}
				</label>
			</div>

			<button type="submit" class="btn btn-primary w-100">{'Confirm Order'|translate}</button>
		</form>
	</div>

	<div class="col-lg-5">
		<div class="checkout-summary page-card bg-light">
			<h2 class="fs-6 mb-3">{'Order Summary'|translate}</h2>

			{foreach $cart.items as $item}
			<div class="d-flex justify-content-between gap-2 mb-2 small">
				<span>{$item.product_name|escape} x {$item.qty}</span>
				<span>{$item.line_total_formatted}</span>
			</div>
			{/foreach}

			<div class="coupon-box mb-3">
				<label class="form-label small mb-1">{'Coupon Code'|translate}</label>
				<div class="input-group input-group-sm">
					<input type="text" id="couponCodeInput" class="form-control text-uppercase" placeholder="{'Coupon Code'|translate}" value="{$checkoutTotals.coupon_code|escape}">
					<button type="button" class="btn btn-dark" id="applyCouponBtn">{'Apply'|translate}</button>
				</div>
				{if $checkoutTotals.has_coupon}
				<div class="d-flex justify-content-between align-items-center mt-2 small">
					<span class="text-success">{'Coupon applied'|translate}: {$checkoutTotals.coupon_code|escape}</span>
					<button type="button" class="btn btn-link btn-sm p-0 text-danger" id="removeCouponBtn">{'Delete'|translate}</button>
				</div>
				{/if}
			</div>

			<hr>

			<div class="d-flex justify-content-between mb-2">
				<span>{'Subtotal'|translate}</span>
				<span id="checkoutSubtotal">{$checkoutTotals.subtotal_formatted}</span>
			</div>
			{if $checkoutTotals.discount > 0}
			<div class="d-flex justify-content-between mb-2 text-success" id="checkoutDiscountRow">
				<span>{'Discount'|translate}</span>
				<span id="checkoutDiscount">-{$checkoutTotals.discount_formatted}</span>
			</div>
			{else}
			<div class="d-flex justify-content-between mb-2 text-success d-none" id="checkoutDiscountRow">
				<span>{'Discount'|translate}</span>
				<span id="checkoutDiscount"></span>
			</div>
			{/if}
			{if $cartRequiresShipping}
			<div class="d-flex justify-content-between mb-2" id="checkoutShippingRow">
				<span>{'Cargo'|translate}</span>
				<span id="checkoutShipping">{if $checkoutTotals.shipping > 0}{$checkoutTotals.shipping_formatted}{else}{'Free'|translate}{/if}</span>
			</div>
			{if $checkoutTotals.shipping > 0}
			<p class="small text-muted mb-2" id="checkoutShippingNote">{Tools::displayPrice($checkoutTotals.free_shipping_min)} {'Free shipping orders over'|translate}</p>
			{/if}
			{/if}
			<div class="d-flex justify-content-between fw-bold fs-5 mt-3">
				<span>{'Total'|translate}</span>
				<span class="text-danger" id="checkoutTotal">{$checkoutTotals.total_formatted}</span>
			</div>
		</div>
	</div>
</div>
</div>
