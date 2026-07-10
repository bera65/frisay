(function () {
	var field = document.getElementById('discountTimerAdminField');
	if (!field) return;

	var pricePanel = document.getElementById('productOldPrice');
	if (!pricePanel) return;

	var col = pricePanel.closest('.col-6');
	var row = col ? col.closest('.row') : null;

	if (row) {
		var wrap = document.createElement('div');
		wrap.className = 'col-12';
		wrap.appendChild(field);
		row.appendChild(wrap);
	}
})();
