$(document).on('submit', '#footerNewsletterForm, .newsletter-tab-form', function (e) {
	e.preventDefault();

	var $form = $(this);
	var url = $form.data('api-url');
	var email = $.trim($form.find('[name="email"]').val());

	if (!url) {
		return;
	}

	$.ajax({
		url: url,
		method: 'POST',
		dataType: 'json',
		data: {
			email: email,
			token: typeof csrfToken !== 'undefined' ? csrfToken : ''
		}
	}).done(function (data) {
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
