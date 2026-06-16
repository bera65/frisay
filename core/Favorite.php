<?php

class Favorite
{
	public static function toggle(int $idProduct): array
	{
		if (!Customer::isLoggedIn()) {
			return [
				'success' => false,
				'message' => 'Favorilere eklemek için giriş yapın',
				'login_required' => true,
				'is_favorite' => false,
				'count' => 0,
			];
		}

		$product = Product::getById($idProduct);
		if (!$product) {
			return self::fail('Ürün bulunamadı');
		}

		$idUser = Customer::getId();
		$exists = DB::getValue(
			'SELECT id_favorite FROM favorites WHERE id_user = ? AND id_product = ? LIMIT 1',
			[$idUser, $idProduct]
		);

		if ($exists) {
			DB::execute(
				'DELETE FROM favorites WHERE id_user = ? AND id_product = ?',
				[$idUser, $idProduct]
			);

			return self::ok('Favorilerden kaldırıldı', false);
		}

		DB::insert('favorites', [
			'id_user' => $idUser,
			'id_product' => $idProduct,
		]);

		return self::ok('Favorilere eklendi', true);
	}

	public static function remove(int $idProduct): array
	{
		if (!Customer::isLoggedIn()) {
			return self::fail('Giriş yapmalısınız');
		}

		DB::execute(
			'DELETE FROM favorites WHERE id_user = ? AND id_product = ?',
			[Customer::getId(), $idProduct]
		);

		return self::ok('Favorilerden kaldırıldı', false);
	}

	public static function isFavorite(int $idProduct, ?int $idUser = null): bool
	{
		if (!$idUser && !Customer::isLoggedIn()) {
			return false;
		}

		$idUser = $idUser ?: Customer::getId();

		return (bool) DB::getValue(
			'SELECT id_favorite FROM favorites WHERE id_user = ? AND id_product = ? LIMIT 1',
			[$idUser, $idProduct]
		);
	}

	public static function getCount(?int $idUser = null): int
	{
		if (!$idUser && !Customer::isLoggedIn()) {
			return 0;
		}

		$idUser = $idUser ?: Customer::getId();

		return (int) DB::getValue(
			'SELECT COUNT(*) FROM favorites WHERE id_user = ?',
			[$idUser]
		);
	}

	public static function getList(?int $idUser = null, int $limit = 0, int $offset = 0): array
	{
		if (!$idUser && !Customer::isLoggedIn()) {
			return [];
		}

		$idUser = $idUser ?: Customer::getId();

		$sql = 'SELECT p.*, b.brand_name, b.brand_link, c.category_name, c.category_link, i.id_image
			FROM favorites f
			INNER JOIN products p ON f.id_product = p.id_product
			INNER JOIN brands b ON p.id_brand = b.id_brand
			INNER JOIN categories c ON p.id_category = c.id_category
			LEFT JOIN images i ON p.id_product = i.id_product AND i.cover = 1
			WHERE f.id_user = ? AND p.active = 1
			ORDER BY f.date_add DESC';

		if ($limit > 0) {
			$sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
		}

		$rows = DB::execute($sql, [$idUser]);

		if (!$rows) {
			return [];
		}

		return Product::enrichList($rows);
	}

	private static function ok(string $message, bool $isFavorite): array
	{
		return [
			'success' => true,
			'message' => $message,
			'is_favorite' => $isFavorite,
			'count' => self::getCount(),
		];
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
			'is_favorite' => false,
			'count' => self::getCount(),
		];
	}
}
