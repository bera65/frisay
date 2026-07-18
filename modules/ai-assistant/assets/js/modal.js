(function () {
	'use strict';

	var MODAL_ID = 'aiAssistantResultModal';

	function ensureModal() {
		var existing = document.getElementById(MODAL_ID);

		if (existing) {
			return existing;
		}

		var el = document.createElement('div');
		el.className = 'modal fade ai-assist-modal';
		el.id = MODAL_ID;
		el.tabIndex = -1;
		el.setAttribute('aria-hidden', 'true');
		el.innerHTML =
			'<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">'
			+ '<div class="modal-content">'
			+ '<div class="modal-header">'
			+ '<h5 class="modal-title"></h5>'
			+ '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>'
			+ '</div>'
			+ '<div class="modal-body"></div>'
			+ '<div class="modal-footer"></div>'
			+ '</div>'
			+ '</div>';

		document.body.appendChild(el);

		return el;
	}

	function getInstance(el) {
		if (window.bootstrap && window.bootstrap.Modal) {
			return window.bootstrap.Modal.getOrCreateInstance(el);
		}

		return null;
	}

	window.AiAssistantModal = {
		open: function (opts) {
			opts = opts || {};
			var el = ensureModal();
			var titleEl = el.querySelector('.modal-title');
			var bodyEl = el.querySelector('.modal-body');
			var footerEl = el.querySelector('.modal-footer');

			if (titleEl) {
				titleEl.textContent = opts.title || 'Yapay Zeka';
			}

			if (bodyEl) {
				bodyEl.innerHTML = opts.body || '';
			}

			if (footerEl) {
				footerEl.innerHTML = opts.footer
					|| '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>';
			}

			var instance = getInstance(el);

			if (instance) {
				instance.show();
			}
		},

		loading: function (title, message) {
			this.open({
				title: title || 'Yapay zeka çalışıyor…',
				body: '<div class="ai-modal-loading text-center py-5">'
					+ '<div class="spinner-border text-dark" role="status" aria-hidden="true"></div>'
					+ '<p class="text-muted small mt-3 mb-0">' + (message || 'Lütfen bekleyin…') + '</p>'
					+ '</div>',
				footer: ''
			});
		},

		setBody: function (html) {
			var el = ensureModal();
			var bodyEl = el.querySelector('.modal-body');

			if (bodyEl) {
				bodyEl.innerHTML = html;
			}
		},

		setFooter: function (html) {
			var el = ensureModal();
			var footerEl = el.querySelector('.modal-footer');

			if (footerEl) {
				footerEl.innerHTML = html;
			}
		},

		setTitle: function (title) {
			var el = ensureModal();
			var titleEl = el.querySelector('.modal-title');

			if (titleEl) {
				titleEl.textContent = title;
			}
		},

		close: function () {
			var el = document.getElementById(MODAL_ID);

			if (!el) {
				return;
			}

			var instance = getInstance(el);

			if (instance) {
				instance.hide();
			}
		}
	};
})();
