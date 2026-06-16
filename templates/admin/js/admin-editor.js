(function () {
	if (typeof tinymce === 'undefined') {
		return;
	}

	tinymce.init({
		selector: 'textarea.wysiwyg-editor',
		height: 360,
		menubar: false,
		plugins: 'lists link image table code fullscreen',
		toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code fullscreen',
		content_style: 'body { font-family: Segoe UI, sans-serif; font-size: 14px; }',
		language: 'tr',
		language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@24/langs6/tr.js',
		relative_urls: false,
		convert_urls: false,
		promotion: false,
		branding: false
	});

	document.querySelectorAll('form').forEach(function (form) {
		form.addEventListener('submit', function () {
			if (typeof tinymce !== 'undefined') {
				tinymce.triggerSave();
			}
		});
	});
})();
