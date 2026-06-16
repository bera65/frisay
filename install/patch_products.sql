ALTER TABLE `products`
  ADD COLUMN `stock` int(11) NOT NULL DEFAULT 100 AFTER `active`;

UPDATE `products` SET `stock` = 100 WHERE `stock` = 0;
