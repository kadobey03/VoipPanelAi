<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grup Bakiye Yükle - PapaM VoIP Panel</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4 max-w-lg">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Bakiye Yükle - <?= htmlspecialchars($group['name']) ?></h1>
      <a href="<?= \App\Helpers\Url::to('/topups') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Geri</a>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    
    <!-- Cryptocurrency Payment Info -->
    <?php if (isset($cryptoPaymentData) && $cryptoPaymentData): ?>
    <div class="max-w-lg mx-auto">
      <!-- Payment Header -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-t-lg">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-xl font-bold">💎 USDT TRC20 Ödeme</h2>
          <div id="paymentStatus" class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 border border-yellow-300">
            🔄 Ödeme Bekleniyor
          </div>
        </div>
        <p class="text-blue-100 text-sm">Güvenli blockchain ödeme sistemi</p>
      </div>

      <!-- Payment Amount -->
      <div class="bg-white dark:bg-gray-800 p-6 border-x border-gray-200 dark:border-gray-700">
        <div class="text-center mb-6">
          <div class="text-3xl font-bold text-green-600 mb-2">
            <?= number_format($cryptoPaymentData['amount'], 2) ?> USDT
          </div>
          <div class="text-sm text-gray-600 dark:text-gray-400">TRC20 Network</div>
        </div>

        <!-- Wallet Address -->
        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            📱 Wallet Adresi
          </label>
          <div class="flex gap-2">
            <input type="text"
                   id="walletAddress"
                   value="<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>"
                   class="flex-1 p-3 text-sm font-mono border-2 border-gray-300 rounded-lg bg-gray-50 dark:bg-gray-700 focus:border-blue-500"
                   readonly>
            <button onclick="copyAddress()"
                    class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
              📋 Kopyala
            </button>
          </div>
        </div>

        <!-- QR Code Section -->
        <div class="text-center mb-6">
          <div class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">📱 QR Kod ile Ödeme</div>
          <div class="inline-block p-4 bg-white rounded-lg shadow-lg border">
            <div id="qrcode"></div>
          </div>
          <div class="mt-2 text-xs text-gray-500">QR kodu okutarak kolayca ödeme yapın</div>
        </div>
      </div>

      <!-- Payment Status -->
      <div class="bg-gray-50 dark:bg-gray-900 p-6 border-x border-gray-200 dark:border-gray-700">
        <div id="paymentProgress" class="mb-4">
          <!-- Progress bar will be added via JS -->
        </div>
        
        <!-- Payment Instructions -->
        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
          <div class="flex items-start gap-2">
            <span class="text-green-500 mt-1">✅</span>
            <span>Sadece USDT TRC20 gönderin</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-blue-500 mt-1">⏱</span>
            <span>19+ onay gerekli (~1-3 dakika)</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-purple-500 mt-1">⚡</span>
            <span>Otomatik bakiye yükleme</span>
          </div>
        </div>
      </div>

      <!-- Timer & Expiry -->
      <div class="bg-gradient-to-r from-orange-400 to-red-500 text-white p-4 rounded-b-lg">
        <div class="flex items-center justify-between">
          <div class="text-sm">
            <div class="font-semibold">Kalan Süre:</div>
            <div id="countdown" class="text-orange-100 font-mono"></div>
          </div>
          <div class="text-right text-sm">
            <div class="font-semibold">Son Geçerlilik:</div>
            <div id="expiryTime" class="text-orange-100">--:--</div>
          </div>
        </div>
        
        <!-- Cancel Button -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-b-lg border-t border-gray-200 dark:border-gray-700">
          <button onclick="showCancelModal()"
                  class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
            ❌ Ödemeyi İptal Et
          </button>
        </div>
      </div>

    <!-- Success Modal (Hidden by default) -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="text-center">
          <div class="text-6xl mb-4">🎉</div>
          <h3 class="text-2xl font-bold text-green-600 mb-2">Ödeme Alındı!</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">USDT transferiniz başarıyla onaylandı ve bakiyeniz güncellendi.</p>
          <button onclick="location.reload()" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Devam Et
          </button>
        </div>
      </div>
    </div>
    
    <!-- Cancel Confirmation Modal (Hidden by default) -->
    <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="text-center">
          <div class="text-6xl mb-4">⚠️</div>
          <h3 class="text-xl font-bold text-red-600 mb-2">Ödemeyi İptal Et</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6">Bu ödeme talebini iptal etmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
          <div class="flex gap-3">
            <button onclick="hideCancelModal()"
                    class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-medium">
              İptal
            </button>
            <button onclick="confirmCancel()"
                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
              Evet, İptal Et
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Cancel Success Modal (Hidden by default) -->
    <div id="cancelSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="text-center">
          <div class="text-6xl mb-4">✅</div>
          <h3 class="text-xl font-bold text-green-600 mb-2">İptal Edildi</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">Ödeme talebiniz başarıyla iptal edildi.</p>
          <button onclick="location.href='/groups/topup?id=<?= $group['id'] ?>'"
                  class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Tamam
          </button>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="mb-3">Mevcut Bakiye: <strong><?= number_format((float)$group['balance'],2) ?></strong></div>
    
    <?php if (!isset($cryptoPaymentData) || !$cryptoPaymentData): ?>
    <form method="post" enctype="multipart/form-data" class="space-y-3 bg-white dark:bg-gray-800 p-4 rounded shadow">
      <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='superadmin'): ?>
        <?php if (!isset($methods) || !is_array($methods) || count($methods)===0) { $db = \App\Helpers\DB::conn(); $methods=[]; if($r=$db->query('SELECT id,name,method_type,fee_percent,fee_fixed FROM payment_methods WHERE active=1 ORDER BY id DESC')){ while($row=$r->fetch_assoc()){$methods[]=$row;} } } ?>
        <div>
          <label class="block text-sm mb-1">Ödeme Yöntemi</label>
          <select id="method_id" name="method_id" class="w-full border rounded p-2 bg-white dark:bg-gray-800" required>
            <?php foreach ($methods as $m): ?>
              <option value="<?= (int)$m['id'] ?>" data-name="<?= htmlspecialchars($m['name']) ?>" data-p="<?= (float)$m['fee_percent'] ?>" data-f="<?= (float)$m['fee_fixed'] ?>"><?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['method_type']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="method" id="method_name" value="">
        </div>
      <?php else: ?>
        <input type="hidden" name="method" value="manual">
      <?php endif; ?>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']!=='superadmin'): ?>
        <div class="text-xs text-slate-500">Not: Talebiniz onaylanınca grup bakiyenize yansır.</div>
      <?php endif; ?>
      <div>
        <label class="block text-sm mb-1">Tutar</label>
        <input id="amount" type="number" step="0.01" min="0.01" name="amount" class="w-full border rounded p-2 bg-white dark:bg-gray-800" required>
      </div>
      <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='superadmin'): ?>
      <div id="feeSection" class="bg-slate-50 dark:bg-slate-900 p-3 rounded text-sm">
        <div>Komisyon: <span id="feeText">0</span></div>
        <div>Ödenecek Toplam: <strong id="totalText">0</strong></div>
      </div>
      <div id="noteSection">
        <label class="block text-sm mb-1">Açıklama (opsiyonel)</label>
        <input name="note" class="w-full border rounded p-2 bg-white dark:bg-gray-800" placeholder="Not/ek açıklama">
      </div>
      <div id="receiptSection">
        <label class="block text-sm mb-1">Dekont (opsiyonel)</label>
        <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full border rounded p-2 bg-white dark:bg-gray-800">
      </div>
      <?php endif; ?>
      <button id="submitBtn" class="w-full bg-blue-600 text-white rounded p-2">Gönder</button>
    </form>
    <?php endif; ?>
    
    <div class="mt-4 text-sm">
      <a class="text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/topups') ?>">Bakiye Yükleme Talepleri</a>
    </div>
  </div>
  
  <!-- JavaScript -->
  <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='superadmin'): ?>
  <script>
    (function(){
      // Hide unnecessary sections for all payment gateways (auto payment)
      var feeSection = document.getElementById('feeSection');
      var noteSection = document.getElementById('noteSection');
      var receiptSection = document.getElementById('receiptSection');
      var submitBtn = document.getElementById('submitBtn');
      
      if (feeSection) feeSection.style.display = 'none';
      if (noteSection) noteSection.style.display = 'none';
      if (receiptSection) receiptSection.style.display = 'none';
      if (submitBtn) submitBtn.textContent = 'İlerle';
      
      function calc(){
        var sel=document.getElementById('method_id'); if(!sel) return;
        var p=parseFloat(sel.options[sel.selectedIndex].getAttribute('data-p')||'0');
        var f=parseFloat(sel.options[sel.selectedIndex].getAttribute('data-f')||'0');
        var name=sel.options[sel.selectedIndex].getAttribute('data-name')||'';
        var amt=parseFloat(document.getElementById('amount').value||'0');
        var fee= (amt * (p/100.0)) + f; var total = amt + fee;
        
        // For internal calculation (even if not displayed)
        if (document.getElementById('feeText')) {
          document.getElementById('feeText').textContent = fee.toFixed(2) + ' ('+p.toFixed(2)+'% + '+f.toFixed(2)+')';
        }
        if (document.getElementById('totalText')) {
          document.getElementById('totalText').textContent = total.toFixed(2);
        }
        
        var mn=document.getElementById('method_name'); if(mn) mn.value=name;
      }
      var el=document.getElementById('method_id'); if(el){ el.addEventListener('change',calc); }
      var am=document.getElementById('amount'); if(am){ am.addEventListener('input',calc); }
      calc();
    })();
  </script>
  <?php endif; ?>
  
  <!-- Cryptocurrency JavaScript -->
  <?php if (isset($cryptoPaymentData) && $cryptoPaymentData): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
  <script>
    let paymentCheckInterval;
    let countdownInterval;
    
    // Copy address to clipboard
    function copyAddress() {
      const addressInput = document.getElementById('walletAddress');
      addressInput.select();
      addressInput.setSelectionRange(0, 99999);
      
      navigator.clipboard.writeText(addressInput.value).then(function() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '✅ Kopyalandı!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        
        setTimeout(function() {
          btn.innerHTML = originalText;
          btn.classList.remove('bg-green-600', 'hover:bg-green-700');
          btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
      }).catch(function() {
        alert('Kopyalama başarısız. Lütfen manuel olarak kopyalayın.');
      });
    }
    
    // Initialize everything when page loads
    document.addEventListener('DOMContentLoaded', function() {
      generateQRCode();
      startPaymentMonitoring();
      startCountdown();
      createProgressBar();
    });
    
    // Generate QR Code
    function generateQRCode() {
      const address = '<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>';
      const qrContainer = document.getElementById('qrcode');
      
      try {
        // Create QR code using qrcode-generator library
        const qr = qrcode(0, 'M');
        qr.addData(address);
        qr.make();
        
        // Generate HTML table and style it
        const qrHTML = qr.createImgTag(4, 8); // cellSize=4, margin=8
        qrContainer.innerHTML = qrHTML;
        
        // Style the generated image
        const img = qrContainer.querySelector('img');
        if (img) {
          img.style.width = '200px';
          img.style.height = '200px';
          img.style.border = '1px solid #ddd';
          img.style.borderRadius = '8px';
        }
      } catch (error) {
        console.error('QR Code generation failed:', error);
        qrContainer.innerHTML = '<div class="p-4 text-center text-gray-500 border border-gray-300 rounded-lg">QR kod oluşturulamadı<br><small>Wallet adresini manuel kopyalayın</small></div>';
      }
    }
    
    // Create progress bar
    function createProgressBar() {
      const progressContainer = document.getElementById('paymentProgress');
      progressContainer.innerHTML = `
        <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Ödeme Durumu</div>
        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
          <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500" style="width: 25%"></div>
        </div>
        <div class="mt-1 text-xs text-gray-500">Blockchain izleme aktif...</div>
      `;
    }
    
    // Start payment monitoring
    function startPaymentMonitoring() {
      paymentCheckInterval = setInterval(checkPaymentStatus, 10000); // Every 10 seconds
      checkPaymentStatus(); // Check immediately
    }
    
    // Check payment status via AJAX
    function checkPaymentStatus() {
      const paymentId = <?= $cryptoPaymentData['payment_id'] ?? 0 ?>;
      const address = '<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>';
      
      fetch('/api/check-payment-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          payment_id: paymentId,
          wallet_address: address
        })
      })
      .then(response => response.json())
      .then(data => {
        updatePaymentStatus(data);
      })
      .catch(error => {
        console.log('Payment check error:', error);
        // Continue monitoring even if there's an error
      });
    }
    
    // Update payment status UI
    function updatePaymentStatus(data) {
      const statusElement = document.getElementById('paymentStatus');
      const progressBar = document.getElementById('progressBar');
      
      if (data.status === 'confirmed') {
        statusElement.innerHTML = '✅ Ödeme Onaylandı';
        statusElement.className = 'px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 border border-green-300 text-green-700';
        progressBar.style.width = '100%';
        
        clearInterval(paymentCheckInterval);
        clearInterval(countdownInterval);
        
        // Show success modal
        document.getElementById('successModal').classList.remove('hidden');
        
      } else if (data.status === 'pending' && data.confirmations > 0) {
        const progress = Math.min((data.confirmations / 19) * 100, 90);
        statusElement.innerHTML = `⏳ ${data.confirmations}/19 Onay`;
        statusElement.className = 'px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 border border-yellow-300 text-yellow-700';
        progressBar.style.width = `${25 + progress}%`;
        
      } else if (data.status === 'detected') {
        statusElement.innerHTML = '🔍 Transfer Tespit Edildi';
        statusElement.className = 'px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 border border-blue-300 text-blue-700';
        progressBar.style.width = '50%';
        
      } else {
        // Still waiting
        statusElement.innerHTML = '🔄 Ödeme Bekleniyor';
        statusElement.className = 'px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 border border-yellow-300';
      }
    }
    
    // Start countdown timer (using real payment creation time)
    function startCountdown() {
      const createdAtStr = '<?= $cryptoPaymentData['created_at'] ?? '' ?>';
      const timeoutMinutes = <?= $cryptoPaymentData['timeout_minutes'] ?? 10 ?>;
      
      if (!createdAtStr) {
        console.error('Payment creation time not available');
        return;
      }
      
      // Parse payment creation time (server time)
      const paymentCreatedAt = new Date(createdAtStr).getTime();
      const timeoutMs = timeoutMinutes * 60 * 1000; // Convert to milliseconds
      const paymentExpiryTime = paymentCreatedAt + timeoutMs;
      
      // Calculate and display expiry time
      const expiryTime = new Date(paymentExpiryTime);
      const expiryTimeElement = document.getElementById('expiryTime');
      if (expiryTimeElement) {
        expiryTimeElement.innerHTML = expiryTime.toLocaleTimeString('tr-TR', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: false
        });
      }
      
      countdownInterval = setInterval(function() {
        const now = new Date().getTime(); // Current client time
        const timeLeft = Math.max(0, paymentExpiryTime - now); // Remaining time from expiry
        
        if (timeLeft > 0) {
          const minutes = Math.floor(timeLeft / (1000 * 60));
          const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
          
          document.getElementById('countdown').innerHTML =
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        } else {
          document.getElementById('countdown').innerHTML = '00:00';
          document.getElementById('countdown').style.color = '#ef4444';
          clearInterval(countdownInterval);
          clearInterval(paymentCheckInterval);
          
          // Show expiry message
          document.getElementById('paymentStatus').innerHTML = '⏰ Süre Doldu';
          document.getElementById('paymentStatus').className = 'px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 border border-red-300 text-red-700';
        }
      }, 1000);
    }
    
    // Cancel payment functions
    function showCancelModal() {
      document.getElementById('cancelModal').classList.remove('hidden');
    }
    
    function hideCancelModal() {
      document.getElementById('cancelModal').classList.add('hidden');
    }
    
    function confirmCancel() {
      const paymentId = <?= $cryptoPaymentData['payment_id'] ?? 0 ?>;
      const groupId = <?= $group['id'] ?? 0 ?>;
      
      // Hide cancel modal
      hideCancelModal();
      
      // Send cancel request
      fetch('/groups/cancel-crypto-payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          payment_id: paymentId,
          group_id: groupId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Stop timers
          clearInterval(paymentCheckInterval);
          clearInterval(countdownInterval);
          
          // Show success modal
          document.getElementById('cancelSuccessModal').classList.remove('hidden');
        } else {
          alert('İptal işlemi başarısız: ' + (data.error || 'Bilinmeyen hata'));
        }
      })
      .catch(error => {
        console.error('Cancel error:', error);
        alert('İptal işlemi sırasında hata oluştu');
      });
    }
  </script>
  <?php endif; ?>
</body>
</html>

