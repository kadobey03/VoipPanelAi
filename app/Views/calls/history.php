<?php $title='CDR Geçmişi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-table-list text-indigo-600"></i> CDR Geçmişi</h1>
  </div>

  <?php if ($isSuper && isset($callStat)): ?>
  <details class="mb-4 bg-white/80 dark:bg-slate-800 p-3 rounded-xl shadow">
    <summary class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-800">API Yanıtı (Call Stat)</summary>
    <pre class="mt-2 bg-slate-100 dark:bg-slate-900 p-2 rounded text-xs overflow-auto max-h-96 whitespace-pre-wrap"><?php echo htmlspecialchars(json_encode($callStat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
  </details>
  <?php endif; ?>

  <form method="get" action="<?= \App\Helpers\Url::to('/calls/history') ?>" class="mb-4 bg-white/80 dark:bg-slate-800 p-3 rounded-xl shadow flex flex-wrap items-end gap-3">
    <input type="hidden" name="search" value="1">
    <div>
      <label class="block text-xs">Başlangıç</label>
      <input type="datetime-local" name="from" value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-d\TH:i', strtotime('-1 day'))) ?>" class="border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-xs">Bitiş</label>
      <input type="datetime-local" name="to" value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d\TH:i')) ?>" class="border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-xs">Src</label>
      <input name="src" value="<?= htmlspecialchars($_GET['src'] ?? '') ?>" class="border rounded p-1 bg-white dark:bg-slate-900" placeholder="aramayı başlatan">
    </div>
    <div>
      <label class="block text-xs">Dst</label>
      <input name="dst" value="<?= htmlspecialchars($_GET['dst'] ?? '') ?>" class="border rounded p-1 bg-white dark:bg-slate-900" placeholder="aranan">
    </div>
    <div>
      <label class="block text-xs">Sayfa (100'lük)</label>
      <input type="number" min="1" max="20" name="pages" value="<?= (int)($_GET['pages'] ?? 3) ?>" class="w-20 border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <?php if ($isSuper): ?>
    <div>
      <label class="block text-xs">Grup</label>
      <select name="group_id" class="border rounded p-1 bg-white dark:bg-slate-900">
        <option value="">Tümü</option>
        <?php foreach (($groups ?? []) as $g): $gid=(int)$g['id']; ?>
          <option value="<?= $gid ?>" <?= (isset($_GET['group_id']) && (int)$_GET['group_id']===$gid)?'selected':'' ?>><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div>
      <button class="px-4 py-2 rounded bg-gradient-to-r from-indigo-600 to-blue-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-magnifying-glass"></i> Ara</button>
    </div>
  </form>

  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-xs md:text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">Tarih</th>
          <th class="p-2">Ülke</th>
          <th class="p-2">From</th>
          <th class="p-2">Grup</th>
          <th class="p-2">To</th>
          <th class="p-2">Disposition</th>
          <th class="p-2">Hangup</th>
          <th class="p-2">Süre</th>
          <th class="p-2">Billsec</th>
          <th class="p-2">Cost</th>
          <th class="p-2">Kayıt</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($results['error'])): ?>
          <tr><td class="p-2 text-red-600" colspan="11">Hata: <?= htmlspecialchars($results['error']) ?></td></tr>
        <?php elseif (!empty($results)): ?>
          <?php foreach ($results as $i=>$r): ?>
          <tr class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50/60 dark:hover:bg-slate-900/20 transition">
            <td class="p-2 whitespace-nowrap"><?= htmlspecialchars((string)($r['start'] ?? $r['date'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($r['country'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($r['src'] ?? $r['from'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($r['group'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($r['dst'] ?? $r['to'] ?? '')) ?></td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs <?= strtoupper((string)($r['disposition'] ?? ''))==='ANSWERED'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200' ?>"><?= htmlspecialchars((string)($r['disposition'] ?? '')) ?></span></td>
            <td class="p-2"><?= htmlspecialchars((string)($r['hangup'] ?? '')) ?></td>
            <td class="p-2"><?= (int)($r['duration'] ?? 0) ?></td>
            <td class="p-2"><?= (int)($r['billsec'] ?? 0) ?></td>
            <td class="p-2"><?= is_numeric($r['cost'] ?? null) ? number_format((float)$r['cost'],6) : htmlspecialchars((string)($r['cost'] ?? '')) ?></td>
            <td class="p-2">
              <?php $cid = (string)($r['id'] ?? $r['call_id'] ?? ''); if ($cid!==''): ?>
                <a class="inline-flex items-center gap-1 text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=<?= urlencode($cid) ?>" target="_blank"><i class="fa-regular fa-circle-play"></i> Dinle</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td class="p-2 text-slate-500" colspan="11">Kayıt bulunamadı. Filtreler ile arayın.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>


