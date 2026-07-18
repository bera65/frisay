(function () {
	'use strict';

	var cfg = window.AiAssistantProduct || {};
	var btn = document.getElementById('aiImproveProductBtn');
	var statusEl = document.getElementById('aiProductStatus');
	var suggestions = null;

	if (!btn) {
		return;
	}

	function setStatus(msg, type) {
		if (!statusEl) {
			return;
		}

		statusEl.textContent = msg || '';
		statusEl.className = 'small mb-0' + (type === 'error' ? ' text-danger' : type === 'success' ? ' text-success' : ' text-muted');
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function formatBlock(label, value, extraClass) {
		var cls = 'ai-modal-field' + (extraClass ? ' ' + extraClass : '');
		return '<div class="' + cls + '">'
			+ '<div class="ai-modal-field__label">' + escapeHtml(label) + '</div>'
			+ '<div class="ai-modal-field__value">' + (value || '<span class="text-muted">—</span>') + '</div>'
			+ '</div>';
	}

	function getActiveLangCode() {
		var activePane = document.querySelector('.tab-pane.show.active[id^="product-pane-"]');

		if (activePane && activePane.id) {
			return activePane.id.replace('product-pane-', '');
		}

		var first = document.querySelector('.tab-pane[id^="product-pane-"]');

		return first && first.id ? first.id.replace('product-pane-', '') : '';
	}

	function field(name, lang) {
		if (lang) {
			return document.querySelector('[name="langs[' + lang + '][' + name + ']"]');
		}

		return document.querySelector('[name="' + name + '"]');
	}

	function readFields() {
		var lang = getActiveLangCode();

		function val(name) {
			var el = field(name, lang);

			return el ? el.value : '';
		}

		return {
			lang: lang,
			product_name: val('product_name'),
			short_description: val('short_description'),
			description: val('description'),
			meta_title: val('meta_title'),
			meta_description: val('meta_description')
		};
	}

	function setTinyOrInput(el, value) {
		if (!el) {
			return;
		}

		el.value = value;

		if (window.tinymce) {
			var ed = tinymce.get(el.id);

			if (ed) {
				ed.setContent(value || '');
			}
		}

		el.dispatchEvent(new Event('input', { bubbles: true }));
		el.dispatchEvent(new Event('change', { bubbles: true }));
	}

	function applySuggestions(data) {
		var lang = getActiveLangCode();

		if (!data) {
			return;
		}

		if (data.product_name) {
			setTinyOrInput(field('product_name', lang), data.product_name);
		}

		if (data.short_description) {
			setTinyOrInput(field('short_description', lang), data.short_description);
		}

		if (data.description) {
			setTinyOrInput(field('description', lang), data.description);
		}

		if (data.meta_title) {
			setTinyOrInput(field('meta_title', lang), data.meta_title);
		}

		if (data.meta_description) {
			setTinyOrInput(field('meta_description', lang), data.meta_description);
		}
	}

	function renderModalPreview(data, notes) {
		var html = formatBlock('Başlık', escapeHtml(data.product_name || ''));
		html += formatBlock('Kısa açıklama', escapeHtml(data.short_description || ''));
		html += formatBlock(
			'Uzun açıklama',
			'<div class="ai-modal-description">' + escapeHtml(data.description || '').replace(/\n/g, '<br>') + '</div>',
			'ai-modal-field--wide'
		);
		html += formatBlock('Meta başlık', escapeHtml(data.meta_title || ''));
		html += formatBlock('Meta açıklama', escapeHtml(data.meta_description || ''));

		if (notes) {
			html += '<div class="alert alert-light border small mb-0 mt-3">' + escapeHtml(notes) + '</div>';
		}

		return html;
	}

	function openResultModal(data, notes, message) {
		if (!window.AiAssistantModal) {
			return;
		}

		var footer = ''
			+ '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>'
			+ '<button type="button" class="btn btn-dark btn-sm" id="aiModalApplyBtn">Önerileri forma yaz</button>';

		window.AiAssistantModal.open({
			title: 'AI ürün metni önerileri',
			body: renderModalPreview(data || {}, notes || '')
				+ (message ? '<p class="small text-success mb-0 mt-2">' + escapeHtml(message) + '</p>' : ''),
			footer: footer
		});

		var applyBtn = document.getElementById('aiModalApplyBtn');

		if (applyBtn) {
			applyBtn.addEventListener('click', function () {
				applySuggestions(suggestions);
				setStatus('Öneriler forma yazıldı. Kaydetmeyi unutmayın.', 'success');
				window.AiAssistantModal.close();
			});
		}
	}

	btn.addEventListener('click', function () {
		if (!cfg.configured) {
			window.location.href = cfg.settingsUrl || '#';
			return;
		}

		var current = readFields();
		btn.disabled = true;
		setStatus('Yapay zeka çalışıyor…', 'info');

		if (window.AiAssistantModal) {
			window.AiAssistantModal.loading('Ürün metinleri iyileştiriliyor', 'Başlık, açıklama ve SEO alanları hazırlanıyor…');
		}

		var body = new FormData();
		body.append('token', cfg.token || '');
		body.append('tone', cfg.tone || 'professional');
		body.append('product_name', current.product_name);
		body.append('short_description', current.short_description);
		body.append('description', current.description);
		body.append('meta_title', current.meta_title);
		body.append('meta_description', current.meta_description);

		fetch(cfg.apiUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data || !data.success) {
					var err = (data && data.message) || 'İstek başarısız';

					setStatus(err, 'error');

					if (window.AiAssistantModal) {
						window.AiAssistantModal.open({
							title: 'Hata',
							body: '<div class="alert alert-danger mb-0">' + escapeHtml(err) + '</div>',
							footer: '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>'
						});
					}

					return;
				}

				suggestions = data.suggestions || null;
				setStatus(data.message || 'Öneriler hazır.', 'success');
				openResultModal(suggestions || {}, data.notes || '', data.message || '');
			})
			.catch(function () {
				setStatus('Bağlantı hatası', 'error');

				if (window.AiAssistantModal) {
					window.AiAssistantModal.open({
						title: 'Bağlantı hatası',
						body: '<div class="alert alert-danger mb-0">Sunucuya ulaşılamadı. Tekrar deneyin.</div>',
						footer: '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>'
					});
				}
			})
			.finally(function () {
				btn.disabled = false;
			});
	});
})();
