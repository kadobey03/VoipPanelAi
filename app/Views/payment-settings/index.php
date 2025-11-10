<?php $title='Ödeme Ayarları - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-credit-card text-green-600"></i> Ödeme Ayarları</h1>
  </div>
  <form method="post" class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-6">
    
    <!-- Cryptocurrency Settings Section -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">💎 Cryptocurrency Ayarları</h3>
      <div class="grid md:grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">💰 USDT TRC20 Wallet Adresi</label>
          <input type="text" name="crypto_usdt_wallet" value="<?= htmlspecialchars($settings['crypto_usdt_wallet'] ?? '') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900 font-mono text-sm"
                 placeholder="TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t (Örnek TRON adresi)">
          <p class="text-xs text-gray-500 mt-1">🔹 Tüm USDT TRC20 ödemeleri bu adrese yönlendirilecek</p>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">⏱️ Ödeme Geçerlilik Süresi (dakika)</label>
            <input type="number" name="crypto_payment_timeout" value="<?= htmlspecialchars($settings['crypto_payment_timeout'] ?? '10') ?>" 
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="60">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">✅ Gereken Onay Sayısı</label>
            <input type="number" name="crypto_required_confirmations" value="<?= htmlspecialchars($settings['crypto_required_confirmations'] ?? '19') ?>" 
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="50">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">🔄 Kontrol Aralığı (saniye)</label>
            <input type="number" name="crypto_check_interval" value="<?= htmlspecialchars($settings['crypto_check_interval'] ?? '10') ?>" 
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="5" max="300">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">🔑 TronGrid API Key (opsiyonel)</label>
          <input type="text" name="crypto_tron_api_key" value="<?= htmlspecialchars($settings['crypto_tron_api_key'] ?? '') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900"
                 placeholder="TronGrid API anahtarı (blockchain monitoring için)">
          <p class="text-xs text-gray-500 mt-1">🔹 Otomatik blockchain monitoring için gerekli</p>
        </div>
      </div>
    </div>

    <!-- Payment Security Settings -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">🛡️ Ödeme Güvenliği</h3>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">📊 Saatlik Ödeme Limiti</label>
          <input type="number" name="payment_hourly_limit" value="<?= htmlspecialchars($settings['payment_hourly_limit'] ?? '10') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="100">
          <p class="text-xs text-gray-500 mt-1">Bir kullanıcının saatte yapabileceği maksimum ödeme sayısı</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">📈 Günlük Ödeme Limiti</label>
          <input type="number" name="payment_daily_limit" value="<?= htmlspecialchars($settings['payment_daily_limit'] ?? '50') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="1000">
          <p class="text-xs text-gray-500 mt-1">Bir kullanıcının günde yapabileceği maksimum ödeme sayısı</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">💵 Minimum Ödeme Tutarı</label>
          <input type="number" name="payment_min_amount" value="<?= htmlspecialchars($settings['payment_min_amount'] ?? '1.00') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="0.01" step="0.01">
          <p class="text-xs text-gray-500 mt-1">USDT cinsinden minimum ödeme tutarı</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">💰 Maksimum Ödeme Tutarı</label>
          <input type="number" name="payment_max_amount" value="<?= htmlspecialchars($settings['payment_max_amount'] ?? '10000.00') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" step="0.01">
          <p class="text-xs text-gray-500 mt-1">USDT cinsinden maksimum ödeme tutarı</p>
        </div>
      </div>
    </div>

    <!-- Advanced Settings -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2">⚙️ Gelişmiş Ayarlar</h3>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">🔗 TRON Network Endpoint</label>
          <input type="text" name="tron_network_endpoint" value="<?= htmlspecialchars($settings['tron_network_endpoint'] ?? 'https://api.trongrid.io') ?>" 
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <p class="text-xs text-gray-500 mt-1">TRON blockchain API endpoint</p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">📱 Wallet Blacklist Check</label>
          <select name="wallet_blacklist_enabled" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
            <option value="1" <?= ($settings['wallet_blacklist_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Etkin</option>
            <option value="0" <?= ($settings['wallet_blacklist_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Kapalı</option>
          </select>
          <p class="text-xs text-gray-500 mt-1">Şüpheli wallet adreslerini kontrol et</p>
        </div>
      </div>
    </div>

    <button type="submit" name="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">
      💾 Ödeme Ayarlarını Kaydet
    </button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>