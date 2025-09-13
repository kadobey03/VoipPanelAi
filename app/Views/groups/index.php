<?php $title='Gruplar - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-layer-group text-indigo-600"></i> Gruplar</h1>
    <div class="space-x-2">
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
      <a href="<?= \App\Helpers\Url::to('/groups/create') ?>" class="px-3 py-2 rounded bg-indigo-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-plus"></i> Yeni Grup</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-hidden">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-3">ID</th>
          <th class="p-3">Ad</th>
          <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
          <?php if ($isSuper): ?><th class="p-3">Margin %</th><?php endif; ?>
          <th class="p-3">Bakiye</th>
          <th class="p-3">API Grup</th>
          <th class="p-3">İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($groups as $g): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50/60 dark:hover:bg-slate-900/20 transition">
          <td class="p-3 font-mono text-xs">#<?= (int)$g['id'] ?></td>
          <td class="p-3 font-medium"><?= htmlspecialchars($g['name']) ?></td>
          <?php if ($isSuper): ?><td class="p-3"><?= number_format((float)$g['margin'],2) ?>%</td><?php endif; ?>
          <td class="p-3"><?= number_format((float)$g['balance'],2) ?></td>
          <td class="p-3">
            <?php if (!empty($g['api_group_name'])): ?>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200"><i class="fa-solid fa-link"></i> <?= htmlspecialchars($g['api_group_name']) ?><?php if(!empty($g['api_group_id'])): ?> (#<?= (int)$g['api_group_id'] ?>)<?php endif; ?></span>
            <?php else: ?>
              <span class="text-slate-400">Eşleşmedi</span>
            <?php endif; ?>
          </td>
          <td class="p-3 space-x-2">
            <a class="inline-flex items-center gap-1 text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/groups/show') ?>?id=<?= (int)$g['id'] ?>"><i class="fa-regular fa-eye"></i> Detay</a>
            <a class="inline-flex items-center gap-1 text-indigo-600 hover:underline" href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>"><i class="fa-regular fa-pen-to-square"></i> Düzenle</a>
            <a class="inline-flex items-center gap-1 text-emerald-600 hover:underline" href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$g['id'] ?>"><i class="fa-solid fa-circle-plus"></i> Bakiye Yükle</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
