<?php

class CatalogFilter
{
	public int $baseCategoryId = 0;
	public int $subCategoryId = 0;
	public int $brandId = 0;
	public ?float $priceMin = null;
	public ?float $priceMax = null;

	/** @var int[] */
	private array $allowedCategoryIds = [];

	public static function forCategory(int $idCategory): self
	{
		$filter = new self();
		$filter->baseCategoryId = max(0, $idCategory);
		$filter->allowedCategoryIds = $idCategory > 0 ? Category::getScopeIds($idCategory) : [];
		$filter->readRequest();

		return $filter;
	}

	public function readRequest(): void
	{
		$sub = (int) Tools::getValue('subcat', 0);
		if ($sub > 0 && in_array($sub, $this->allowedCategoryIds, true)) {
			$this->subCategoryId = $sub;
		}

		$this->brandId = max(0, (int) Tools::getValue('brand', 0));

		$priceMin = trim((string) Tools::getValue('price_min', ''));
		$priceMax = trim((string) Tools::getValue('price_max', ''));

		$this->priceMin = $priceMin !== '' ? max(0, (float) str_replace(',', '.', $priceMin)) : null;
		$this->priceMax = $priceMax !== '' ? max(0, (float) str_replace(',', '.', $priceMax)) : null;

		if ($this->priceMin !== null && $this->priceMax !== null && $this->priceMin > $this->priceMax) {
			$swap = $this->priceMin;
			$this->priceMin = $this->priceMax;
			$this->priceMax = $swap;
		}
	}

	/** @return int[] */
	public function getCategoryIds(): array
	{
		if ($this->baseCategoryId <= 0) {
			return [];
		}

		if ($this->subCategoryId > 0) {
			return Category::getScopeIds($this->subCategoryId);
		}

		return $this->allowedCategoryIds;
	}

	/** @return array<string, scalar> */
	public function toQueryArray(): array
	{
		$query = [];

		if ($this->subCategoryId > 0) {
			$query['subcat'] = $this->subCategoryId;
		}

		if ($this->brandId > 0) {
			$query['brand'] = $this->brandId;
		}

		if ($this->priceMin !== null) {
			$query['price_min'] = $this->formatPriceQuery($this->priceMin);
		}

		if ($this->priceMax !== null) {
			$query['price_max'] = $this->formatPriceQuery($this->priceMax);
		}

		return $query;
	}

	public function hasActiveFilters(): bool
	{
		return $this->subCategoryId > 0
			|| $this->brandId > 0
			|| $this->priceMin !== null
			|| $this->priceMax !== null;
	}

	public function buildUrl(string $baseUrl, array $extra = []): string
	{
		$params = $this->toQueryArray();

		foreach ($extra as $key => $value) {
			if ($value === null) {
				unset($params[$key]);
			} else {
				$params[$key] = $value;
			}
		}

		$params = array_filter($params, static function ($value) {
			return $value !== null && $value !== '';
		});

		$qs = http_build_query($params);

		return $baseUrl . ($qs !== '' ? '?' . $qs : '');
	}

	private function formatPriceQuery(float $value): string
	{
		return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
	}
}
