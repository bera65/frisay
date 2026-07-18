(function () {
	'use strict';

	var cfg = window.AiAssistantDashboard || {};
	var btn = document.getElementById('aiAnalyzeDashboardBtn');
	var statusEl = document.getElementById('aiDashStatus');

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
			.replace(/>/g, '&gt;');
	}

	function simpleMarkdown(md) {
		var text = escapeHtml(md || '');
		text = text.replace(/^### (.+)$/gm, '<h5 class="h6 mt-3">$1</h5>');
		text = text.replace(/^## (.+)$/gm, '<h4 class="h6 mt-3">$1</h4>');
		text = text.replace(/^# (.+)$/gm, '<h3 class="h6 mt-3">$1</h3>');
		text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
		text = text.replace(/^\s*[-*] (.+)$/gm, '<li>$1</li>');
		text = text.replace(/(?:<li>.*?<\/li>\s*)+/gs, function (block) {
			return '<ul class="mb-2 ps-3">' + block + '</ul>';
		});
		text = text.replace(/\n{2,}/g, '</p><p class="mb-2">');

		return '<div class="ai-modal-analysis">' + text + '</div>';
	}

	btn.addEventListener('click', function () {
		if (!cfg.configured) {
			window.location.href = cfg.settingsUrl || '#';
			return;
		}

		btn.disabled = true;
		setStatus('Analiz başlatıldı…', 'info');

		if (window.AiAssistantModal) {
			window.AiAssistantModal.loading(
				'Dashboard analiz ediliyor',
				'Satış verileri toplanıyor ve yapay zeka yorumluyor…'
			);
		}

		var body = new FormData();
		body.append('token', cfg.token || '');

		fetch(cfg.apiUrl, {
			method: 'POST',
			body: body,
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		})
			.then(function (res) { return res.json(); })
			.then(function (data) {
				if (!data || !data.success) {
					var err = (data && data.message) || 'Analiz başarısız';

					setStatus(err, 'error');

					if (window.AiAssistantModal) {
						window.AiAssistantModal.open({
							title: 'Analiz başarısız',
							body: '<div class="alert alert-danger mb-0">' + escapeHtml(err) + '</div>',
							footer: '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>'
						});
					}

					return;
				}

				var meta = (data.message || 'Analiz hazır') + (data.model ? ' · ' + data.model : '');
				setStatus('Analiz tamamlandı. Sonuçlar pencerede açıldı.', 'success');

				if (window.AiAssistantModal) {
					window.AiAssistantModal.open({
						title: 'Yapay zeka mağaza analizi',
						body: '<p class="small text-muted mb-3">' + escapeHtml(meta) + '</p>' + simpleMarkdown(data.analysis || ''),
						footer: '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kapat</button>'
					});
				}
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
