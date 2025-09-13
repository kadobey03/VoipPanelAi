<?php $title='Çağrılar - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-phone text-rose-600"></i> Çağrılar</h1>
  </div>
  <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
  <form method="post" action="<?= \App\Helpers\Url::to('/calls/sync-history') ?>" class="mb-4 bg-white/80 dark:bg-slate-800 p-3 rounded-xl shadow flex flex-wrap items-end gap-2">
    <div>
      <label class="block text-xs">Başlangıç</label>
      <input type="datetime-local" name="from" class="border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-xs">Bitiş</label>
      <input type="datetime-local" name="to" class="border rounded p-1 bg-white dark:bg-slate-900">
    </div>
    <button class="px-4 py-2 rounded bg-gradient-to-r from-indigo-600 to-blue-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-rotate"></i> CDR Senkronize</button>
  </form>
  <?php endif; ?>

  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-xs md:text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">Tarih</th>
          <th class="p-2">Src</th>
          <th class="p-2">Dst</th>
          <th class="p-2">Süre</th>
          <th class="p-2">Billsec</th>
          <th class="p-2">Durum</th>
          <th class="p-2">Grup</th>
          <th class="p-2">Cost(API)</th>
          <th class="p-2">Margin%</th>
          <th class="p-2">Tahsil</th>
          <th class="p-2">Kayıt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($calls as $c): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50/60 dark:hover:bg-slate-900/20 transition">
          <td class="p-2 whitespace-nowrap"><?= htmlspecialchars($c['start']) ?></td>
          <td class="p-2"><?= htmlspecialchars($c['src']) ?></td>
          <td class="p-2"><?= htmlspecialchars($c['dst']) ?></td>
          <td class="p-2"><?= (int)$c['duration'] ?></td>
          <td class="p-2"><?= (int)$c['billsec'] ?></td>
          <td class="p-2"><span class="px-2 py-0.5 rounded text-xs <?= strtoupper($c['disposition'])==='ANSWERED'?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200' ?>"><?= htmlspecialchars($c['disposition']) ?></span></td>
          <?php
            $gid=(int)$c['group_id'];
            $gn = $gid;
            if (isset($groupNamesById) && isset($groupNamesById[$gid])) { $gn=$groupNamesById[$gid]; }
            elseif (isset($groupNamesByApi) && isset($groupNamesByApi[$gid])) { $gn=$groupNamesByApi[$gid]; }
          ?>
          <td class="p-2"><?= htmlspecialchars(is_string($gn)?$gn:('#'.$gid)) ?></td>
          <td class="p-2"><?= number_format((float)$c['cost_api'],6) ?></td>
          <td class="p-2"><?= number_format((float)$c['margin_percent'],2) ?></td>
          <td class="p-2"><?= number_format((float)$c['amount_charged'],6) ?></td>
          <td class="p-2">
            <?php if (!empty($c['call_id']) && strtoupper($c['disposition'])==='ANSWERED'): ?>
              <a class="inline-flex items-center gap-1 text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=<?= urlencode($c['call_id']) ?>" target="_blank"><i class="fa-regular fa-circle-play"></i> Dinle</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
