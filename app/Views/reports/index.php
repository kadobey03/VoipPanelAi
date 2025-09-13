<?php $title='Raporlar - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-chart-line text-emerald-600"></i> Raporlar</h1>
  </div>
  <form method="get" class="mb-4 bg-white/80 dark:bg-slate-800 p-3 rounded-xl shadow flex flex-wrap items-end gap-2">
    <div>
      <label class="block text-xs">Baslangic</label>
      <input type="datetime-local" name="from" class="border rounded p-1 bg-white dark:bg-slate-900" value="<?= htmlspecialchars(str_replace(' ', 'T', substr($from,0,16))) ?>">
    </div>
    <div>
      <label class="block text-xs">Bitis</label>
      <input type="datetime-local" name="to" class="border rounded p-1 bg-white dark:bg-slate-900" value="<?= htmlspecialchars(str_replace(' ', 'T', substr($to,0,16))) ?>">
    </div>
    <?php if ($isSuper): ?>
    <div>
      <label class="block text-xs">Grup</label>
      <input type="number" name="group_id" class="border rounded p-1 bg-white dark:bg-slate-900" value="<?= isset($_GET['group_id'])? (int)$_GET['group_id'] : '' ?>" placeholder="(hepsi)">
    </div>
    <?php endif; ?>
    <button class="px-4 py-2 rounded bg-gradient-to-r from-indigo-600 to-blue-600 text-white hover:opacity-90 transition"><i class="fa-solid fa-filter"></i> Filtrele</button>
  </form>

  <?php
    $totCalls=0;$totCost=0.0;$totRev=0.0;$totProfit=0.0;$answered=0;$noans=0;
    foreach(($summary??[]) as $row){ $totCalls+=(int)$row['calls']; $totCost+=(float)$row['cost_api']; $totRev+=(float)$row['revenue']; $totProfit+=(float)$row['profit']; }
    foreach(($dispRows??[]) as $d){ $n=(int)$d['n']; $disp=strtoupper($d['d']); if(in_array($disp,['ANSWERED','ANSWER'])) $answered+=$n; elseif($disp==='NO ANSWER') $noans+=$n; }
    if(!$isSuper){ $totCalls=$callsCount??0; $answered=$answerCount??0; $noans=$noAnswerCount??0; }
  ?>
  <div class="grid md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-phone text-blue-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Toplam Cagri</div><div class="text-xl font-semibold"><?= (int)$totCalls ?></div></div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-circle-check text-emerald-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Cevaplanan</div><div class="text-xl font-semibold"><?= (int)$answered ?></div></div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-circle-xmark text-rose-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">No Answer</div><div class="text-xl font-semibold"><?= (int)$noans ?></div></div>
    </div>
    <?php if ($isSuper): ?>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-coins text-amber-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Maliyet</div><div class="text-xl font-semibold"><?= number_format((float)$totCost,2) ?></div></div>
    </div>
    <?php else: ?>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-wallet text-amber-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Harcanan</div><div class="text-xl font-semibold"><?= number_format((float)($spent??0),2) ?></div></div>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($isSuper): ?>
  <div class="grid md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-sack-dollar text-emerald-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Gelir</div><div class="text-xl font-semibold"><?= number_format((float)$totRev,2) ?></div></div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-arrow-trend-up text-fuchsia-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Kar</div><div class="text-xl font-semibold"><?= number_format((float)$totProfit,2) ?></div></div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <div class="text-xs text-slate-500">Gruplar</div>
      <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
        <?php foreach (($summary??[]) as $row): $gid=(int)$row['group_id']; ?>
          <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/40 p-2 rounded">
            <span><?= htmlspecialchars($groups[$gid] ?? ('#'.$gid)) ?></span>
            <span class="font-semibold"><?= number_format((float)$row['profit'],2) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="grid md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 flex items-center gap-3">
      <i class="fa-solid fa-piggy-bank text-teal-600 text-2xl"></i>
      <div><div class="text-xs text-slate-500">Kalan Bakiye</div><div class="text-xl font-semibold"><?= number_format((float)($balance??0),2) ?></div></div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <div class="text-xs text-slate-500">Ozet</div>
      <div class="mt-2 grid grid-cols-3 text-center">
        <div><div class="text-xs">Answered</div><div class="font-semibold"><?= (int)$answered ?></div></div>
        <div><div class="text-xs">NoAns</div><div class="font-semibold"><?= (int)$noans ?></div></div>
        <div><div class="text-xs">Calls</div><div class="font-semibold"><?= (int)$totCalls ?></div></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
    <div class="text-lg font-semibold mb-2">Gunluk Trend</div>
    <canvas id="trend" height="120"></canvas>
  </div>

  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4 mt-6">
    <div class="text-lg font-semibold mb-2">Agent Ozeti (DB)</div>
    <?php if ($isSuper): ?>
      <?php foreach (($agentsByGroup ?? []) as $groupName => $agents): ?>
        <details open class="mb-4">
          <summary class="cursor-pointer bg-slate-50 dark:bg-slate-900/40 p-2 rounded font-semibold"><?= htmlspecialchars($groupName) ?></summary>
          <div class="overflow-x-auto mt-2">
            <table class="min-w-full text-xs md:text-sm">
              <thead class="bg-slate-50 dark:bg-slate-900/40">
                <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
                  <th class="p-2">Login</th>
                  <th class="p-2">Cagri</th>
                  <th class="p-2">Cevap</th>
                  <th class="p-2">Billsec</th>
                  <th class="p-2">Cost</th>
                  <th class="p-2">Exten</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($agents as $r): ?>
                <tr class="border-b border-slate-100 dark:border-slate-700/60">
                  <td class="p-2"><?= htmlspecialchars($r['user_login'] ?? '') ?></td>
                  <td class="p-2"><?= (int)($r['calls'] ?? 0) ?></td>
                  <td class="p-2"><?= (int)($r['answer'] ?? 0) ?></td>
                  <td class="p-2"><?= (int)($r['billsec'] ?? 0) ?></td>
                  <td class="p-2"><?= number_format((float)($r['cost'] ?? 0),2) ?></td>
                  <td class="p-2"><?= htmlspecialchars($r['voip_exten'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </details>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm">
          <thead class="bg-slate-50 dark:bg-slate-900/40">
            <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
              <th class="p-2">Login</th>
              <th class="p-2">Cagri</th>
              <th class="p-2">Cevap</th>
              <th class="p-2">Billsec</th>
              <th class="p-2">Exten</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($agentsByGroup[key($agentsByGroup ?? [])] ?? []) as $r): ?>
            <tr class="border-b border-slate-100 dark:border-slate-700/60">
              <td class="p-2"><?= htmlspecialchars($r['user_login'] ?? '') ?></td>
              <td class="p-2"><?= (int)($r['calls'] ?? 0) ?></td>
              <td class="p-2"><?= (int)($r['answer'] ?? 0) ?></td>
              <td class="p-2"><?= (int)($r['billsec'] ?? 0) ?></td>
              <td class="p-2"><?= htmlspecialchars($r['voip_exten'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="grid md:grid-cols-2 gap-4 mt-6">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <div class="text-lg font-semibold mb-2">Top Agentler (Billsec)</div>
      <canvas id="topAgents" height="160"></canvas>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <div class="text-lg font-semibold mb-2">Disposition Dagilimi</div>
      <canvas id="dispChart" height="160"></canvas>
    </div>
  </div>

  <script src="<?= \App\Helpers\Url::to('/public/assets/js/chart.min.js') ?>"></script>
  <script>
    const labels = <?= json_encode(array_map(function($t){return $t['d'];}, $trend ?? []), JSON_UNESCAPED_UNICODE) ?>;
    const cost = <?= json_encode(array_map(function($t){return (float)$t['cost'];}, $trend ?? [])) ?>;
    const revenue = <?= json_encode(array_map(function($t){return (float)$t['revenue'];}, $trend ?? [])) ?>;
    const ctx = document.getElementById('trend').getContext('2d');
    new Chart(ctx, { type:'line', data:{ labels, datasets:[ {label:'Maliyet', data:cost, borderColor:'rgba(239,68,68,1)', backgroundColor:'rgba(239,68,68,0.2)', tension:.25}, {label:'Gelir', data:revenue, borderColor:'rgba(16,185,129,1)', backgroundColor:'rgba(16,185,129,0.2)', tension:.25} ] }, options:{responsive:true, animation:{duration:800, easing:'easeOutQuart'}, scales:{y:{beginAtZero:true}}} });

    const agentStats = <?= json_encode($allAgents ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const topAgents = (agentStats||[]).slice().sort((a,b)=>(+b.billsec)-(+a.billsec)).slice(0,10);
    const aLabels = topAgents.map(a=> (a.user_login||a.voip_exten||'agent'));
    const aBill = topAgents.map(a=> +a.billsec||0);
    new Chart(document.getElementById('topAgents').getContext('2d'), { type:'bar', data:{ labels:aLabels, datasets:[{ label:'Billsec', data:aBill, backgroundColor:'rgba(59,130,246,0.6)', borderRadius:6}] }, options:{responsive:true, animation:{duration:700}, scales:{y:{beginAtZero:true}}} });

    const disp = <?= json_encode($dispRows ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const dLabels = (disp||[]).map(x=>x.d);
    const dData = (disp||[]).map(x=>+x.n||0);
    new Chart(document.getElementById('dispChart').getContext('2d'), { type:'doughnut', data:{ labels:dLabels, datasets:[{ data:dData, backgroundColor:['#10b981','#ef4444','#f59e0b','#3b82f6','#8b5cf6'] }] }, options:{responsive:true, animation:{animateScale:true, duration:800}} });
  </script>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
