<?php $title='Bakiye Geçmişi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-slate-600"></i> Bakiye Geçmişi</h1>
    <?php if ($isSuper): ?>
      <form method="get" class="flex items-end gap-2">
        <div>
          <label class="block text-xs">Grup ID</label>
          <input type="number" name="group_id" value="<?= isset($_GET['group_id'])?(int)$_GET['group_id']:'' ?>" class="border rounded p-1 bg-white dark:bg-slate-900" placeholder="(hepsi)">
        </div>
        <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700">Filtre</button>
      </form>
    <?php endif; ?>
  </div>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">Tarih</th>
          <th class="p-2">Grup</th>
          <th class="p-2">Tür</th>
          <th class="p-2">Tutar</th>
          <th class="p-2">Referans</th>
          <th class="p-2">Açıklama</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($items ?? []) as $t): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2"><?= htmlspecialchars($t['created_at']) ?></td>
          <td class="p-2"><?= htmlspecialchars($t['group_name'] ?? ('#'.$t['group_id'])) ?></td>
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

