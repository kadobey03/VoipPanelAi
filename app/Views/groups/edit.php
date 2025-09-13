<?php $title='Grup Düzenle - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-indigo-600"></i> Grup Düzenle</h1>
    <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700">Geri</a>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($ok)): ?>
    <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
    <div>
      <label class="block text-sm mb-1">Ad</label>
      <input name="name" required class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars($group['name']) ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">Margin %</label>
      <input type="number" step="0.01" name="margin" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars((string)$group['margin']) ?>">
    </div>
    <div>
      <label class="block text-sm mb-1">API Grubu Eşle</label>
      <?php
        $apiGroups = $apiGroups ?? [];
        $selectedApi = $group['api_group_id'] ?? '';
      ?>
      <select name="api_group_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
        <option value="">(Seçilmedi)</option>
        <?php foreach ($apiGroups as $ag): $gid=(int)($ag['id']??0); $gname=(string)($ag['name']??''); ?>
          <option value="<?= $gid ?>" <?= $gid===(int)$selectedApi?'selected':'' ?>>#<?= $gid ?> - <?= htmlspecialchars($gname) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($group['api_group_id'])): ?>
      <div class="text-xs text-slate-500 mt-1">Eşleşen: <?= htmlspecialchars((string)($group['api_group_name'] ?? '')) ?> (#<?= (int)$group['api_group_id'] ?>)</div>
      <?php endif; ?>
    </div>
    <div class="text-sm text-slate-500">Çağrı maliyetine margin uygulanır: fiyat = cost_api * (1 + margin/100)</div>
    <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2">Güncelle</button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
