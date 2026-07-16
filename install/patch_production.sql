-- Canlıya almadan önce çalıştırın (değerleri kendi bilgilerinizle değiştirin)
-- mysql -u root -p fshop < install/patch_production.sql

UPDATE `settings` SET `value` = 'https://www.ornekdomain.com/' WHERE `title` = 'DOMAIN';
UPDATE `settings` SET `value` = '/' WHERE `title` = 'FOLDER';
UPDATE `settings` SET `value` = 'GÜÇLÜ_RASTGELE_TOKEN' WHERE `title` = 'SHOP_TOKEN';

-- Admin şifresini değiştirmek için PHP ile hash üretin:
-- php -r "echo password_hash('YeniSifre123', PASSWORD_DEFAULT);"
-- UPDATE admins SET password = 'HASH_BURAYA' WHERE email = 'admin@fyazilim.com';
