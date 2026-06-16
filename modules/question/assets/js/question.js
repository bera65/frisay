(function () {
	$(document).on('submit', '#productQuestionForm', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $wrap = $('#productQuestions');
		var url = $form.data('api-url');
		var idProduct = $wrap.data('product-id');

		if (!url || !idProduct) {
			return;
		}

		$.ajax({
			url: url,
			method: 'POST',
			dataType: 'json',
			data: {
				id_product: idProduct,
				question: $form.find('[name="question"]').val(),
				website: $form.find('[name="website"]').val() || '',
				token: typeof csrfToken !== 'undefined' ? csrfToken : ''
			}
		}).done(function (data) {
			if (data.login_required) {
				if (typeof showToast === 'function') {
					showToast(data.message || 'Giriş yapmalısınız', 'danger');
				}
				if (typeof domain !== 'undefined') {
					window.location.href = domain + 'login';
				}
				return;
			}

			if (typeof showToast === 'function') {
				showToast(data.message || 'İşlem tamamlandı', data.success ? 'success' : 'danger');
			}

			if (data.success) {
				$form[0].reset();
			}
		}).fail(function () {
			if (typeof showToast === 'function') {
				showToast('Sunucuya bağlanılamadı', 'danger');
			}
		});
	});
})();
