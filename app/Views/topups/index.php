<?php $title='Bakiye Yükleme Talepleri - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-circle-plus text-emerald-600"></i> Bakiye Yükleme Talepleri</h1>
  </div>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">ID</th>
          <th class="p-2">Grup</th>
          <th class="p-2">Kullanıcı</th>
          <th class="p-2">Tutar</th>
          <th class="p-2">Yöntem</th>
          <th class="p-2">Durum</th>
          <th class="p-2">Tarih</th>
          <?php if ($isSuper): ?><th class="p-2">İşlem</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($items ?? []) as $it): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2">#<?= (int)$it['id'] ?></td>
          <td class="p-2"><?= htmlspecialchars($it['group_name'] ?? ('#'.$it['group_id'])) ?></td>
          <td class="p-2"><?= htmlspecialchars($it['user_login'] ?? (string)$it['user_id']) ?></td>
          <td class="p-2"><?= number_format((float)$it['amount'],2) ?></td>
          <td class="p-2"><?= htmlspecialchars($it['method']) ?></td>
          <td class="p-2 capitalize">
            <?php $st=$it['status']; $cls=$st==='approved'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':($st==='rejected'?'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200':'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'); ?>
            <span class="px-2 py-0.5 rounded text-xs <?= $cls ?>"><?= htmlspecialchars($st) ?></span>
          </td>
          <td class="p-2"><?= htmlspecialchars($it['created_at']) ?></td>
          <?php if ($isSuper): ?>
          <td class="p-2 space-x-2">
            <?php if ($it['status']==='pending'): ?>
            <form method="post" action="<?= \App\Helpers\Url::to('/topups/approve') ?>" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <button class="px-2 py-1 rounded bg-emerald-600 text-white">Onayla</button>
            </form>
            <form method="post" action="<?= \App\Helpers\Url::to('/topups/reject') ?>" style="display:inline">
              <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
              <button class="px-2 py-1 rounded bg-rose-600 text-white">Reddet</button>
            </form>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

