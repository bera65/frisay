-- FShop demo verileri (kurulum sihirbazında isteğe bağlı)

INSERT INTO `categories` (`id_category`, `id_parent`, `category_name`, `category_link`, `active`) VALUES
(1, 0, 'Katalog', 'katalog', 1),
(2, 1, 'Kozmetik', 'kozmetik', 1),
(3, 1, 'Vitamin & Takviye', 'vitamin-takviye', 1),
(4, 1, 'Elektronik', 'elektronik', 1);

INSERT INTO `brands` (`id_brand`, `brand_name`, `brand_link`, `active`) VALUES
(1, 'Nutrof', 'nutrof', 1),
(2, 'TechnoMark', 'technomark', 1),
(3, 'FShop Demo', 'fshop-demo', 1);

INSERT INTO `products` (
  `id_product`, `id_category`, `id_brand`, `product_name`, `short_description`, `description`,
  `product_link`, `price`, `doviz`, `doviz_price`, `doviz_old_price`, `old_price`, `vat`,
  `active`, `stock`, `cargo_day`, `label`, `stock_code`, `barcode`, `desi`
) VALUES
(1, 3, 1, 'SolNutrof Total 30 Kapsül',
 'Göz sağlığı için gelişmiş formül.',
 '<p><strong>SolNutrof Total</strong> göz sağlığını desteklemek için geliştirilmiş takviye edici gıdadır.</p><ul><li>30 kapsül</li><li>Omega-3 ve antioksidan içerir</li></ul>',
 'solnutrof-total-30-kapsul', 899.00, 'try', 899.00, 1099.00, 1099.00, 20.00, 1, 120, 2, '3 Al 2 Öde', 'SOLNUTROF30', '8690000000011', 1),

(2, 2, 1, 'Nutrof Cilt Bakım Serumu 30 ml',
 'Nem dengesi ve parlak görünüm için serum.',
 '<p>Günlük cilt bakım rutininize eklenebilecek hafif formül.</p>',
 'nutrof-cilt-bakim-serumu', 549.00, 'try', 549.00, 0.00, 0.00, 20.00, 1, 85, 1, 'Yeni', 'NUTSERUM30', '8690000000028', 1),

(3, 4, 2, 'Kablosuz Kulaklık Pro X',
 'Aktif gürültü engelleme özellikli kulaklık.',
 '<p>40 saate kadar pil ömrü, Bluetooth 5.3, şarj kutusu dahil.</p>',
 'kablosuz-kulaklik-pro-x', 0.00, 'usd', 49.99, 59.99, 0.00, 20.00, 1, 40, 3, 'Çok Satan', 'TECH-HP-49', '8690000000035', 2),

(4, 4, 2, 'Akıllı Saat Lite',
 'Adım, nabız ve uyku takibi.',
 '<p>Su geçirmez kasa, 7 gün pil ömrü, mobil uygulama desteği.</p>',
 'akilli-saat-lite', 0.00, 'eur', 79.00, 0.00, 0.00, 20.00, 1, 25, 0, 'Fırsat', 'TECH-WATCH79', '8690000000042', 1),

(5, 3, 3, 'Multivitamin Complex 60 Tablet',
 'Günlük vitamin ve mineral desteği.',
 '<p>60 tabletlik ekonomik ambalaj.</p>',
 'multivitamin-complex-60', 329.00, 'try', 329.00, 399.00, 399.00, 10.00, 1, 200, 1, '', 'DEMO-MULTI60', '8690000000059', 1);

INSERT INTO `users` (`user_full_name`, `phone`, `email`, `password`, `active`) VALUES
('Demo Müşteri', '05551234567', 'musteri@example.com', '$2y$10$GPLawYLHswCkWbeq8.ErQeI2/Eq.nLk4r1kG/PzfjhlDN2k3z3JTG', 1);

INSERT INTO `coupons` (`code`, `discount_type`, `discount_value`, `min_cart`, `max_uses`, `active`) VALUES
('HOSGELDIN10', 'percent', 10.00, 200.00, 100, 1);
