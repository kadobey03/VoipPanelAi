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
    <div class="mb-3">Mevcut Bakiye: <strong><?= number_format((float)$group['balance'],2) ?></strong></div>
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
    <div class="mt-4 text-sm">
      <a class="text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/topups') ?>">Bakiye Yükleme Talepleri</a>
    </div>
  </div>
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
</body>
</html>

