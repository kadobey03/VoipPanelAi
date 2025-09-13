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
    <form method="post" class="space-y-3 bg-white dark:bg-gray-800 p-4 rounded shadow">
      <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role']!=='superadmin'): ?>
      <div>
        <label class="block text-sm mb-1">Ödeme Yöntemi</label>
        <select name="method" class="w-full border rounded p-2 bg-white dark:bg-gray-800" required>
          <option value="bank">Havale/EFT</option>
          <option value="card">Kredi Kartı</option>
          <option value="paypal">PayPal</option>
          <option value="crypto">Kripto</option>
        </select>
      </div>
      <?php else: ?>
      <input type="hidden" name="method" value="manual">
      <?php endif; ?>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']!=='superadmin'): ?>
        <div class="text-xs text-slate-500">Not: Talebiniz onaylanınca grup bakiyenize yansır.</div>
      <?php endif; ?>
      <div>
        <label class="block text-sm mb-1">Tutar</label>
        <input type="number" step="0.01" min="0.01" name="amount" class="w-full border rounded p-2 bg-white dark:bg-gray-800" required>
      </div>
      <button class="w-full bg-blue-600 text-white rounded p-2">Gönder</button>
    </form>
    <div class="mt-4 text-sm">
      <a class="text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/topups') ?>">Bakiye Yükleme Talepleri</a>
    </div>
  </div>
</body>
</html>
