<?php $title='Dış Numaralar - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-address-book text-amber-600"></i> Dış Numaralar</h1>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-100 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">Numara</th>
          <th class="p-2">Durum</th>
          <th class="p-2">İşlem</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($numbers ?? []) as $n): $num=$n['number'] ?? ''; $st=strtolower($n['status'] ?? ''); ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2 font-mono text-xs"><?= htmlspecialchars($num) ?></td>
          <td class="p-2 capitalize">
            <?php $cls = $st==='active'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':($st==='spam'?'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200'); ?>
            <span class="px-2 py-0.5 rounded text-xs <?= $cls ?>"><?= htmlspecialchars($st) ?></span>
          </td>
          <td class="p-2 space-x-2">
            <form method="post" action="<?= \App\Helpers\Url::to('/numbers/active') ?>" style="display:inline">
              <input type="hidden" name="number" value="<?= htmlspecialchars($num) ?>">
              <button class="px-2 py-1 rounded bg-emerald-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-check"></i> Active</button>
            </form>
            <form method="post" action="<?= \App\Helpers\Url::to('/numbers/spam') ?>" style="display:inline">
              <input type="hidden" name="number" value="<?= htmlspecialchars($num) ?>">
              <button class="px-2 py-1 rounded bg-amber-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-ban"></i> Spam</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

