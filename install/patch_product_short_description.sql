-- Ürün kısa açıklama alanı (utf8mb4)
ALTER TABLE `products`
  ADD COLUMN `short_description` varchar(512)
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
    AFTER `product_name`;

-- Kolon zaten varsa charset düzeltmesi:
-- ALTER TABLE `products` MODIFY COLUMN `short_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';
