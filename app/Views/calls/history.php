<?php $title='CDR Geçmişi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-table-list text-indigo-600"></i> CDR Geçmişi</h1>
  </div>

  <form method="get" action="<?= \App\Helpers\Url::to('/calls/history') ?>" class="mb-4 bg-white/80 dark:bg-slate-800 p-3 rounded-xl shadow flex flex-wrap items-end gap-3">
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
      <label class="block text-xs">Sayfa</label>
      <input type="number" min="1" name="page" value="<?= (int)($_GET['page'] ?? 1) ?>" class="w-20 border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-xs">Adet</label>
      <input type="number" min="10" max="200" name="per" value="<?= (int)($_GET['per'] ?? 100) ?>" class="w-24 border rounded p-1 bg-white dark:bg-slate-900">
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
          <th class="p-2">Src</th>
          <?php if ($isSuper): ?><th class="p-2">Grup</th><?php endif; ?>
          <th class="p-2">Dst</th>
          <th class="p-2">Disposition</th>
          <th class="p-2">Süre</th>
          <th class="p-2">Billsec</th>
          <?php if ($isSuper): ?><th class="p-2">Cost(API)</th><?php endif; ?>
          <?php if ($isSuper): ?><th class="p-2">Margin%</th><?php endif; ?>
          <th class="p-2">Tahsil</th>
          <th class="p-2">Kayıt</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($calls ?? [])): ?>
          <?php foreach ($calls as $c): ?>
          <tr class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50/60 dark:hover:bg-slate-900/20 transition">
            <td class="p-2 whitespace-nowrap"><?= htmlspecialchars($c['start']) ?></td>
            <td class="p-2"><?= htmlspecialchars($c['src']) ?></td>
            <?php if ($isSuper): ?>
              <?php $gid=(int)$c['group_id']; $gn = isset($groupNamesById[$gid]) ? $groupNamesById[$gid] : (isset($groupNamesByApi[$gid]) ? $groupNamesByApi[$gid] : ('#'.$gid)); ?>
              <td class="p-2"><?= htmlspecialchars($gn) ?></td>
            <?php endif; ?>
            <td class="p-2"><?= htmlspecialchars($c['dst']) ?></td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs <?= strtoupper($c['disposition'])==='ANSWERED'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200' ?>"><?= htmlspecialchars($c['disposition']) ?></span></td>
            <td class="p-2"><?= (int)$c['duration'] ?></td>
            <td class="p-2"><?= (int)$c['billsec'] ?></td>
            <?php if ($isSuper): ?><td class="p-2"><?= number_format((float)$c['cost_api'],6) ?></td><?php endif; ?>
            <?php if ($isSuper): ?><td class="p-2"><?= number_format((float)$c['margin_percent'],2) ?></td><?php endif; ?>
            <td class="p-2"><?= number_format((float)$c['amount_charged'],6) ?></td>
            <td class="p-2"><?php if (!empty($c['call_id']) && strtoupper($c['disposition'])==='ANSWERED'): ?><a class="inline-flex items-center gap-1 text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=<?= urlencode($c['call_id']) ?>" target="_blank"><i class="fa-regular fa-circle-play"></i> Dinle</a><?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td class="p-2 text-slate-500" colspan="<?= $isSuper ? 11 : 8 ?>">Kayıt bulunamadı.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php $page = (int)($_GET['page'] ?? 1); $per=(int)($_GET['per'] ?? 100); $totalPages = $totalPages ?? 1; ?>
  <div class="mt-3 flex items-center gap-2 text-sm">
    <?php if (($page ?? 1) > 1): $q=$_GET; $q['page']=$page-1; ?>
      <a class="px-3 py-1 rounded bg-slate-200 dark:bg-slate-700" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>">Önceki</a>
    <?php endif; ?>
    <div>Sayfa <?= (int)$page ?>/<?= (int)$totalPages ?></div>
    <?php if (($page ?? 1) < ($totalPages ?? 1)): $q=$_GET; $q['page']=$page+1; ?>
      <a class="px-3 py-1 rounded bg-slate-200 dark:bg-slate-700" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>">Sonraki</a>
    <?php endif; ?>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>


