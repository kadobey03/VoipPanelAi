-- Foreign Key Constraint Fix Migration
-- Bu script mevcut crypto_payments tablosunu günceller

-- 1. Foreign key constraint'ini kaldır
ALTER TABLE crypto_payments DROP FOREIGN KEY crypto_payments_ibfk_3;

-- 2. wallet_id sütununu kaldır (artık kullanmıyoruz)
ALTER TABLE crypto_payments DROP COLUMN wallet_id;

-- 3. Şema güncel hali ile uyumlu olsun diye kontrol et
DESCRIBE crypto_payments;