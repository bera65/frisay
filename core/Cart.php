<?php

class Cart
{
	const SESSION_KEY = 'cart';

	public static function init(): void
	{
		if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
			$_SESSION[self::SESSION_KEY] = [];
		}
	}

	public static function add(int $idProduct, int $qty = 1): array
	{
		self::init();

		$product = Product::getById($idProduct);
		if (!$product) {
			return self::fail('Ürün bulunamadı');
		}

		$stock = Product::getStock($product);
		if ($stock <= 0) {
			return self::fail('Bu ürün stokta yok');
		}

		$qty = max(1, $qty);
		$id = (int) $idProduct;
		$current = (int) ($_SESSION[self::SESSION_KEY][$id] ?? 0);
		$maxAllowed = $stock - $current;

		if ($maxAllowed <= 0) {
			return self::fail('Sepette zaten maksimum stok adedi var (' . $stock . ' adet)');
		}

		$added = min($qty, $maxAllowed);
		$_SESSION[self::SESSION_KEY][$id] = $current + $added;

		$message = $added < $qty
			? 'Sepete eklendi (stok sınırı: ' . $stock . ' adet)'
			: 'Sepete eklendi';

		return self::ok($message);
	}

	public static function update(int $idProduct, int $qty): array
	{
		self::init();
		$id = (int) $idProduct;

		if ($qty <= 0) {
			return self::remove($id);
		}

		$product = Product::getById($id);

		if (!$product) {
			unset($_SESSION[self::SESSION_KEY][$id]);

			return self::fail('Ürün bulunamadı');
		}

		$stock = Product::getStock($product);
		if ($stock <= 0) {
			unset($_SESSION[self::SESSION_KEY][$id]);

			return self::fail('Bu ürün stokta yok');
		}

		$newQty = min($stock, max(1, $qty));
		$_SESSION[self::SESSION_KEY][$id] = $newQty;

		$message = $newQty < $qty
			? 'Sepet güncellendi (stok sınırı: ' . $stock . ' adet)'
			: 'Sepet güncellendi';

		return self::ok($message);
	}

	public static function remove(int $idProduct): array
	{
		self::init();
		unset($_SESSION[self::SESSION_KEY][(int) $idProduct]);

		return self::ok('Ürün sepetten kaldırıldı');
	}

	public static function clear(): array
	{
		$_SESSION[self::SESSION_KEY] = [];

		return self::ok('Sepet temizlendi');
	}

	public static function getSummary(): array
	{
		self::init();

		$items = [];
		$total = 0.0;
		$count = 0;

		foreach ($_SESSION[self::SESSION_KEY] as $idProduct => $qty) {
			$product = Product::getById((int) $idProduct);

			if (!$product) {
				unset($_SESSION[self::SESSION_KEY][$idProduct]);
				continue;
			}

			$stock = Product::getStock($product);
			$qty = max(1, (int) $qty);

			if ($stock <= 0) {
				unset($_SESSION[self::SESSION_KEY][$idProduct]);
				continue;
			}

			if ($qty > $stock) {
				$qty = $stock;
				$_SESSION[self::SESSION_KEY][$idProduct] = $qty;
			}

			$lineTotal = (float) $product['price'] * $qty;

			$items[] = [
				'id_product' => (int) $idProduct,
				'product_name' => $product['product_name'],
				'price' => (float) $product['price'],
				'price_formatted' => Tools::displayPrice($product['price']),
				'qty' => $qty,
				'stock' => $stock,
				'max_qty' => $stock,
				'line_total' => $lineTotal,
				'line_total_formatted' => Tools::displayPrice($lineTotal),
				'url' => $product['url'],
				'image_url' => $product['image_url'],
			];

			$total += $lineTotal;
			$count += $qty;
		}

		return [
			'items' => $items,
			'count' => $count,
			'total' => $total,
			'total_formatted' => Tools::displayPrice($total),
			'empty' => empty($items),
		];
	}

	private static function ok(string $message): array
	{
		return array_merge([
			'success' => true,
			'message' => $message,
		], self::getSummary());
	}

	private static function fail(string $message): array
	{
		return array_merge([
			'success' => false,
			'message' => $message,
		], self::getSummary());
	}
}
