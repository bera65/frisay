(function () {
	if (typeof tinymce === 'undefined') {
		return;
	}

	function openMediaForEditor(callback) {
		if (!window.FShopMediaPicker || !FShopMediaPicker.available) {
			callback('', { alt: '' });
			return;
		}

		FShopMediaPicker.open({
			multi: false,
			confirmLabel: 'Editöre ekle',
			onSelect: function (items) {
				if (!items || !items.length) {
					return;
				}
				var item = items[0];
				callback(item.url || '', { alt: item.name || '', title: item.name || '' });
			}
		});
	}

	var toolbar = 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code fullscreen';
	if (window.FShopMediaPicker && FShopMediaPicker.available) {
		toolbar = 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link fshopmedia image table | code fullscreen';
	}

	tinymce.init({
		selector: 'textarea.wysiwyg-editor',
		height: 360,
		menubar: false,
		plugins: 'lists link image table code fullscreen',
		toolbar: toolbar,
		content_style: 'body { font-family: Segoe UI, sans-serif; font-size: 14px; }',
		language: 'tr',
		language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24/langs6/tr.js',
		relative_urls: false,
		convert_urls: false,
		promotion: false,
		branding: false,
		file_picker_types: 'image',
		file_picker_callback: function (callback, value, meta) {
			if (meta.filetype === 'image') {
				openMediaForEditor(callback);
			}
		},
		setup: function (editor) {
			if (!window.FShopMediaPicker || !FShopMediaPicker.available) {
				return;
			}

			editor.ui.registry.addButton('fshopmedia', {
				icon: 'gallery',
				tooltip: 'Medya kütüphanesi',
				onAction: function () {
					FShopMediaPicker.open({
						multi: false,
						confirmLabel: 'Editöre ekle',
						onSelect: function (items) {
							if (!items || !items.length) {
								return;
							}
							var url = items[0].url || '';
							if (url) {
								editor.insertContent('<img src="' + url.replace(/"/g, '&quot;') + '" alt="" />');
							}
						}
					});
				}
			});
		}
	});

	document.querySelectorAll('form').forEach(function (form) {
		form.addEventListener('submit', function () {
			if (typeof tinymce !== 'undefined') {
				tinymce.triggerSave();
			}
		});
	});
})();
