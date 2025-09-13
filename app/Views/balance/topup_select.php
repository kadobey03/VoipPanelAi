<?php $title='Bakiye Yükle - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Grup Seçerek Bakiye Yükle</h1>
    <form method="get" action="<?= \App\Helpers\Url::to('/groups/topup') ?>" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
      <div>
        <label class="block text-sm mb-1">Grup</label>
        <select name="id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <?php foreach (($groups ?? []) as $g): ?>
            <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2">Devam Et</button>
    </form>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

