(function () {
	var $picker = $('.review-star-picker');
	var $input = $picker.length ? $picker.closest('form').find('[name="rating"]') : $();

	function setRating(value) {
		if (!$picker.length) {
			return;
		}

		value = Math.max(1, Math.min(5, parseInt(value, 10) || 5));
		$picker.attr('data-rating', value);
		$input.val(value);
		$picker.find('.review-star-picker-btn').each(function () {
			var star = parseInt($(this).data('value'), 10);
			$(this).toggleClass('is-active', star <= value);
		});
	}

	if ($picker.length) {
		$picker.on('mouseenter', '.review-star-picker-btn', function () {
			var hover = parseInt($(this).data('value'), 10);
			$picker.find('.review-star-picker-btn').each(function () {
				var star = parseInt($(this).data('value'), 10);
				$(this).toggleClass('is-hover', star <= hover);
			});
		});

		$picker.on('mouseleave', function () {
			$picker.find('.review-star-picker-btn').removeClass('is-hover');
		});

		$picker.on('click', '.review-star-picker-btn', function () {
			setRating($(this).data('value'));
		});

		setRating($picker.attr('data-rating') || 5);
	}

	$(document).on('submit', '#productReviewForm', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $wrap = $('#productReviews');
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
				rating: $form.find('[name="rating"]').val(),
				title: $form.find('[name="title"]').val(),
				comment: $form.find('[name="comment"]').val(),
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
				if ($picker.length) {
					setRating(5);
				}
			}
		}).fail(function () {
			if (typeof showToast === 'function') {
				showToast('Sunucuya bağlanılamadı', 'danger');
			}
		});
	});
})();
