(function () {
	'use strict';

	var body = document.getElementById('optionGroupsBody');
	var addBtn = document.getElementById('addOptionGroup');
	var rowIndex = body ? body.querySelectorAll('.option-group-row').length : 0;

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function buildRow(data) {
		data = data || {};
		var idx = rowIndex++;

		return ''
			+ '<div class="option-group-row border rounded p-3 mb-2 bg-white">'
			+ '<div class="row g-2 align-items-start">'
			+ '<div class="col-md-4">'
			+ '<label class="form-label small mb-1">Grup adı</label>'
			+ '<input type="text" name="option_groups[' + idx + '][name]" class="form-control form-control-sm" value="' + escapeHtml(data.name || '') + '" placeholder="Boyut">'
			+ '</div>'
			+ '<div class="col-md-2">'
			+ '<label class="form-label small mb-1">Zorunlu</label>'
			+ '<div class="form-check mt-1">'
			+ '<input type="hidden" name="option_groups[' + idx + '][required]" value="0">'
			+ '<input type="checkbox" name="option_groups[' + idx + '][required]" value="1" class="form-check-input"' + (data.required === false ? '' : ' checked') + '>'
			+ '</div>'
			+ '</div>'
			+ '<div class="col-md-5">'
			+ '<label class="form-label small mb-1">Değerler (her satıra bir tane)</label>'
			+ '<textarea name="option_groups[' + idx + '][values_text]" class="form-control form-control-sm" rows="3" placeholder="1&#10;1.5&#10;2">' + escapeHtml(data.values_text || '') + '</textarea>'
			+ '</div>'
			+ '<div class="col-md-1 text-end">'
			+ '<label class="form-label small mb-1 d-block">&nbsp;</label>'
			+ '<button type="button" class="btn btn-sm btn-outline-danger option-group-remove" title="Grubu sil">&times;</button>'
			+ '</div>'
			+ '</div>'
			+ '</div>';
	}

	if (addBtn && body) {
		addBtn.addEventListener('click', function () {
			body.insertAdjacentHTML('beforeend', buildRow({
				name: '',
				required: true,
				values_text: ''
			}));
		});

		body.addEventListener('click', function (event) {
			var btn = event.target.closest('.option-group-remove');

			if (!btn) {
				return;
			}

			var row = btn.closest('.option-group-row');

			if (row) {
				row.remove();
			}
		});
	}
})();
