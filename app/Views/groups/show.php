<?php $title='Grup Detayı - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-layer-group text-indigo-600"></i> Grup: <?= htmlspecialchars($group['name']) ?></h1>
    <div class="space-x-2">
      <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$group['id'] ?>" class="px-3 py-2 rounded bg-indigo-600 text-white"><i class="fa-regular fa-pen-to-square"></i> Düzenle</a>
      <a href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$group['id'] ?>" class="px-3 py-2 rounded bg-emerald-600 text-white"><i class="fa-solid fa-circle-plus"></i> Bakiye Yükle</a>
    </div>
  </div>
  <div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-800 rounded p-4 shadow">
      <div class="text-sm text-slate-500">Bakiye</div>
      <div class="text-2xl font-semibold"><?= number_format((float)$group['balance'],2) ?></div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded p-4 shadow">
      <div class="text-sm text-slate-500">Margin</div>
      <div class="text-2xl font-semibold"><?= number_format((float)$group['margin'],2) ?>%</div>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded p-4 shadow">
      <div class="text-sm text-slate-500">Grup ID</div>
      <div class="text-2xl font-semibold"><?= (int)$group['id'] ?></div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
          <th class="p-2">Tarih</th>
          <th class="p-2">Tür</th>
          <th class="p-2">Tutar</th>
          <th class="p-2">Referans</th>
          <th class="p-2">Açıklama</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $t): ?>
        <tr class="border-b border-gray-100 dark:border-gray-700">
          <td class="p-2"><?= htmlspecialchars($t['created_at']) ?></td>
          <td class="p-2"><?= htmlspecialchars($t['type']) ?></td>
          <td class="p-2"><?= number_format((float)$t['amount'],2) ?></td>
          <td class="p-2"><?= htmlspecialchars((string)$t['reference']) ?></td>
          <td class="p-2"><?= htmlspecialchars((string)$t['description']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

