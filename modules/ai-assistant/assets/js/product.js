(function () {
	'use strict';

	var cfg = window.AiAssistantProduct || {};
	var btn = document.getElementById('aiImproveProductBtn');
	var applyBtn = document.getElementById('aiApplySuggestionsBtn');
	var statusEl = document.getElementById('aiProductStatus');
	var previewEl = document.getElementById('aiProductPreview');
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

	function renderPreview(data, notes) {
		if (!previewEl) {
			return;
		}
		var html = '<div class="border rounded p-2 bg-light">';
		html += '<div class="mb-1"><strong>Başlık:</strong> ' + escapeHtml(data.product_name || '—') + '</div>';
		html += '<div class="mb-1"><strong>Kısa:</strong> ' + escapeHtml(data.short_description || '—') + '</div>';
		html += '<div class="mb-1"><strong>Meta başlık:</strong> ' + escapeHtml(data.meta_title || '—') + '</div>';
		html += '<div class="mb-1"><strong>Meta açıklama:</strong> ' + escapeHtml(data.meta_description || '—') + '</div>';
		if (notes) {
			html += '<div class="text-muted mt-1">' + escapeHtml(notes) + '</div>';
		}
		html += '</div>';
		previewEl.style.display = '';
		previewEl.innerHTML = html;
	}

	btn.addEventListener('click', function () {
		if (!cfg.configured) {
			window.location.href = cfg.settingsUrl || '#';
			return;
		}

		var current = readFields();
		btn.disabled = true;
		if (applyBtn) {
			applyBtn.disabled = true;
		}
		setStatus('Yapay zeka çalışıyor…', 'info');

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
					setStatus((data && data.message) || 'İstek başarısız', 'error');
					return;
				}
				suggestions = data.suggestions || null;
				renderPreview(suggestions || {}, data.notes || '');
				if (applyBtn) {
					applyBtn.disabled = !suggestions;
				}
				setStatus(data.message || 'Öneriler hazır. Forma yazmak için butona basın.', 'success');
			})
			.catch(function () {
				setStatus('Bağlantı hatası', 'error');
			})
			.finally(function () {
				btn.disabled = false;
			});
	});

	if (applyBtn) {
		applyBtn.addEventListener('click', function () {
			if (!suggestions) {
				return;
			}
			applySuggestions(suggestions);
			setStatus('Öneriler forma yazıldı. Kaydetmeyi unutmayın.', 'success');
		});
	}
})();
