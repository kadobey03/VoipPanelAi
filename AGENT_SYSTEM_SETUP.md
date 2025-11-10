# 🤖 Agent Satın Alma Sistemi - Kurulum ve Test Rehberi

## 📋 Sistem Özeti

Agent satın alma sistemi başarıyla implement edildi ve aşağıdaki özellikler tamamen çalışır durumda:

### ✅ Tamamlanan Özellikler
1. **Agent Dropdown Menüsü** - "Agentler" ve "Agent Satın Al" seçenekleri
2. **Agent Ürün Yönetimi** - Admin panel ile ürün ekleme/düzenleme
3. **Otomatik Bakiye Düşme** - Satın alma işleminde bakiyeden otomatik düşme
4. **Abonelik Sistemi** - Aylık ödemeli agent abonelikleri
5. **Telegram Bildirimleri** - Tüm işlemler için real-time bildirimler
6. **Askıya Alma Sistemi** - Ödeme yapılmaması durumunda otomatik askıya alma
7. **Admin Yönetim Paneli** - Abonelik yönetimi ve manuel işlemler

## 🗄️ Veritabanı Kurulumu

### 1. Schema Dosyasını Çalıştırın
```sql
-- agent_purchase_schema.sql dosyasını MySQL'de çalıştırın
mysql -u username -p database_name < agent_purchase_schema.sql
```

### 2. Örnek Ürünler
Sistem otomatik olarak aşağıdaki örnek ürünleri oluşturacak:
- **0905 Li Numara** - Tek Kullanıcı - $250 (Tek Seferlik)
- **0905 Li Sabit Geri Aranabilen Numara** - Tek Kullanıcı - $350 + $100/ay

## 🔧 Sistem Konfigürasyonu

### 1. Telegram Bot Ayarları
`.env` dosyasına ekleyin:
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

### 2. Cron Job Kurulumu
```bash
# crontab -e ile aşağıdaki satırı ekleyin (günlük çalıştırma)
0 2 * * * /usr/bin/php /path/to/your/project/cron_subscriptions.php
```

## 📱 Sistem Kullanımı

### Kullanıcı İşlemleri
1. **Agent Satın Alma:**
   - Menü: Agentler → Agent Satın Al
   - Ürün seçimi ve ödeme tipi belirleme
   - Bakiye kontrolü ve otomatik düşme

2. **Abonelik Takibi:**
   - Otomatik aylık ödemeler
   - Bakiye yetersizliğinde askıya alma

### Admin İşlemleri
1. **Ürün Yönetimi:**
   - Menü: Agentler → Agent Satın Al (Admin görünümü)
   - "Ürünleri Yönet" butonu
   - Ürün ekleme, düzenleme, silme

2. **Abonelik Yönetimi:**
   - Menü: Agentler → Abonelik Yönetimi
   - Vadesi geçmiş ödemeler görüntüleme
   - Manuel ödeme işleme
   - Agent askıya alma/aktifleştirme

## 🔔 Telegram Bildirim Türleri

### Otomatik Bildirimler
- ✅ **Agent Satın Alımı** - Yeni satın alma işlemi
- 💳 **Abonelik Ödemesi** - Başarılı aylık ödeme
- ❌ **Ödeme Başarısız** - Yetersiz bakiye durumu
- ⏸️ **Agent Askıya Alındı** - Ödeme yapılmama durumu
- 🔄 **Agent Reaktifleştirildi** - Manuel ödeme sonrası
- 👨‍💼 **Admin İşlemi** - Manuel abonelik işlemleri
- 📊 **Günlük Rapor** - Sistem performans raporu

## 🚀 Test Senaryoları

### 1. Temel Satın Alma Testi
```php
// Test kullanıcısı ile satın alma işlemi
1. Kullanıcı girişi yapın
2. Agentler → Agent Satın Al menüsüne gidin
3. Bir ürün seçip satın alma işlemini tamamlayın
4. Telegram bildirimi kontrolü yapın
5. Bakiye düşümünü kontrol edin
```

### 2. Abonelik Sistemi Testi
```php
// Aylık abonelik testi
1. Aylık abonelik ürünü satın alın
2. Cron job'ı manuel çalıştırın: php cron_subscriptions.php
3. Ödeme işleminin doğru çalıştığını kontrol edin
4. Telegram bildirimi geldiğini kontrol edin
```

### 3. Yetersiz Bakiye Testi
```php
// Bakiye yetersizliği senaryosu
1. Kullanıcı bakiyesini düşürün
2. Cron job çalıştırın
3. Agent'ın askıya alındığını kontrol edin
4. Telegram uyarı mesajını kontrol edin
```

## 📊 İstatistikler ve Raporlama

### Günlük Rapor İçeriği
- 📈 Toplam İşlem Sayısı
- ✅ Başarılı Ödeme Sayısı  
- ❌ Başarısız Ödeme Sayısı
- 💰 Toplam Gelir Miktarı
- ⏸️ Askıya Alınan Agent Sayısı

### Admin Dashboard Metrikleri
- Aktif abonelik sayısı
- Vadesi geçmiş ödemeler
- Bu ay toplam geliri
- Bekleyen işlemler

## 🔒 Güvenlik Özellikleri

- ✅ CSRF Token koruması
- ✅ Admin yetkilendirme kontrolü
- ✅ SQL Injection koruması (PDO kullanımı)
- ✅ Transaction güvenliği
- ✅ Input validation
- ✅ XSS koruması

## 🛠️ Bakım ve Monitöring

### Günlük Kontroller
1. Cron job loglarını kontrol edin
2. Telegram bildirimlerini izleyin
3. Başarısız ödemeleri takip edin
4. Sistem error loglarını inceleyin

### Aylık Bakım
1. Veritabanı performans optimizasyonu
2. Log dosyası temizliği
3. İstatistik raporları oluşturma
4. Backup kontrolü

## 📞 Destek ve Sorun Giderme

### Yaygın Sorunlar
1. **Telegram bildirimi gelmiyor:**
   - Bot token'ının doğru olup olmadığını kontrol edin
   - Chat ID'nin doğru olup olmadığını kontrol edin
   - İnternet bağlantısını kontrol edin

2. **Cron job çalışmıyor:**
   - PHP path'ini kontrol edin
   - Dosya permission'larını kontrol edin
   - Crontab syntax'ını kontrol edin

3. **Satın alma işlemi başarısız:**
   - Kullanıcı bakiyesini kontrol edin
   - Veritabanı bağlantısını kontrol edin
   - Error log'ları inceleyin

## ✨ Sistem Hazır!

Agent satın alma sistemi tamamen implement edildi ve production ortamında kullanıma hazır. Tüm özellikler test edilmiş ve çalışır durumda.

**Son Güncelleme:** 10.11.2025
**Sistem Versiyonu:** v1.0
**Durum:** ✅ Production Ready