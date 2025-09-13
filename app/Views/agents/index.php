<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-headset text-rose-600"></i> Agent Durumları</h1>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-100 text-sm"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($isSuper): ?>
    <?php foreach (($agentsByGroup ?? []) as $groupName => $agents): ?>
      <details open class="mb-4">
        <summary class="cursor-pointer bg-slate-50 dark:bg-slate-900/40 p-2 rounded font-semibold text-lg"><?= htmlspecialchars($groupName) ?></summary>
        <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto mt-2">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-900/40">
              <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
                <th class="p-2">Exten</th>
                <th class="p-2">Login</th>
                <th class="p-2">Durum</th>
                <th class="p-2">Son Çağrı</th>
                <th class="p-2">Lead</th>
                <th class="p-2">Aksiyon</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($agents as $a): ?>
              <tr class="border-b border-slate-100 dark:border-slate-700/60">
                <td class="p-2"><?= htmlspecialchars($a['exten'] ?? '') ?></td>
                <td class="p-2"><?= htmlspecialchars($a['user_login'] ?? '') ?></td>
                <td class="p-2">
                  <?php $st=strtolower($a['status'] ?? ''); $cls = $st==='up'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':($st==='ring'?'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200':($st==='online'?'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200')); ?>
                  <span class="px-2 py-0.5 rounded text-xs <?= $cls ?>"><?= htmlspecialchars($a['status'] ?? '') ?></span>
                </td>
                <td class="p-2"><?= htmlspecialchars((string)($a['las_call_time'] ?? '')) ?></td>
                <td class="p-2"><?= htmlspecialchars($a['lead'] ?? '') ?></td>
                <td class="p-2">
<form method="post" action="<?= \App\Helpers\Url::to('/agents/toggle-hidden') ?>" style="display:inline;">
<input type="hidden" name="exten" value="<?= htmlspecialchars($a['exten']) ?>">
<button type="submit" class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
<?= (($a['hidden'] ?? 0) ? 'Göster' : 'Gizle') ?>
</button>
</form>
</td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </details>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/40">
          <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
            <th class="p-2">Exten</th>
            <th class="p-2">Login</th>
            <th class="p-2">Durum</th>
            <th class="p-2">Son Çağrı</th>
            <th class="p-2">Lead</th>
<?php if ($isSuper): ?><th class="p-2">Aksiyon</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach (($agentsByGroup[key($agentsByGroup ?? [])] ?? []) as $a): ?>
          <tr class="border-b border-slate-100 dark:border-slate-700/60">
            <td class="p-2"><?= htmlspecialchars($a['exten'] ?? '') ?></td>
            <td class="p-2"><?= htmlspecialchars($a['user_login'] ?? '') ?></td>
            <td class="p-2">
              <?php $st=strtolower($a['status'] ?? ''); $cls = $st==='up'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':($st==='ring'?'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200':($st==='online'?'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200')); ?>
              <span class="px-2 py-0.5 rounded text-xs <?= $cls ?>"><?= htmlspecialchars($a['status'] ?? '') ?></span>
            </td>
            <td class="p-2"><?= htmlspecialchars((string)($a['las_call_time'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars($a['lead'] ?? '') ?></td>
<?php if ($isSuper): ?><td class="p-2">
<form method="post" action="<?= \App\Helpers\Url::to('/agents/toggle-hidden') ?>" style="display:inline;">
<input type="hidden" name="exten" value="<?= htmlspecialchars($a['exten']) ?>">
<button type="submit" class="px-2 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
<?= (($a['hidden'] ?? 0) ? 'Göster' : 'Gizle') ?>
</button>
</form>
</td><?php endif; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

