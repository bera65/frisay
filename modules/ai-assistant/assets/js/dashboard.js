(function () {
	'use strict';

	var cfg = window.AiAssistantDashboard || {};
	var btn = document.getElementById('aiAnalyzeDashboardBtn');
	var statusEl = document.getElementById('aiDashStatus');
	var resultEl = document.getElementById('aiDashResult');

	if (!btn) {
		return;
	}

	function setStatus(msg, type) {
		if (!statusEl) {
			return;
		}
		statusEl.textContent = msg || '';
		statusEl.className = 'small mb-2' + (type === 'error' ? ' text-danger' : type === 'success' ? ' text-success' : ' text-muted');
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
		return '<p class="mb-2">' + text + '</p>';
	}

	btn.addEventListener('click', function () {
		if (!cfg.configured) {
			window.location.href = cfg.settingsUrl || '#';
			return;
		}

		btn.disabled = true;
		setStatus('Satış verileri toplanıyor ve analiz ediliyor…', 'info');
		if (resultEl) {
			resultEl.innerHTML = '';
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
					setStatus((data && data.message) || 'Analiz başarısız', 'error');
					return;
				}
				setStatus((data.message || 'Analiz hazır') + (data.model ? ' · ' + data.model : ''), 'success');
				if (resultEl) {
					resultEl.innerHTML = simpleMarkdown(data.analysis || '');
				}
			})
			.catch(function () {
				setStatus('Bağlantı hatası', 'error');
			})
			.finally(function () {
				btn.disabled = false;
			});
	});
})();
