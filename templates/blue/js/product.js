function updateQty(val) {
	const input = document.getElementById('qty-input');
	if (!input) return;

	const max = parseInt(input.dataset.max, 10) || 99;
	let current = parseInt(input.value, 10) || 1;
	const next = current + val;

	if (next >= 1 && next <= max) {
		input.value = next;
	}
}

(function () {
	var mainImg = document.getElementById('main-display');
	var modalImg = document.getElementById('modal-display');
	var thumbs = Array.prototype.slice.call(document.querySelectorAll('.product-gallery__thumb'));
	var prevBtn = document.querySelector('.product-gallery__nav--prev');
	var nextBtn = document.querySelector('.product-gallery__nav--next');
	var currentIndex = 0;

	function getImageUrls() {
		if (thumbs.length) {
			return thumbs.map(function (thumb) {
				return thumb.getAttribute('data-image') || '';
			}).filter(Boolean);
		}

		return mainImg && mainImg.src ? [mainImg.src] : [];
	}

	function setActiveIndex(index) {
		var urls = getImageUrls();
		if (!urls.length) {
			return;
		}

		if (index < 0) {
			index = urls.length - 1;
		}
		if (index >= urls.length) {
			index = 0;
		}

		currentIndex = index;

		if (mainImg && urls[index]) {
			mainImg.src = urls[index];
		}
		if (modalImg && urls[index]) {
			modalImg.src = urls[index];
		}

		thumbs.forEach(function (thumb, i) {
			thumb.classList.toggle('active', i === index);
		});
	}

	thumbs.forEach(function (thumb, index) {
		thumb.addEventListener('click', function () {
			setActiveIndex(index);
		});
	});

	if (prevBtn) {
		prevBtn.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			setActiveIndex(currentIndex - 1);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			setActiveIndex(currentIndex + 1);
		});
	}

	if (mainImg) {
		mainImg.addEventListener('click', function () {
			if (modalImg && mainImg.src) {
				modalImg.src = mainImg.src;
			}
		});
	}

	var imageModal = document.getElementById('imageModal');
	if (imageModal) {
		imageModal.addEventListener('show.bs.modal', function () {
			if (modalImg && mainImg && mainImg.src) {
				modalImg.src = mainImg.src;
			}
		});
	}
})();
