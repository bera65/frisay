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
});
