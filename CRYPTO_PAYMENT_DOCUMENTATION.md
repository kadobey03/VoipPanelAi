# TRON TRC20 USDT Otomatik Ödeme Sistemi Dokümantasyonu

## 📋 Genel Bakış

Bu sistem, VoIP paneline TRON TRC20 USDT ile otomatik bakiye yükleme özelliği ekler. Kullanıcılar cryptocurrency ile ödeme yapabilir ve sistem otomatik olarak ödemeleri izleyip onaylar.

## 🏗️ Sistem Mimarisi

### Ana Bileşenler

1. **TronClient.php** - TRON blockchain API entegrasyonu
2. **TronWallet.php** - Wallet yönetim sistemi
3. **CryptoSecurity.php** - Güvenlik ve doğrulama sistemi
4. **crypto_payment_monitor.php** - Otomatik ödeme izleme cron job'ı
5. **GroupController** - Ödeme akışı yönetimi
6. **TopupController** - Ödeme onaylama sistemi

### Veritabanı Tabloları

- `crypto_wallets` - Oluşturulan wallet adresleri
- `crypto_payments` - Cryptocurrency ödeme talepleri
- `security_logs` - Güvenlik logları
- `crypto_blacklist` - Kara liste adresleri
- `rate_limits` - Rate limiting verileri

## 🚀 Kurulum

### 1. Bağımlılıkları Yükleyin

```bash
composer install
```

Gerekli PHP paketleri:
- `guzzlehttp/guzzle ^7.0`
- `phpseclib/phpseclib ^3.0`
- `kornrunner/secp256k1-php ^2.0`

### 2. Veritabanı Şemasını Güncelleyin

```sql
-- crypto_schema.sql dosyasını çalıştırın
mysql -u username -p database_name < crypto_schema.sql
```

### 3. Ayarları Yapılandırın

`settings` tablosuna aşağıdaki ayarları ekleyin:

```sql
INSERT INTO settings (name, value) VALUES 
('crypto_tron_api_key', 'YOUR_TRONGRID_API_KEY'),
('crypto_usdt_min_amount', '1.00'),
('crypto_usdt_max_amount', '10000.00'),
('crypto_payment_timeout_hours', '24'),
('crypto_required_confirmations', '19');
```

### 4. Ödeme Yöntemini Ekleyin

```sql
INSERT INTO payment_methods (name, method_type, details, fee_percent, fee_fixed, active) 
VALUES ('USDT TRC20', 'cryptocurrency', 'Tether USDT via TRON TRC20 Network', 0.00, 0.00, 1);
```

### 5. Cron Job'ı Kurun

```bash
# Her 2 dakikada bir çalıştır
*/2 * * * * /usr/bin/php /path/to/crypto_payment_monitor.php
```

## 💳 Ödeme Akışı

### Kullanıcı Perspektifi

1. **Ödeme Talebi Oluşturma**
   - `/groups/topup?id=X` sayfasına git
   - "USDT TRC20" ödeme yöntemini seç
   - Tutarı gir ve formu gönder

2. **Ödeme Bilgileri Gösterimi**
   - Sistem otomatik TRON adresi oluşturur
   - QR kod ve adres bilgileri gösterilir
   - 24 saat geçerlilik süresi

3. **Ödeme Yapma**
   - Gösterilen adrese USDT TRC20 gönder
   - Sistem otomatik olarak ödemeleri izler
   - 19+ onay sonrası bakiye yüklenir

### Sistem Perspektifi

1. **Wallet Oluşturma**
   ```php
   $tronWallet = new TronWallet();
   $wallet = $tronWallet->getOrCreateWalletForGroup($groupId);
   ```

2. **Ödeme İzleme**
   ```php
   // Cron job her 2 dakikada çalışır
   $monitor = new CryptoPaymentMonitor();
   $monitor->monitor();
   ```

3. **Otomatik Onaylama**
   ```php
   // Yeterli onay sonrası otomatik bakiye yükleme
   $this->confirmPayment($payment, $transaction);
   ```

## 🔐 Güvenlik Özellikleri

### Rate Limiting
- Saatlik maksimum: 10 ödeme talebi
- Günlük maksimum: 50 ödeme talebi
- 10 dakikada 3+ talep = engelleme

### Miktar Kontrolleri
- Minimum: 1.00 USDT (yapılandırılabilir)
- Maksimum: 10,000.00 USDT (yapılandırılabilir)
- Şüpheli miktar eşiği: 1,000 USDT

### Adres Doğrulama
- TRON adres formatı kontrolü
- Kara liste kontrolü
- Checksum doğrulaması

### Veri Şifreleme
- Private key'ler AES-256-CBC ile şifrelenir
- Hassas veriler güvenli şekilde saklanır

## 📊 API Endpoints

### Cryptocurrency Kontrolleri

