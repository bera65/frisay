{if $sonuc}
	<div class="alert alert-{$sonucType|default:'success'}">{$sonuc}</div>
{/if}
<form method="get" class="admin-toolbar row g-2 mb-3">
	<div class="col-md-3">
		<input type="text" name="q" class="form-control form-control-sm" placeholder="{'Search...'|adminT}" value="{$searchQuery|escape}">
	</div>
	<div class="col-md-2">
		<select name="category" class="form-select form-select-sm">
			<option value="0">{'All categories'|adminT}</option>
			{foreach $categoryOptions as $cat}
			<option value="{$cat.id_category}"{if $categoryFilter == $cat.id_category} selected{/if}>{$cat.category_name|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-md-2">
		<select name="brand" class="form-select form-select-sm">
			<option value="0">{'All brands'|adminT}</option>
			{foreach $brandOptions as $b}
			<option value="{$b.id_brand}"{if $brandFilter == $b.id_brand} selected{/if}>{$b.brand_name|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-md-2">
		<select name="active" class="form-select form-select-sm">
			<option value=""{if $activeFilter == -1} selected{/if}>{'All statuses'|adminT}</option>
			<option value="1"{if $activeFilter == 1} selected{/if}>{'Active'|adminT}</option>
			<option value="0"{if $activeFilter == 0} selected{/if}>{'Inactive'|adminT}</option>
		</select>
	</div>
	<div class="col-md-3 d-flex gap-2">
		<button type="submit" class="btn btn-sm btn-dark">{'Filter'|adminT}</button>
		<a href="{$adminUrl}products" class="btn btn-sm btn-outline-secondary">{'Clear'|adminT}</a>
	</div>
</form>
<div class="admin-panel mb-3 d-flex justify-content-between align-items-center">
	<div>
		<a href="{$adminUrl}product" class="btn btn-sm btn-primary">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
			{'New Product'|adminT}
		</a>
	</div>
	<div class="d-flex gap-2">
		<form action="" method="POST">
			<button class="btn btn-dark btn-sm" name="exprtExcel" value="{$adminToken}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
				{'Export'|adminT}
			</button>
		</form>
		<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload-icon lucide-upload"><path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/></svg>
			{'Import'|adminT}
		</button>
	</div>
</div>
<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th></th>
					<th>{'Product'|adminT}</th>
					<th>{'Category'|adminT}</th>
					<th>{'Brand'|adminT}</th>
					<th>{'Price'|adminT}</th>
					<th>{'Stock'|adminT}</th>
					<th>{'Status'|adminT}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $products|@count}
				{foreach $products as $row}
				<tr>
					<td style="width:48px"><img src="{$row.image_url}" alt="" width="40" height="40" style="object-fit:contain"></td>
					<td>{$row.product_name|escape}{if $row.product_type|default:'physical' == 'virtual'} <span class="badge bg-info">{'Virtual'|adminT}</span>{/if}</td>
					<td>{$row.category_name|escape}</td>
					<td>{$row.brand_name|escape}</td>
					<td>{$row.price_formatted}</td>
					<td>{$row.stock}</td>
					<td>{$row.active_label|escape}</td>
					<td class="text-end">
					<form action="" method="POST">
						<a href="{$adminUrl}product?id={$row.id_product}" class="btn btn-sm btn-outline-dark">
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
						</a>
						<input type="hidden" name="idProduct" value="{$row.id_product}" />
						<button type="submit" name="deleteProduct" value="{$adminToken}" class="btn btn-danger btn-sm js-admin-confirm" data-confirm-title="{'Delete product'|adminT}" data-confirm-message="{'Are you sure you want to delete this product? The product and related images will be permanently removed.'|adminT}">
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
						</button>
					</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="8" class="text-muted">{'No records found.'|adminT}</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{include file='admin/plugin/pagination.tpl'}

<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="importExcelModalLabel">{'Import products from Excel'|adminT}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
			</div>
			<form action="" method="POST" enctype="multipart/form-data">
				<div class="modal-body">
					<div class="alert alert-info mb-3">
						<strong>{'Information'|adminT}</strong>
						<ul class="mb-0 mt-2 small">
							<li>{'Only <strong>.xlsx</strong> files are allowed.'|adminT nofilter}</li>
							<li>{'Download the current product list with <strong>Export</strong> first to get a sample template.'|adminT nofilter}</li>
							<li>{'If <strong>SKU</strong> matches an existing product, the record is updated; otherwise a new product is created.'|adminT nofilter}</li>
							<li>{'If <strong>category</strong> or <strong>brand</strong> in Excel does not exist, it is created automatically.'|adminT nofilter}</li>
							<li>{'In the <strong>Images</strong> column, enter image URLs separated by <strong>;</strong>. On import, existing product images are replaced with the URLs in Excel.'|adminT nofilter}</li>
						</ul>
					</div>
					<div class="mb-3">
						<p class="fw-semibold mb-2">{'Expected columns'|adminT}</p>
						<div class="small text-muted">
							Product Name, Barcode, Stock Code, {'Desi'|adminT}, Price, Old Price, Vat, Stock,
							short Description, Description, Meta Title, Meta Description, Slug,
							Category Name, Brand Name, Active
						</div>
					</div>
					<div class="mb-0">
						<label for="excelFile" class="form-label">{'Excel file'|adminT}</label>
						<input type="file" id="excelFile" name="excelFile" class="form-control" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
					<input type="hidden" name="imprtExcel" value="{$adminToken}">
					<button type="submit" class="btn btn-success">{'Upload and import'|adminT}</button>
				</div>
			</form>
		</div>
	</div>
</div>
