document.addEventListener('DOMContentLoaded', function () {
	if (window.history.replaceState) {
		window.history.replaceState(null, null, window.location.href);
	}

	var mobileMenuBtn = document.getElementById('mobileMenuBtn');
	var sidebar = document.getElementById('adminSidebar');
	var backdrop = document.getElementById('sidebarBackdrop');

	function openSidebar() {
		if (!sidebar) {
			return;
		}
		sidebar.classList.add('active');
		if (backdrop) {
			backdrop.hidden = false;
		}
		document.body.classList.add('admin-sidebar-open');
	}

	function closeSidebar() {
		if (!sidebar) {
			return;
		}
		sidebar.classList.remove('active');
		if (backdrop) {
			backdrop.hidden = true;
		}
		document.body.classList.remove('admin-sidebar-open');
		if (mobileMenuBtn) {
			mobileMenuBtn.classList.remove('open');
		}
	}

	function toggleSidebar() {
		if (sidebar && sidebar.classList.contains('active')) {
			closeSidebar();
		} else {
			openSidebar();
		}
	}

	if (mobileMenuBtn) {
		mobileMenuBtn.addEventListener('click', function () {
			this.classList.toggle('open');
			toggleSidebar();
		});
	}

	if (backdrop) {
		backdrop.addEventListener('click', closeSidebar);
	}

	window.addEventListener('resize', function () {
		if (window.innerWidth >= 992) {
			closeSidebar();
		}
	});

	initModuleListFilters();
	initAdminConfirmBindings();
});

window.AdminConfirm = {
	show: function (title, message, onConfirm) {
		var modalEl = document.getElementById('admin-confirm-modal');
		if (!modalEl) {
			if (window.confirm(message || title)) {
				if (typeof onConfirm === 'function') {
					onConfirm();
				}
			}
			return;
		}

		if (modalEl.parentNode !== document.body) {
			document.body.appendChild(modalEl);
		}

		var titleEl = document.getElementById('admin-confirm-title');
		var messageEl = document.getElementById('admin-confirm-message');
		var confirmBtn = document.getElementById('admin-confirm-btn');

		if (titleEl) {
			titleEl.textContent = title || (window.__adminI18n && window.__adminI18n.confirmTitle) || 'Confirm action';
		}
		if (messageEl) {
			messageEl.textContent = message || (window.__adminI18n && window.__adminI18n.confirmMessage) || 'Are you sure you want to perform this action?';
		}

		var newBtn = confirmBtn.cloneNode(true);
		confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

		newBtn.addEventListener('click', function () {
			var instance = window.bootstrap && bootstrap.Modal
				? bootstrap.Modal.getInstance(modalEl)
				: null;
			if (instance) {
				instance.hide();
			}
			if (typeof onConfirm === 'function') {
				onConfirm();
			}
		});

		if (window.bootstrap && bootstrap.Modal) {
			bootstrap.Modal.getOrCreateInstance(modalEl).show();
		} else {
			modalEl.classList.add('show');
			modalEl.style.display = 'block';
		}
	}
};

function initAdminConfirmBindings() {
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-admin-confirm');
		if (!btn) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		var title = btn.getAttribute('data-confirm-title') || (window.__adminI18n && window.__adminI18n.confirmTitle) || 'Confirm action';
		var message = btn.getAttribute('data-confirm-message') || (window.__adminI18n && window.__adminI18n.confirmMessage) || 'Are you sure you want to perform this action?';
		var form = btn.form || btn.closest('form');

		AdminConfirm.show(title, message, function () {
			if (!form) {
				return;
			}

			if (typeof form.requestSubmit === 'function') {
				btn.classList.remove('js-admin-confirm');
				form.requestSubmit(btn);
				btn.classList.add('js-admin-confirm');
				return;
			}

			var name = btn.getAttribute('name');
			var value = btn.getAttribute('value') || '1';
			if (name) {
				var existing = form.querySelector('input[type="hidden"][data-admin-confirm-proxy="1"][name="' + name + '"]');
				if (!existing) {
					existing = document.createElement('input');
					existing.type = 'hidden';
					existing.name = name;
					existing.setAttribute('data-admin-confirm-proxy', '1');
					form.appendChild(existing);
				}
				existing.value = value;
			}
			form.submit();
		});
	}, true);
}

function initModuleListFilters() {
	var list = document.getElementById('moduleList');
	var search = document.getElementById('moduleSearch');
	var filter = document.getElementById('moduleStatusFilter');
	var emptyState = document.getElementById('moduleListEmpty');

	if (!list) {
		return;
	}

	function normalize(value) {
		return (value || '').toLocaleLowerCase('tr-TR').trim();
	}

	function matchesStatus(rowStatus, statusFilter) {
		if (statusFilter === 'all') {
			return true;
		}

		if (statusFilter === 'installed') {
			return rowStatus === 'installed' || rowStatus === 'active';
		}

		return rowStatus === statusFilter;
	}

	function applyFilters() {
		var query = normalize(search ? search.value : '');
		var status = filter ? filter.value : 'all';
		var rows = list.querySelectorAll('.module-row');
		var visibleCount = 0;

		rows.forEach(function (row) {
			var text = normalize(row.getAttribute('data-module-search'));
			var rowStatus = row.getAttribute('data-module-status') || '';
			var matchQuery = query === '' || text.indexOf(query) !== -1;
			var matchStatus = matchesStatus(rowStatus, status);
			var visible = matchQuery && matchStatus;

			row.classList.toggle('d-none', !visible);

			if (visible) {
				visibleCount++;
			}
		});

		if (emptyState) {
			emptyState.classList.toggle('d-none', visibleCount > 0 || rows.length === 0);
		}
	}

	if (search) {
		search.addEventListener('input', applyFilters);
		search.addEventListener('search', applyFilters);
	}

	if (filter) {
		filter.addEventListener('change', applyFilters);
	}

	applyFilters();
}
