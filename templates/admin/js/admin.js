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
});

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
