<?php $title='Yeni Ödeme Yöntemi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Yeni Ödeme Yöntemi</h1>
    <?php if (!empty($error)): ?><div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($ok)): ?><div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
      <div><label class="block text-sm mb-1">Ad</label><input name="name" class="w-full border rounded p-2 bg-white dark:bg-slate-900" required></div>
      <div><label class="block text-sm mb-1">Tip</label><input name="method_type" class="w-full border rounded p-2 bg-white dark:bg-slate-900" placeholder="bank/card/paypal/crypto"></div>
      <div><label class="block text-sm mb-1">Detay</label><textarea name="details" class="w-full border rounded p-2 bg-white dark:bg-slate-900" rows="3" placeholder="Açıklama ve yönergeler"></textarea></div>
      <div class="grid grid-cols-2 gap-2">
        <div><label class="block text-sm mb-1">Komisyon %</label><input type="number" step="0.01" name="fee_percent" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0"></div>
        <div><label class="block text-sm mb-1">Sabit Komisyon</label><input type="number" step="0.01" name="fee_fixed" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0"></div>
      </div>
      <label class="inline-flex items-center gap-2"><input type="checkbox" name="active" checked> Aktif</label>
      <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2">Kaydet</button>
    </form>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

