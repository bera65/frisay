(function () {
	function onlyDigits(value) {
		return (value || '').replace(/\D/g, '');
	}

	function formatCardNumber(value) {
		var digits = onlyDigits(value).slice(0, 16);
		return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
	}

	function bindCardNumber(input) {
		if (!input) {
			return;
		}

		var sync = function () {
			input.value = formatCardNumber(input.value);
		};

		sync();
		input.addEventListener('input', sync);
		input.addEventListener('blur', sync);
		input.addEventListener('paste', function () {
			setTimeout(sync, 0);
		});
	}

	function bindCvv(input) {
		if (!input) {
			return;
		}

		var sync = function () {
			input.value = onlyDigits(input.value).slice(0, 3);
		};

		sync();
		input.addEventListener('input', sync);
		input.addEventListener('blur', sync);
	}

	function init() {
		bindCardNumber(document.getElementById('parampos-card-number'));
		bindCvv(document.getElementById('parampos-cvv'));
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
