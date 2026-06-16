<div class="alert-price-wrapper mt-3">
    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#alertPriceModal-{$id_product}">
        <i class="fa-regular fa-bell"></i> Fiyatı Düşünce Haber Ver
    </button>
</div>

<div class="modal fade" id="alertPriceModal-{$id_product}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Fiyat Alarmı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>{$product_name|escape}</strong><br>
                <small class="text-muted">Mevcut fiyat: {$current_price_formatted|escape}</small></p>

                <div id="alertPriceMessage-{$id_product}" class="alert d-none"></div>

                <form id="alertPriceForm-{$id_product}" class="alert-price-form" method="post" action="#"
                      data-api-url="{$api_url|escape}" data-product-id="{$id_product}">
                    <div class="mb-3">
                        <label class="form-label">E-posta adresiniz</label>
                        <input type="email" name="email" class="form-control" placeholder="ornek@email.com" value="{$user_email|escape}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef fiyat (TL)</label>
                        <input type="number" name="target_price" class="form-control" step="0.01" min="1" placeholder="Örn: 250.00" required>
                        <div class="form-text">Fiyat bu rakam veya altına düştüğünde size e-posta gönderilecektir.</div>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fa-regular fa-bell"></i> Bildirim Oluştur
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
