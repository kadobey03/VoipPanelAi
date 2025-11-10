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
      <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Geri</a>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    
    <!-- Cryptocurrency Payment Info -->
    <?php if (isset($cryptoPaymentData) && $cryptoPaymentData): ?>
    <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
      <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-3">
        🔸 USDT TRC20 Ödeme Bilgileri
      </h3>
      
      <div class="grid gap-3">
        <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded">
          <span class="text-sm font-medium">Ödeme Tutarı:</span>
          <span class="font-bold text-green-600"><?= number_format($cryptoPaymentData['amount'], 2) ?> <?= htmlspecialchars($cryptoPaymentData['currency']) ?></span>
        </div>
        
        <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded">
          <span class="text-sm font-medium">Ağ:</span>
          <span class="font-mono text-sm"><?= htmlspecialchars($cryptoPaymentData['network']) ?></span>
        </div>
        
        <div class="p-3 bg-white dark:bg-gray-800 rounded">
          <label class="block text-sm font-medium mb-2">Wallet Adresi:</label>
          <div class="flex items-center space-x-2">
            <input type="text"
                   id="walletAddress"
                   value="<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>"
                   class="flex-1 p-2 text-xs font-mono border rounded bg-gray-50 dark:bg-gray-700"
                   readonly>
            <button type="button"
                    onclick="copyAddress()"
                    class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
              Kopyala
            </button>
          </div>
        </div>
        
        <!-- QR Code -->
        <div class="text-center p-3 bg-white dark:bg-gray-800 rounded">
          <div class="mb-2 text-sm font-medium">QR Kod ile Ödeme:</div>
          <div id="qrcode" class="inline-block p-2 bg-white rounded"></div>
        </div>
        
        <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
          <div class="text-sm text-yellow-800 dark:text-yellow-200">
            ⏱ <strong>Önemli:</strong> Bu ödeme 24 saat geçerlidir.
            <br>Son geçerlilik: <?= date('d.m.Y H:i', strtotime($cryptoPaymentData['expires_at'])) ?>
          </div>
        </div>
        
        <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
          <p>• Sadece USDT TRC20 gönderin, diğer tokenlar kaybolabilir</p>
          <p>• Minimum 19 onay gereklidir (~1-3 dakika)</p>
          <p>• Ödeme onaylandığında bakiyeniz otomatik yüklenir</p>
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
      <div class="bg-slate-50 dark:bg-slate-900 p-3 rounded text-sm">
        <div>Komisyon: <span id="feeText">0</span></div>
        <div>Ödenecek Toplam: <strong id="totalText">0</strong></div>
      </div>
      <div>
        <label class="block text-sm mb-1">Açıklama (opsiyonel)</label>
        <input name="note" class="w-full border rounded p-2 bg-white dark:bg-gray-800" placeholder="Not/ek açıklama">
      </div>
      <div>
        <label class="block text-sm mb-1">Dekont (opsiyonel)</label>
        <input type="file" name="receipt" accept="image/*,application/pdf" class="w-full border rounded p-2 bg-white dark:bg-gray-800">
      </div>
      <?php endif; ?>
      <button class="w-full bg-blue-600 text-white rounded p-2">Gönder</button>
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
      function calc(){
        var sel=document.getElementById('method_id'); if(!sel) return;
        var p=parseFloat(sel.options[sel.selectedIndex].getAttribute('data-p')||'0');
        var f=parseFloat(sel.options[sel.selectedIndex].getAttribute('data-f')||'0');
        var name=sel.options[sel.selectedIndex].getAttribute('data-name')||'';
        var amt=parseFloat(document.getElementById('amount').value||'0');
        var fee= (amt * (p/100.0)) + f; var total = amt + fee;
        document.getElementById('feeText').textContent = fee.toFixed(2) + ' ('+p.toFixed(2)+'% + '+f.toFixed(2)+')';
        document.getElementById('totalText').textContent = total.toFixed(2);
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
  <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
  <script>
    // Copy address to clipboard
    function copyAddress() {
      const addressInput = document.getElementById('walletAddress');
      addressInput.select();
      addressInput.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(addressInput.value).then(function() {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Kopyalandı!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600');
        
        setTimeout(function() {
          btn.textContent = originalText;
          btn.classList.remove('bg-green-600');
          btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
      }).catch(function() {
        alert('Kopyalama başarısız. Lütfen manuel olarak kopyalayın.');
      });
    }
    
    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
      const address = '<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>';
      const amount = '<?= $cryptoPaymentData['amount'] ?>';
      
      // TRON URI scheme: tron:address?amount=value
      const qrData = 'tron:' + address + '?amount=' + amount + '&token=TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
      
      QRCode.toCanvas(document.getElementById('qrcode'), qrData, {
        width: 200,
        height: 200,
        margin: 2,
        color: {
          dark: '#000000',
          light: '#ffffff'
        }
      });
      
      // Auto refresh page every 30 seconds to check payment status
      setTimeout(function() {
        location.reload();
      }, 30000);
    });
    
    // Payment status checker
    function checkPaymentStatus() {
      // This would typically call an AJAX endpoint to check status
      console.log('Checking payment status...');
    }
    
    // Check every 15 seconds
    setInterval(checkPaymentStatus, 15000);
  </script>
  <?php endif; ?>
</body>
</html>

