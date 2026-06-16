{if $sonuc}
	<div class="alert alert-{$sonucType|default:'success'}">{$sonuc}</div>
{/if}
<form method="get" class="admin-toolbar row g-2 mb-3">
	<div class="col-md-3">
		<input type="text" name="q" class="form-control form-control-sm" placeholder="Ara..." value="{$searchQuery|escape}">
	</div>
	<div class="col-md-2">
		<select name="category" class="form-select form-select-sm">
			<option value="0">Tüm kategoriler</option>
			{foreach $categoryOptions as $cat}
			<option value="{$cat.id_category}"{if $categoryFilter == $cat.id_category} selected{/if}>{$cat.category_name|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-md-2">
		<select name="brand" class="form-select form-select-sm">
			<option value="0">Tüm markalar</option>
			{foreach $brandOptions as $b}
			<option value="{$b.id_brand}"{if $brandFilter == $b.id_brand} selected{/if}>{$b.brand_name|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="col-md-2">
		<select name="active" class="form-select form-select-sm">
			<option value=""{if $activeFilter == -1} selected{/if}>Tüm durumlar</option>
			<option value="1"{if $activeFilter == 1} selected{/if}>Aktif</option>
			<option value="0"{if $activeFilter == 0} selected{/if}>Pasif</option>
		</select>
	</div>
	<div class="col-md-3 d-flex gap-2">
		<button type="submit" class="btn btn-sm btn-dark">Filtrele</button>
		<a href="{$adminUrl}products" class="btn btn-sm btn-outline-secondary">Temizle</a>
	</div>
</form>
<div class="admin-panel mb-3 d-flex justify-content-between align-items-center">
	<div>
		<a href="{$adminUrl}product" class="btn btn-sm btn-primary">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
			Yeni Ürün
		</a>
	</div>
	<div class="d-flex gap-2">
		<form action="" method="POST">
			<button class="btn btn-dark btn-sm" name="exprtExcel" value="{$adminToken}">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
				Dışa Aktar
			</button>
		</form>
		<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload-icon lucide-upload"><path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/></svg>
			İçe Aktar
		</button>
	</div>
</div>
<div class="admin-panel">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th></th>
					<th>Ürün</th>
					<th>Kategori</th>
					<th>Marka</th>
					<th>Fiyat</th>
					<th>Stok</th>
					<th>Durum</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $products|@count}
				{foreach $products as $row}
				<tr>
					<td style="width:48px"><img src="{$row.image_url}" alt="" width="40" height="40" style="object-fit:contain"></td>
					<td>{$row.product_name|escape}</td>
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
						<button type="submit" name="deleteProduct" value="{$adminToken}" class="btn btn-danger btn-sm">
							<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
						</button>
					</form>
					</td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="8" class="text-muted">Kayıt bulunamadı.</td></tr>
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
				<h5 class="modal-title" id="importExcelModalLabel">Excel ile Ürün İçe Aktar</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
			</div>
			<form action="" method="POST" enctype="multipart/form-data">
				<div class="modal-body">
					<div class="alert alert-info mb-3">
						<strong>Bilgilendirme</strong>
						<ul class="mb-0 mt-2 small">
							<li>Sadece <strong>.xlsx</strong> formatında dosya yükleyebilirsiniz.</li>
							<li>Örnek şablon için önce <strong>Dışa Aktar</strong> ile mevcut ürün listesini indirebilirsiniz.</li>
							<li><strong>Barkod</strong> veya <strong>Stok Kodu</strong> mevcut ürünle eşleşirse kayıt güncellenir; eşleşme yoksa yeni ürün oluşturulur.</li>
							<li>Excel'deki <strong>kategori</strong> veya <strong>marka</strong> sistemde yoksa otomatik olarak oluşturulur.</li>
							<li>Ürün görselleri Excel ile aktarılmaz; görselleri ürün düzenleme sayfasından yükleyebilirsiniz.</li>
						</ul>
					</div>
					<div class="mb-3">
						<p class="fw-semibold mb-2">Beklenen sütunlar</p>
						<div class="small text-muted">
							Product Name, Barcode, Stock Code, Desi, Price, Old Price, Vat, Stock,
							short Description, Description, Meta Title, Meta Description, Slug,
							Category Name, Brand Name, Active
						</div>
					</div>
					<div class="mb-0">
						<label for="excelFile" class="form-label">Excel Dosyası</label>
						<input type="file" id="excelFile" name="excelFile" class="form-control" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">İptal</button>
					<input type="hidden" name="imprtExcel" value="{$adminToken}">
					<button type="submit" class="btn btn-success">Yükle ve İçe Aktar</button>
				</div>
			</form>
		</div>
	</div>
</div>
