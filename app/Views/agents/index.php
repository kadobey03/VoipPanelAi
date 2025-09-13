<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-headset text-rose-600"></i> Agent Durumları</h1>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-100 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">Exten</th>
          <th class="p-2">Login</th>
          <th class="p-2">Durum</th>
          <th class="p-2">Son Çağrı</th>
          <th class="p-2">Lead</th>
          <th class="p-2">Grup</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach (($agents ?? []) as $a): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2"><?= htmlspecialchars($a['exten'] ?? '') ?></td>
          <td class="p-2"><?= htmlspecialchars($a['user_login'] ?? '') ?></td>
          <td class="p-2">
            <?php $st=strtolower($a['status'] ?? ''); $cls = $st==='up'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':($st==='ring'?'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200':($st==='online'?'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200')); ?>
            <span class="px-2 py-0.5 rounded text-xs <?= $cls ?>"><?= htmlspecialchars($a['status'] ?? '') ?></span>
          </td>
          <td class="p-2"><?= htmlspecialchars((string)($a['las_call_time'] ?? '')) ?></td>
          <td class="p-2"><?= htmlspecialchars($a['lead'] ?? '') ?></td>
          <td class="p-2"><?= htmlspecialchars($a['group'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

