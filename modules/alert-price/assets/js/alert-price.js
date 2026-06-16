$(document).on('submit', '.alert-price-form', function (e) {
	e.preventDefault();

	var $form = $(this);
	var url = $form.data('api-url');
	var idProduct = $form.data('product-id');
	var $messageBox = $('#alertPriceMessage-' + idProduct);
	var $submitBtn = $form.find('button[type="submit"]');
	var modalEl = document.getElementById('alertPriceModal-' + idProduct);

	if (!url || typeof csrfToken === 'undefined') {
		if ($messageBox.length) {
			$messageBox.removeClass('d-none alert-success').addClass('alert alert-danger');
			$messageBox.text('İstek gönderilemedi. Sayfayı yenileyip tekrar deneyin.');
		}
		return;
	}

	$submitBtn.prop('disabled', true);

	$.ajax({
		url: url,
		method: 'POST',
		dataType: 'json',
		data: {
			token: csrfToken,
			id_product: idProduct,
			email: $.trim($form.find('[name="email"]').val()),
			target_price: $form.find('[name="target_price"]').val()
		}
	}).done(function (data) {
		if (!$messageBox.length) {
			return;
		}

		$messageBox.removeClass('d-none alert-success alert-danger');

		if (data.success) {
			$messageBox.addClass('alert alert-success').text(data.message || 'Talebiniz alındı.');
			$form[0].reset();

			if (modalEl && typeof bootstrap !== 'undefined') {
				setTimeout(function () {
					var modal = bootstrap.Modal.getInstance(modalEl);
					if (modal) {
						modal.hide();
					}
				}, 2000);
			}
		} else {
			$messageBox.addClass('alert alert-danger').text(data.message || 'İşlem başarısız.');
		}
	}).fail(function (xhr) {
		if (!$messageBox.length) {
			return;
		}

		var message = 'Bir hata oluştu. Lütfen tekrar deneyin.';

		if (xhr.responseJSON && xhr.responseJSON.message) {
			message = xhr.responseJSON.message;
		} else if (xhr.status === 403) {
			message = 'Geçersiz istek. Sayfayı yenileyip tekrar deneyin.';
		}

		$messageBox.removeClass('d-none alert-success').addClass('alert alert-danger').text(message);
	}).always(function () {
		$submitBtn.prop('disabled', false);
	});
});
