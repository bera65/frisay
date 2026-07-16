<div class="modal fade ml-modal" id="adminMediaLibraryModal" tabindex="-1" aria-labelledby="adminMediaLibraryTitle" aria-hidden="true"
	data-media-api="{$domain}api/admin-media.php"
	data-token="{$adminToken|escape}">
	<div class="modal-dialog modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="adminMediaLibraryTitle">{'File manager'|adminT}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
			</div>

			<div class="ml-toolbar">
				<div class="ml-toolbar-group">
					<span class="ml-toolbar-label">{'Actions'|adminT}</span>
					<button type="button" class="btn btn-sm btn-outline-dark" data-ml-upload-btn title="{'Add new file'|adminT}">{'+ Add file'|adminT}</button>
					<button type="button" class="btn btn-sm btn-outline-secondary" data-ml-mkdir title="{'New folder'|adminT}">{'+ Folder'|adminT}</button>
					<button type="button" class="btn btn-sm btn-outline-secondary" data-ml-home-media title="{'Media folder'|adminT}">{'Media'|adminT}</button>
					<input type="file" data-ml-upload accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
				</div>
				<div class="ml-toolbar-group ms-auto">
					<span class="ml-toolbar-label">{'Filters'|adminT}</span>
					<input type="search" class="form-control form-control-sm" style="width:180px" placeholder="{'Filter...'|adminT}" data-ml-filter>
				</div>
			</div>

			<div class="ml-nav">
				<div class="ml-breadcrumbs" data-ml-crumbs></div>
				<button type="button" class="btn btn-sm btn-outline-secondary" data-ml-refresh>{'Refresh'|adminT}</button>
			</div>

			<div class="ml-body">
				<div class="ml-grid" data-ml-grid>
					<div class="ml-loading">{'Loading…'|adminT}</div>
				</div>
			</div>

			<div class="ml-footer">
				<div>
					<div class="ml-footer-meta" data-ml-meta>{'Select an image or upload new'|adminT}</div>
					<p class="small text-muted mb-0 mt-1" data-ml-status></p>
				</div>
				<div class="ml-footer-actions">
					<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
					<button type="button" class="btn btn-dark btn-sm" data-ml-confirm disabled>{'Select'|adminT}</button>
				</div>
			</div>
		</div>
	</div>
</div>
