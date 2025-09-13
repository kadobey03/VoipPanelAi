<?php $title='Grup Oluştur - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-plus text-indigo-600"></i> Grup Oluştur</h1>
    <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700">Geri</a>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($ok)): ?>
    <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
    <div>
      <label class="block text-sm mb-1">Ad</label>
      <input name="name" required class="w-full border rounded p-2 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-sm mb-1">Margin %</label>
      <input type="number" step="0.01" name="margin" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0">
    </div>
    <div>
      <label class="block text-sm mb-1">Başlangıç Bakiye</label>
      <input type="number" step="0.01" name="balance" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0">
    </div>
    <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2">Oluştur</button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

