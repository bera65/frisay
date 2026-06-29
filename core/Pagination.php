<?php

class Pagination
{
	public const PER_PAGE = 24;

	public static function build(int $total, int $page, int $perPage, string $baseUrl, array $query = []): array
	{
		$totalPages = max(1, (int) ceil($total / $perPage));
		$page = max(1, min($page, $totalPages));
		$offset = ($page - 1) * $perPage;

		$makeUrl = function (int $p) use ($baseUrl, $query) {
			$params = $query;

			if ($p > 1) {
				$params['page'] = $p;
			} else {
				unset($params['page']);
			}

			$params = array_filter($params, static function ($value) {
				return $value !== null && $value !== '';
			});

			$qs = http_build_query($params);

			return $baseUrl . ($qs !== '' ? '?' . $qs : '');
		};

		$pages = [];
		$start = max(1, $page - 2);
		$end = min($totalPages, $page + 2);

		for ($i = $start; $i <= $end; $i++) {
			$pages[] = [
				'num' => $i,
				'url' => $makeUrl($i),
				'current' => $i === $page,
			];
		}

		return [
			'page' => $page,
			'per_page' => $perPage,
			'total' => $total,
			'total_pages' => $totalPages,
			'offset' => $offset,
			'has_prev' => $page > 1,
			'has_next' => $page < $totalPages,
			'prev_url' => $makeUrl($page - 1),
			'next_url' => $makeUrl($page + 1),
			'pages' => $pages,
		];
	}

	public static function resolveSort(string $sort): string
	{
		$allowed = ['newest', 'price_asc', 'price_desc', 'name_asc', 'discount'];

		if (!in_array($sort, $allowed, true)) {
			$sort = 'newest';
		}

		$map = [
			'newest' => 'p.id_product DESC',
			'price_asc' => 'p.price ASC',
			'price_desc' => 'p.price DESC',
			'name_asc' => 'p.product_name ASC',
			'discount' => '(p.old_price > p.price) DESC, p.id_product DESC',
		];

		return $map[$sort];
	}

	public static function getSortOptions(): array
	{
		$options = [
			'newest' => 'Newest',
			'price_asc' => 'Price (Low to High)',
			'price_desc' => 'Price (High to Low)',
			'name_asc' => 'Name (A-Z)',
			'discount' => 'Discount Rate',
		];

		if (function_exists('translate')) {
			foreach ($options as $key => $label) {
				$options[$key] = translate($label);
			}
		}

		return $options;
	}
}
