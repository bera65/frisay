-- FShop örnek veri
-- Kurulum: mysql -u root -p fshop < install/seed.sql

INSERT INTO `settings` (`title`, `value`) VALUES
('FOLDER', '/fshop/'),
('SHOP_TOKEN', 'change-me-in-production'),
('PRODUCT_LIMIT', '5000'),
('SITE_NAME', 'FShop'),
('DANGER', '7'),
('DOMAIN', 'http://localhost/fshop/');

INSERT INTO `categories` (`id_category`, `id_parent`, `category_name`, `category_link`, `active`) VALUES
(1, 0, 'Ana Sayfa', 'ana-sayfa', 1),
(2, 1, 'Temalar', 'temalar', 1),
(3, 1, 'Modüller', 'moduller', 1);

INSERT INTO `brands` (`id_brand`, `brand_name`, `brand_link`, `active`) VALUES
(1, 'F Yazılım', 'f-yazilim', 1),
(2, 'Presta Centre', 'presta-centre', 1);

INSERT INTO `products` (`id_product`, `id_category`, `id_brand`, `product_name`, `description`, `product_link`, `price`, `old_price`, `vat`, `active`, `stock`) VALUES
(1, 3, 1, 'Prestashop Seo Modülü', 'Google indeksleme ve SEO meta düzenlemeleri için modül.', 'prestashop-seo-modulu', 855.00, 1000.00, 20.00, 1, 100),
(2, 3, 1, 'Sepette Ekstra Ürün Modülü', 'Sepete eklenen ürünle ilişkili öneriler sunar.', 'sepette-ekstra-urun-modulu', 750.00, 0.00, 20.00, 1, 100),
(3, 3, 1, 'Whatsapp Live Chat Pro v6', 'Whatsapp üzerinden canlı destek modülü.', 'whatsapp-live-chat-v6', 900.00, 0.00, 20.00, 1, 100),
(4, 2, 1, 'Extra Tab Modülü', 'Ürünlere özel sekmeler eklemenizi sağlar.', 'extra-tab-modulu', 650.00, 800.00, 20.00, 1, 100);
