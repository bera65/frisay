/**
 * Shared admin media library picker (single/multi).
 * Usage: FShopMediaPicker.open({ multi: false, onSelect: function (items) {} })
 * Buttons: <button type="button" data-media-pick data-media-target="#inputId">
 */
(function () {
	'use strict';

	var modalEl = document.getElementById('adminMediaLibraryModal');
	if (!modalEl) {
		window.FShopMediaPicker = {
			open: function () {
				return false;
			},
			available: false
		};
		return;
	}

	var mediaApiUrl = modalEl.getAttribute('data-media-api') || '';
	var token = modalEl.getAttribute('data-token') || '';
	var gridEl = modalEl.querySelector('[data-ml-grid]');
	var crumbEl = modalEl.querySelector('[data-ml-crumbs]');
	var filterEl = modalEl.querySelector('[data-ml-filter]');
	var metaEl = modalEl.querySelector('[data-ml-meta]');
	var uploadInput = modalEl.querySelector('[data-ml-upload]');
	var uploadBtn = modalEl.querySelector('[data-ml-upload-btn]');
	var mkdirBtn = modalEl.querySelector('[data-ml-mkdir]');
	var refreshBtn = modalEl.querySelector('[data-ml-refresh]');
	var confirmBtn = modalEl.querySelector('[data-ml-confirm]');
	var statusModalEl = modalEl.querySelector('[data-ml-status]');
	var confirmLabelDefault = confirmBtn ? (confirmBtn.textContent || 'Seç') : 'Seç';

	var currentPath = '';
	var currentItems = [];
	var selected = {};
	var canUpload = false;
	var canMkdir = false;
	var busy = false;
	var options = { multi: true, onSelect: null };

	function setModalStatus(message, type) {
		if (!statusModalEl) {
			return;
		}
		statusModalEl.textContent = message || '';
		statusModalEl.className = 'small mb-0' + (type === 'error' ? ' text-danger' : type === 'success' ? ' text-success' : ' text-muted');
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function selectedPaths() {
		return Object.keys(selected);
	}

	function selectedItems() {
		return selectedPaths().map(function (path) {
			return selected[path];
		}).filter(Boolean);
	}

	function updateMeta() {
		var count = selectedPaths().length;
		if (metaEl) {
			metaEl.textContent = count
				? count + ' görsel seçildi'
				: 'Görsel seçin veya yeni yükleyin';
		}
		if (confirmBtn) {
			confirmBtn.disabled = count === 0 || busy;
			confirmBtn.textContent = options.confirmLabel || confirmLabelDefault;
		}
		if (uploadBtn) {
			uploadBtn.disabled = !canUpload || busy;
		}
		if (mkdirBtn) {
			mkdirBtn.disabled = !canMkdir || busy;
		}
	}

	function renderCrumbs(crumbs) {
		if (!crumbEl) {
			return;
		}
		var html = '';
		(crumbs || []).forEach(function (crumb, idx) {
			if (idx > 0) {
				html += '<span>/</span>';
			}
			html += '<button type="button" data-ml-path="' + escapeHtml(crumb.path || '') + '">' + escapeHtml(crumb.label) + '</button>';
		});
		crumbEl.innerHTML = html;
	}

	function filteredItems() {
		var q = filterEl ? String(filterEl.value || '').toLocaleLowerCase('tr-TR').trim() : '';
		if (!q) {
			return currentItems;
		}
		return currentItems.filter(function (item) {
			return String(item.name || '').toLocaleLowerCase('tr-TR').indexOf(q) !== -1;
		});
	}

	function renderGrid() {
		if (!gridEl) {
			return;
		}

		var items = filteredItems();
		if (!items.length) {
			gridEl.innerHTML = '<div class="ml-empty">Bu klasörde görsel yok.</div>';
			updateMeta();
			return;
		}

		var html = '';
		items.forEach(function (item) {
			if (item.type === 'dir') {
				html += ''
					+ '<div class="ml-item is-dir" data-type="dir" data-path="' + escapeHtml(item.path) + '">'
					+ '<div class="ml-thumb"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9l-.81-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg></div>'
					+ '<span class="ml-name" title="' + escapeHtml(item.name) + '">' + escapeHtml(item.name) + '</span>'
					+ '</div>';
				return;
			}

			var isSel = !!selected[item.path];
			html += ''
				+ '<div class="ml-item' + (isSel ? ' is-selected' : '') + '" data-type="file" data-path="' + escapeHtml(item.path) + '" data-url="' + escapeHtml(item.url || '') + '">'
				+ '<span class="ml-check"></span>'
				+ '<div class="ml-thumb"><img src="' + escapeHtml(item.url) + '" alt="" loading="lazy"></div>'
				+ '<span class="ml-name" title="' + escapeHtml(item.name) + '">' + escapeHtml(item.name) + '</span>'
				+ '</div>';
		});
		gridEl.innerHTML = html;
		updateMeta();
	}

	function api(action, extra) {
		extra = extra || {};
		var isForm = extra.formData instanceof FormData;
		var url = mediaApiUrl + (mediaApiUrl.indexOf('?') >= 0 ? '&' : '?') + 'action=' + encodeURIComponent(action);

		var fetchOpts = {
			method: isForm || extra.method === 'POST' ? 'POST' : 'GET',
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' }
		};

		if (isForm) {
			extra.formData.append('token', token);
			if (!extra.formData.has('action')) {
				extra.formData.append('action', action);
			}
			fetchOpts.body = extra.formData;
			url = mediaApiUrl;
		} else if (fetchOpts.method === 'POST') {
			var body = new FormData();
			body.append('token', token);
			body.append('action', action);
			Object.keys(extra.fields || {}).forEach(function (key) {
				body.append(key, extra.fields[key]);
			});
			fetchOpts.body = body;
			url = mediaApiUrl;
		} else {
			url += '&token=' + encodeURIComponent(token);
			if (extra.path !== undefined) {
				url += '&path=' + encodeURIComponent(extra.path);
			}
		}

		return fetch(url, fetchOpts).then(function (res) {
			return res.text().then(function (text) {
				var data = null;
				try {
					data = text ? JSON.parse(text) : null;
				} catch (err) {
					throw new Error('Sunucu geçersiz yanıt döndürdü');
				}
				if (!res.ok && (!data || data.message === undefined)) {
					throw new Error('İstek başarısız (' + res.status + ')');
				}
				return data || { success: false, message: 'Boş yanıt' };
			});
		});
	}

	function loadPath(path) {
		if (busy) {
			return;
		}
		busy = true;
		currentPath = path || '';
		if (gridEl) {
			gridEl.innerHTML = '<div class="ml-loading">Yükleniyor…</div>';
		}
		setModalStatus('Klasör okunuyor…', 'info');

		api('list', { path: currentPath })
			.then(function (data) {
				if (!data || !data.success) {
					setModalStatus((data && data.message) || 'Liste alınamadı', 'error');
					currentItems = [];
					renderGrid();
					return;
				}
				currentPath = data.path || '';
				currentItems = data.items || [];
				canUpload = !!data.can_upload;
				canMkdir = !!data.can_mkdir;
				renderCrumbs(data.breadcrumbs || []);
				renderGrid();
				setModalStatus(canUpload ? 'Bu klasöre yükleme yapılabilir.' : 'Görüntüleme modu — yükleme için media klasörüne gidin.', 'info');
			})
			.catch(function (err) {
				setModalStatus((err && err.message) || 'Bağlantı hatası', 'error');
			})
			.finally(function () {
				busy = false;
				updateMeta();
			});
	}

	function closeModal() {
		if (window.bootstrap && bootstrap.Modal) {
			var instance = bootstrap.Modal.getInstance(modalEl);
			if (instance) {
				instance.hide();
			}
		} else {
			modalEl.classList.remove('show');
			modalEl.style.display = 'none';
		}
	}

	function confirmSelection() {
		var items = selectedItems();
		if (!items.length) {
			return;
		}
		var cb = options.onSelect;
		closeModal();
		if (typeof cb === 'function') {
			cb(items);
		}
	}

	function open(opts) {
		opts = opts || {};
		options = {
			multi: opts.multi !== false,
			onSelect: typeof opts.onSelect === 'function' ? opts.onSelect : null,
			confirmLabel: opts.confirmLabel || confirmLabelDefault,
			startPath: opts.startPath || 'media'
		};
		selected = {};
		if (filterEl) {
			filterEl.value = '';
		}
		if (window.bootstrap && bootstrap.Modal) {
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		} else {
			modalEl.classList.add('show');
			modalEl.style.display = 'block';
		}
		loadPath(options.startPath || 'media');
		updateMeta();
		return true;
	}

	if (gridEl) {
		gridEl.addEventListener('click', function (e) {
			var item = e.target.closest('.ml-item');
			if (!item) {
				return;
			}
			var type = item.getAttribute('data-type');
			var path = item.getAttribute('data-path') || '';
			if (type === 'dir') {
				loadPath(path);
				return;
			}
			if (!options.multi) {
				selected = {};
			}
			if (selected[path]) {
				delete selected[path];
			} else {
				selected[path] = {
					path: path,
					url: item.getAttribute('data-url') || '',
					name: (item.querySelector('.ml-name') || {}).textContent || ''
				};
			}
			renderGrid();
		});

		gridEl.addEventListener('dblclick', function (e) {
			var item = e.target.closest('.ml-item[data-type="file"]');
			if (!item || options.multi) {
				return;
			}
			var path = item.getAttribute('data-path') || '';
			selected = {};
			selected[path] = {
				path: path,
				url: item.getAttribute('data-url') || '',
				name: (item.querySelector('.ml-name') || {}).textContent || ''
			};
			confirmSelection();
		});
	}

	if (crumbEl) {
		crumbEl.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-ml-path]');
			if (btn) {
				loadPath(btn.getAttribute('data-ml-path') || '');
			}
		});
	}

	if (filterEl) {
		filterEl.addEventListener('input', renderGrid);
	}

	if (refreshBtn) {
		refreshBtn.addEventListener('click', function () {
			loadPath(currentPath);
		});
	}

	if (confirmBtn) {
		confirmBtn.addEventListener('click', confirmSelection);
	}

	if (uploadBtn && uploadInput) {
		uploadBtn.addEventListener('click', function () {
			if (!canUpload || busy) {
				return;
			}
			uploadInput.click();
		});
		uploadInput.addEventListener('change', function () {
			if (!uploadInput.files || !uploadInput.files.length || busy) {
				return;
			}
			var fd = new FormData();
			fd.append('path', currentPath || 'media');
			Array.prototype.forEach.call(uploadInput.files, function (file) {
				fd.append('files[]', file);
			});
			busy = true;
			updateMeta();
			setModalStatus('Yükleniyor…', 'info');
			api('upload', { formData: fd })
				.then(function (data) {
					if (!data || !data.success) {
						setModalStatus((data && data.message) || 'Yükleme başarısız', 'error');
						return;
					}
					currentPath = data.path || currentPath;
					currentItems = data.items || [];
					canUpload = !!data.can_upload;
					canMkdir = !!data.can_mkdir;
					renderCrumbs(data.breadcrumbs || []);
					renderGrid();
					setModalStatus(data.message || 'Yüklendi', 'success');
				})
				.catch(function (err) {
					setModalStatus((err && err.message) || 'Yükleme hatası', 'error');
				})
				.finally(function () {
					busy = false;
					uploadInput.value = '';
					updateMeta();
				});
		});
	}

	if (mkdirBtn) {
		mkdirBtn.addEventListener('click', function () {
			if (!canMkdir || busy) {
				return;
			}
			var name = window.prompt('Klasör adı');
			if (!name) {
				return;
			}
			busy = true;
			updateMeta();
			api('mkdir', {
				method: 'POST',
				fields: { path: currentPath || '', name: name }
			})
				.then(function (data) {
					if (!data || !data.success) {
						setModalStatus((data && data.message) || 'Klasör oluşturulamadı', 'error');
						return;
					}
					loadPath(currentPath);
					setModalStatus(data.message || 'Klasör oluşturuldu', 'success');
				})
				.catch(function (err) {
					setModalStatus((err && err.message) || 'Hata', 'error');
				})
				.finally(function () {
					busy = false;
					updateMeta();
				});
		});
	}

	var homeMedia = modalEl.querySelector('[data-ml-home-media]');
	if (homeMedia) {
		homeMedia.addEventListener('click', function () {
			loadPath('media');
		});
	}

	document.querySelectorAll('[data-media-pick]').forEach(function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var targetSel = btn.getAttribute('data-media-target') || '';
			var multi = btn.getAttribute('data-media-multi') === '1';
			open({
				multi: multi,
				confirmLabel: btn.getAttribute('data-media-label') || 'Seç',
				onSelect: function (items) {
					if (!items.length) {
						return;
					}
					var target = targetSel ? document.querySelector(targetSel) : null;
					if (target) {
						var val = items[0].path || '';
						if (val && val.indexOf('img/') !== 0 && !/^https?:\/\//i.test(val)) {
							val = 'img/' + val.replace(/^\/+/, '');
						} else if (!val) {
							val = items[0].url || '';
						}
						target.value = val;
						target.dispatchEvent(new Event('change', { bubbles: true }));
					}
					var preview = btn.getAttribute('data-media-preview');
					if (preview) {
						var img = document.querySelector(preview);
						if (img && items[0].url) {
							img.src = items[0].url;
							img.hidden = false;
						}
					}
				}
			});
		});
	});

	window.FShopMediaPicker = {
		available: true,
		open: open
	};
})();