```php
// Ödeme durumu kontrolü (AJAX)
GET /topups/crypto/status?payment_id=123

// Transaction detayları
GET /topups/crypto/transaction?tx_hash=xxx

// Manuel onaylama (admin)
POST /topups/crypto/approve
```

### Transaction Geçmişi

```php
// Genişletilmiş transaction listesi
GET /transactions

// Crypto detayları
GET /transactions/crypto?id=123

// CSV export
GET /transactions/export
```

## 🧪 Test Etme

### Otomatik Testler

```bash
php test_crypto_payment.php
```

Test kapsamı:
- Veritabanı şema kontrolü
- TRON Client fonksiyonları
- Wallet oluşturma
- Güvenlik kontrolleri
- Payment workflow

### Manuel Test Senaryoları

1. **Normal Ödeme Akışı**
   - Küçük miktar (5 USDT) ödeme testi
   - Büyük miktar (500 USDT) ödeme testi
   - Süresi dolan ödeme testi

2. **Güvenlik Testleri**
   - Rate limiting testi
   - Geçersiz adres testi
   - Yanlış miktar testi

3. **Hata Senaryoları**
   - Network bağlantı hatası
   - API key hatası
   - Veritabanı hatası

## 📝 Loglar ve İzleme

### Log Dosyaları

- `crypto_payment_monitor.log` - Cron job logları
- `crypto_security.log` - Güvenlik olayları
- `apache/nginx error.log` - Sistem hataları

### Veritabanı İzleme

```sql
-- Pending ödemeler
SELECT * FROM crypto_payments WHERE status = 'pending';

-- Son güvenlik olayları
SELECT * FROM security_logs ORDER BY created_at DESC LIMIT 50;

-- Transaction özeti
SELECT 
    COUNT(*) as total_payments,
    SUM(amount_received) as total_amount,
    AVG(amount_received) as avg_amount
FROM crypto_payments 
WHERE status = 'completed' 
AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## ⚠️ Troubleshooting

### Yaygın Sorunlar

1. **Cron Job Çalışmıyor**
   ```bash
   # Cron log kontrol et
   tail -f /var/log/cron
   
   # Manuel test
   php crypto_payment_monitor.php
   ```

2. **Ödemeler Onaylanmıyor**
   ```sql
   -- Pending ödemeleri kontrol et
   SELECT cp.*, cw.address 
   FROM crypto_payments cp 
   JOIN crypto_wallets cw ON cp.wallet_id = cw.id 
   WHERE cp.status = 'pending';
   ```

3. **TRON API Hataları**
   ```php
   // API key kontrolü
   $tronClient = new TronClient('YOUR_API_KEY');
   $result = $tronClient->getCurrentBlock();
   var_dump($result);
   ```

### Performans İyileştirmeleri

1. **Database Index'leri**
   ```sql
   CREATE INDEX idx_crypto_payments_status ON crypto_payments(status, created_at);
   CREATE INDEX idx_security_logs_user ON security_logs(user_id, created_at);
   ```

2. **Cron Job Optimizasyonu**
   - Sadece aktif ödemeleri işle
   - Batch processing kullan
   - Memory usage'ı izle

## 🔧 Bakım ve Güncellemeler

### Düzenli Bakım

- **Günlük**: Log dosyalarını kontrol et
- **Haftalık**: Pending ödemeleri gözden geçir
- **Aylık**: Transaction raporları oluştur

### Sistem Güncellemeleri

1. **TRON API Değişiklikleri**
   - TronGrid API versiyonunu güncelle
   - Yeni metodları entegre et

2. **Güvenlik Güncellemeleri**
   - PHP bağımlılıklarını güncelle
   - Yeni tehdit pattern'ları ekle

## 📞 Destek ve İletişim

### Hata Raporlama

1. **Log Dosyalarını Toplayın**
2. **Hata Reproducible Adımları**
3. **Sistem Bilgileri (PHP, MySQL versiyonları)**

### Monitoring Alarmları

```bash
# Disk alanı
df -h

# Memory kullanımı  
free -m

# MySQL bağlantıları
mysqladmin status

# Cron job durumu
systemctl status crond
```

---

## 📈 Başarı Metrikleri

Sistem başarıyla implement edildi ve aşağıdaki özellikleri sağlar:

✅ **Tam Otomatik Ödeme İşleme**
✅ **Güvenli Wallet Yönetimi** 
✅ **Rate Limiting ve Güvenlik**
✅ **Kapsamlı Logging**
✅ **Kullanıcı Dostu Arayüz**
✅ **Admin Panel Entegrasyonu**
✅ **Test Coverage**

**Sistem kullanıma hazır!** 🚀

---

**Son Güncelleme:** 2025-11-10  
**Versiyon:** 1.0.0  
**Geliştirici:** Roo (Claude Sonnet 4)