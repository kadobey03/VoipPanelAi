<?php
$title = __('reports') . ' - ' . __('papam_voip_panel');
require dirname(__DIR__) . '/partials/header.php';

$isSuper       = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'superadmin');
$isGroupAdmin  = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'groupadmin');
$isGroupMember = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'groupmember');
$canSeeCost    = $isSuper || $isGroupAdmin;

// Aggregate totals
$totCalls = 0; $totCost = 0.0; $totRev = 0.0; $totProfit = 0.0; $answered = 0; $noans = 0; $busy = 0; $failed = 0; $totBillsec = 0;
foreach (($summary ?? []) as $row) {
    $totCalls   += (int)$row['calls'];
    $totCost    += (float)$row['cost_api'];
    $totRev     += (float)$row['revenue'];
    $totProfit  += (float)$row['profit'];
    $totBillsec += (int)($row['billsec'] ?? 0);
}
foreach (($dispRows ?? []) as $d) {
    $n = (int)$d['n']; $disp = strtoupper($d['d']);
    if (in_array($disp, ['ANSWERED','ANSWER'])) $answered += $n;
    elseif (str_contains($disp, 'NO')) $noans += $n;
    elseif ($disp === 'BUSY') $busy += $n;
    else $failed += $n;
}
if (!$isSuper) {
    $totCalls = $callsCount ?? 0;
    $answered = $answerCount ?? 0;
    $noans    = $noAnswerCount ?? 0;
}
$answerRate   = $totCalls > 0 ? round($answered / $totCalls * 100, 1) : 0;
$profitMargin = $totRev > 0 ? round($totProfit / $totRev * 100, 1) : 0;
$avgDuration  = $answered > 0 ? round($totBillsec / $answered, 0) : 0;
$billsecFmt   = fn(int $s) => sprintf('%dsa %02ddak', floor($s/3600), floor(($s%3600)/60));
?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl min-w-[200px]">
    <div class="w-12 h-12 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin"></div>
    <p class="font-semibold text-slate-700 dark:text-slate-200"><?= __('report_preparing') ?></p>
  </div>
</div>

<!-- ══════════════ HEADER -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/30 flex-shrink-0">
      <i class="fa-solid fa-chart-mixed text-white text-xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><?= __('reports_and_analysis') ?></h1>
      <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">
        <?= date('d.m.Y', strtotime($from)) ?> — <?= date('d.m.Y', strtotime($to)) ?>
        &nbsp;·&nbsp;
        <span class="text-indigo-600 dark:text-indigo-400 font-medium"><?= number_format($totCalls) ?> çağrı</span>
      </p>
    </div>
  </div>
  <!-- Export -->
  <div class="flex items-center gap-2 flex-wrap">
    <span class="text-xs text-slate-400 font-medium hidden sm:block">Dışa Aktar:</span>
    <button onclick="exportReport('csv')" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-semibold transition-all border border-blue-200 dark:border-blue-700/50">
      <i class="fa-solid fa-file-csv text-sm"></i> CSV
    </button>
    <button onclick="exportReport('excel')" class="inline-flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-semibold transition-all border border-emerald-200 dark:border-emerald-700/50">
      <i class="fa-solid fa-file-excel text-sm"></i> Excel
    </button>
    <button onclick="exportReport('pdf')" class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 rounded-xl text-sm font-semibold transition-all border border-red-200 dark:border-red-700/50">
      <i class="fa-solid fa-file-pdf text-sm"></i> PDF
    </button>
  </div>
</div>

<!-- ══════════════ FILTERS -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-6">
  <div class="flex items-center gap-2 mb-4">
    <i class="fa-solid fa-sliders text-indigo-500 text-sm"></i>
    <span class="font-bold text-slate-700 dark:text-slate-200 text-sm"><?= __('filters') ?></span>
  </div>
  <form method="get" id="filterForm">
    <!-- Quick presets -->
    <div class="flex flex-wrap gap-2 mb-4" id="presetBtns">
      <?php foreach ([
        ['today',     __('today')],
        ['yesterday', __('yesterday')],
        ['week',      __('this_week')],
        ['month',     __('this_month')],
        ['lastmonth', __('last_month')],
        ['last7',     'Son 7 Gün'],
        ['last30',    'Son 30 Gün'],
      ] as [$key, $label]): ?>
      <button type="button" onclick="setPreset('<?= $key ?>', this)"
              class="preset-btn px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all
                     bg-slate-50 dark:bg-slate-700 border-slate-200 dark:border-slate-600
                     text-slate-600 dark:text-slate-300
                     hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-700
                     dark:hover:bg-indigo-900/30 dark:hover:border-indigo-600 dark:hover:text-indigo-300">
        <?= $label ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Inputs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 <?= $isSuper ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-3">
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
          <i class="fa-solid fa-calendar-day text-emerald-500 mr-1"></i><?= __('start_date') ?>
        </label>
        <input type="datetime-local" name="from" id="from-date"
               value="<?= htmlspecialchars(str_replace(' ', 'T', substr($from, 0, 16))) ?>"
               class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
          <i class="fa-solid fa-calendar-day text-rose-500 mr-1"></i><?= __('end_date') ?>
        </label>
        <input type="datetime-local" name="to" id="to-date"
               value="<?= htmlspecialchars(str_replace(' ', 'T', substr($to, 0, 16))) ?>"
               class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
      </div>
      <?php if ($isSuper): ?>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
          <i class="fa-solid fa-layer-group text-purple-500 mr-1"></i><?= __('group_selection') ?>
        </label>
        <input type="number" name="group_id"
               value="<?= isset($_GET['group_id']) ? (int)$_GET['group_id'] : '' ?>"
               placeholder="<?= __('group_id_placeholder') ?>"
               class="w-full px-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
      </div>
      <?php endif; ?>
      <div class="flex items-end">
        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl font-semibold text-sm shadow-md shadow-indigo-500/20 transition-all">
          <i class="fa-solid fa-magnifying-glass"></i><?= __('apply_filters') ?>
        </button>
      </div>
    </div>
  </form>
</div>

<!-- ══════════════ KPI CARDS ROW 1 -->
<div class="grid grid-cols-2 <?= $canSeeCost ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-4 mb-4">

  <!-- Total Calls -->
  <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/20">
    <div class="absolute -right-3 -top-3 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/5 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-phone text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide">Toplam Çağrı</span>
      </div>
      <div class="text-3xl font-black mb-1" id="kpi-total"><?= number_format($totCalls) ?></div>
      <div class="text-xs opacity-60">Seçilen dönem</div>
    </div>
  </div>

  <!-- Answered -->
  <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/20">
    <div class="absolute -right-3 -top-3 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-circle-check text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide">Cevaplanan</span>
      </div>
      <div class="text-3xl font-black mb-1"><?= number_format($answered) ?></div>
      <div class="text-xs opacity-80">
        <div class="w-full bg-white/20 rounded-full h-1.5 mt-2 mb-1">
          <div class="bg-white h-1.5 rounded-full" style="width:<?= $answerRate ?>%"></div>
        </div>
        %<?= $answerRate ?> cevap oranı
      </div>
    </div>
  </div>

  <!-- No Answer -->
  <div class="relative overflow-hidden bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/20">
    <div class="absolute -right-3 -top-3 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-phone-slash text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide">Cevapsız</span>
      </div>
      <div class="text-3xl font-black mb-1"><?= number_format($noans) ?></div>
      <div class="text-xs opacity-80">
        <div class="w-full bg-white/20 rounded-full h-1.5 mt-2 mb-1">
          <div class="bg-white h-1.5 rounded-full" style="width:<?= $totCalls > 0 ? round($noans/$totCalls*100,1) : 0 ?>%"></div>
        </div>
        %<?= $totCalls > 0 ? round($noans/$totCalls*100,1) : 0 ?> oran
      </div>
    </div>
  </div>

  <?php if ($canSeeCost): ?>
  <!-- Cost -->
  <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/20">
    <div class="absolute -right-3 -top-3 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-coins text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= $isSuper ? 'API Maliyet' : 'Harcama' ?></span>
      </div>
      <div class="text-2xl font-black mb-1">$<?= number_format($isSuper ? $totCost : ($spent ?? 0), 2) ?></div>
      <div class="text-xs opacity-60">Dönem toplam</div>
    </div>
  </div>
  <?php else: ?>
  <!-- Answer Rate % -->
  <div class="relative overflow-hidden bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/20">
    <div class="absolute -right-3 -top-3 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-percent text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide">Cevap Oranı</span>
      </div>
      <div class="text-3xl font-black mb-1">%<?= $answerRate ?></div>
      <div class="text-xs opacity-60">Başarı oranı</div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ══════════════ KPI CARDS ROW 2 (extra metrics) -->
<div class="grid grid-cols-2 <?= $isSuper ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-4 mb-6">

  <!-- Avg Duration -->
  <div class="bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-700/40 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-stopwatch text-blue-600 dark:text-blue-400"></i>
    </div>
    <div class="min-w-0">
      <div class="text-lg font-bold text-slate-800 dark:text-white"><?= gmdate('i:s', $avgDuration) ?></div>
      <div class="text-xs text-slate-400">Ort. Konuşma Süresi</div>
    </div>
  </div>

  <!-- Total Talk Time -->
  <div class="bg-white dark:bg-slate-800 border border-cyan-200 dark:border-cyan-700/40 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-clock text-cyan-600 dark:text-cyan-400"></i>
    </div>
    <div class="min-w-0">
      <div class="text-sm font-bold text-slate-800 dark:text-white"><?= $billsecFmt($totBillsec) ?></div>
      <div class="text-xs text-slate-400">Toplam Konuşma</div>
    </div>
  </div>

  <?php if ($isSuper): ?>
  <!-- Revenue -->
  <div class="bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-700/40 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-sack-dollar text-emerald-600 dark:text-emerald-400"></i>
    </div>
    <div class="min-w-0">
      <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">$<?= number_format($totRev, 2) ?></div>
      <div class="text-xs text-slate-400">Toplam Gelir</div>
    </div>
  </div>

  <!-- Net Profit -->
  <div class="bg-white dark:bg-slate-800 border border-fuchsia-200 dark:border-fuchsia-700/40 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-arrow-trend-up text-fuchsia-600 dark:text-fuchsia-400"></i>
    </div>
    <div class="min-w-0">
      <div class="text-lg font-bold text-fuchsia-600 dark:text-fuchsia-400">$<?= number_format($totProfit, 2) ?></div>
      <div class="text-xs text-slate-400">Net Kâr &nbsp;·&nbsp; %<?= $profitMargin ?> marjin</div>
    </div>
  </div>
  <?php else: ?>
  <!-- Busy/Failed -->
  <div class="bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-700/40 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-signal text-amber-600 dark:text-amber-400"></i>
    </div>
    <div class="min-w-0">
      <div class="text-lg font-bold text-slate-800 dark:text-white"><?= number_format($busy + $failed) ?></div>
      <div class="text-xs text-slate-400">Meşgul / Başarısız</div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ══════════════ CHARTS 2-col -->
<div class="grid lg:grid-cols-3 gap-5 mb-6">

  <!-- Trend Line (2/3) -->
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-start justify-between mb-5">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
            <i class="fa-solid fa-chart-line text-white text-sm"></i>
          </div>
          <h3 class="font-bold text-slate-800 dark:text-white"><?= __('daily_trend_analysis') ?></h3>
        </div>
        <p class="text-xs text-slate-400 ml-10"><?= __('cost_and_revenue_trends') ?></p>
      </div>
      <div class="flex gap-2">
        <button onclick="toggleDataset('trend',0)" id="btn-cost"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-700/40 transition-all hover:opacity-70">
          <i class="fa-solid fa-circle text-xs mr-1"></i><?= __('cost') ?>
        </button>
        <?php if ($isSuper): ?>
        <button onclick="toggleDataset('trend',1)" id="btn-rev"
                class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-700/40 transition-all hover:opacity-70">
          <i class="fa-solid fa-circle text-xs mr-1"></i><?= __('revenue') ?>
        </button>
        <?php endif; ?>
      </div>
    </div>
    <div style="height:230px;position:relative">
      <canvas id="trendChart"></canvas>
    </div>
  </div>

  <!-- Disposition Donut (1/3) -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-5">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
        <i class="fa-solid fa-chart-pie text-white text-sm"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm"><?= __('call_status_distribution') ?></h3>
        <p class="text-xs text-slate-400"><?= __('disposition_analysis') ?></p>
      </div>
    </div>
    <div style="height:185px;position:relative">
      <canvas id="dispChart"></canvas>
    </div>
    <!-- Legend pills -->
    <div class="grid grid-cols-2 gap-1.5 mt-3">
      <?php
      $dispMap = [
        ['ANSWERED', 'bg-emerald-500', 'Cevap',      $answered],
        ['NO ANSWER','bg-slate-400',   'Cevapsız',   $noans],
        ['BUSY',     'bg-amber-500',   'Meşgul',     $busy],
        ['FAILED',   'bg-red-500',     'Başarısız',  $failed],
      ];
      foreach ($dispMap as [$key, $color, $label, $val]): ?>
      <div class="flex items-center gap-1.5">
        <div class="w-2.5 h-2.5 rounded-full <?= $color ?> flex-shrink-0"></div>
        <span class="text-xs text-slate-500 dark:text-slate-400 truncate"><?= $label ?>: <strong class="text-slate-700 dark:text-slate-200"><?= number_format($val) ?></strong></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══════════════ CALL COUNT BAR + TOP AGENTS -->
<div class="grid lg:grid-cols-2 gap-5 mb-6">

  <!-- Daily Call Count Bar -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-5">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center">
        <i class="fa-solid fa-bars-staggered text-white text-sm"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm"><?= __('daily_call_count') ?></h3>
        <p class="text-xs text-slate-400">Günlük çağrı sayısı trendi</p>
      </div>
    </div>
    <div style="height:210px;position:relative">
      <canvas id="callsBar"></canvas>
    </div>
  </div>

  <!-- Top Agents Bar -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-start justify-between mb-5">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
          <i class="fa-solid fa-trophy text-white text-sm"></i>
        </div>
        <div>
          <h3 class="font-bold text-slate-800 dark:text-white text-sm">Top 10 Agent</h3>
          <p class="text-xs text-slate-400">En aktif agentler</p>
        </div>
      </div>
      <div class="flex gap-1.5">
        <button onclick="switchAgentMetric('calls')" id="btn-agent-calls"
                class="agent-metric-btn active px-2 py-1 rounded-lg text-xs font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/40">
          Çağrı
        </button>
        <button onclick="switchAgentMetric('billsec')" id="btn-agent-billsec"
                class="agent-metric-btn px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-600">
          Süre
        </button>
      </div>
    </div>
    <div style="height:210px;position:relative">
      <canvas id="topAgentsChart"></canvas>
    </div>
  </div>
</div>

<!-- ══════════════ SUPERADMIN GROUP TABLE -->
<?php if ($isSuper && !empty($summary)): ?>
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-6">
  <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
        <i class="fa-solid fa-layer-group text-white text-sm"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm">Grup Bazlı Özet</h3>
        <p class="text-xs text-slate-400"><?= count($summary) ?> grup</p>
      </div>
    </div>
    <!-- Sort hint -->
    <span class="text-xs text-slate-400 hidden sm:block">Kâra göre sıralı</span>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-700/50">
          <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Grup</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Çağrı</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cevap %</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Maliyet</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Gelir</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kâr</th>
          <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Kâr %</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
        <?php
        usort($summary, fn($a,$b) => (float)$b['profit'] <=> (float)$a['profit']);
        foreach ($summary as $i => $row):
          $gid = (int)$row['group_id'];
          $gCalls  = (int)$row['calls'];
          $gAns    = (int)($row['answer'] ?? 0);
          $gRate   = $gCalls > 0 ? round($gAns/$gCalls*100,1) : 0;
          $gCost   = (float)$row['cost_api'];
          $gRev    = (float)$row['revenue'];
          $gProfit = (float)$row['profit'];
          $gMargin = $gRev > 0 ? round($gProfit/$gRev*100,1) : 0;
          $profitColor = $gProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400';
        ?>
        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20 transition-colors">
          <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
              <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                <?= $i + 1 ?>
              </div>
              <div>
                <div class="font-semibold text-sm text-slate-800 dark:text-white"><?= htmlspecialchars($groups[$gid] ?? ('#'.$gid)) ?></div>
                <div class="text-xs text-slate-400">ID: <?= $gid ?></div>
              </div>
            </div>
          </td>
          <td class="px-4 py-3.5 text-center">
            <span class="font-bold text-slate-700 dark:text-slate-200"><?= number_format($gCalls) ?></span>
          </td>
          <td class="px-4 py-3.5 text-center">
            <div class="flex items-center justify-center gap-2">
              <div class="w-12 bg-slate-200 dark:bg-slate-600 rounded-full h-1.5 hidden sm:block">
                <div class="h-1.5 rounded-full transition-all duration-700
                  <?= $gRate >= 70 ? 'bg-emerald-500' : ($gRate >= 40 ? 'bg-amber-500' : 'bg-red-500') ?>"
                  style="width:<?= $gRate ?>%"></div>
              </div>
              <span class="font-semibold text-sm <?= $gRate >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($gRate >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-red-500 dark:text-red-400') ?>">
                %<?= $gRate ?>
              </span>
            </div>
          </td>
          <td class="px-4 py-3.5 text-center font-semibold text-sm text-amber-600 dark:text-amber-400">$<?= number_format($gCost, 2) ?></td>
          <td class="px-4 py-3.5 text-center font-semibold text-sm text-blue-600 dark:text-blue-400">$<?= number_format($gRev, 2) ?></td>
          <td class="px-4 py-3.5 text-center">
            <span class="font-bold text-sm <?= $profitColor ?>">
              <?= $gProfit >= 0 ? '+' : '' ?>$<?= number_format($gProfit, 2) ?>
            </span>
          </td>
          <td class="px-4 py-3.5 text-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
              <?= $gMargin >= 20 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'
                 : ($gMargin >= 5 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'
                 : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400') ?>">
              %<?= $gMargin ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <!-- Footer totals -->
      <tfoot>
        <tr class="bg-slate-50 dark:bg-slate-700/50 border-t-2 border-slate-200 dark:border-slate-600">
          <td class="px-5 py-3 font-bold text-sm text-slate-700 dark:text-slate-200">TOPLAM</td>
          <td class="px-4 py-3 text-center font-bold text-sm text-slate-700 dark:text-slate-200"><?= number_format($totCalls) ?></td>
          <td class="px-4 py-3 text-center font-bold text-sm text-slate-700 dark:text-slate-200">%<?= $answerRate ?></td>
          <td class="px-4 py-3 text-center font-bold text-sm text-amber-600 dark:text-amber-400">$<?= number_format($totCost, 2) ?></td>
          <td class="px-4 py-3 text-center font-bold text-sm text-blue-600 dark:text-blue-400">$<?= number_format($totRev, 2) ?></td>
          <td class="px-4 py-3 text-center font-bold text-sm text-emerald-600 dark:text-emerald-400">$<?= number_format($totProfit, 2) ?></td>
          <td class="px-4 py-3 text-center font-bold text-sm text-fuchsia-600 dark:text-fuchsia-400">%<?= $profitMargin ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════ AGENT PERFORMANCE TABLE -->
<?php $hasAgents = !empty($agentsByGroup); ?>
<?php if ($hasAgents): ?>
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-6">
  <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center">
        <i class="fa-solid fa-headset text-white text-sm"></i>
      </div>
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm"><?= __('agent_performance_summary') ?></h3>
        <p class="text-xs text-slate-400">Agent bazlı detaylı istatistikler</p>
      </div>
    </div>
    <!-- Search -->
    <div class="relative hidden sm:block">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input type="text" id="agentSearch" placeholder="Agent ara..."
             class="pl-8 pr-3 py-2 text-xs border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent w-40"
             oninput="filterAgentTable(this.value)">
    </div>
  </div>

  <?php foreach (($agentsByGroup ?? []) as $groupName => $agents): ?>
  <div class="mb-0 group-block">
    <?php if ($isSuper): ?>
    <div class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-slate-50 to-slate-100/50 dark:from-slate-700/40 dark:to-slate-700/20 border-b border-slate-100 dark:border-slate-700/50">
      <i class="fa-solid fa-folder-open text-slate-400 text-sm"></i>
      <span class="font-bold text-sm text-slate-700 dark:text-slate-200"><?= htmlspecialchars($groupName) ?></span>
      <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full text-xs font-semibold"><?= count($agents) ?> agent</span>
    </div>
    <?php endif; ?>
    <div class="overflow-x-auto">
      <table class="min-w-full agent-table">
        <thead>
          <tr class="bg-slate-50/50 dark:bg-slate-700/30">
            <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Agent</th>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              <i class="fa-solid fa-phone text-indigo-400 mr-1"></i>Çağrı
            </th>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              <i class="fa-solid fa-check text-emerald-400 mr-1"></i>Cevap
            </th>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              <i class="fa-solid fa-phone-slash text-red-400 mr-1"></i>Cevapsız
            </th>
            <?php if (!$isGroupMember): ?>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              <i class="fa-solid fa-clock text-blue-400 mr-1"></i>Süre
            </th>
            <?php endif; ?>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Cevap %</th>
            <?php if ($canSeeCost): ?>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
              <i class="fa-solid fa-coins text-amber-400 mr-1"></i>Maliyet
            </th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
          <?php
          usort($agents, fn($a,$b) => (int)$b['calls'] <=> (int)$a['calls']);
          foreach ($agents as $rank => $r):
            $aCalls   = (int)($r['calls']   ?? 0);
            $aAns     = (int)($r['answer']  ?? 0);
            $aNoAns   = $aCalls - $aAns;
            $aBill    = (int)($r['billsec'] ?? 0);
            $aCost    = (float)($r['cost']  ?? 0);
            $aRate    = $aCalls > 0 ? round($aAns/$aCalls*100,1) : 0;
            $rateColor = $aRate >= 70 ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20' : ($aRate >= 40 ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/20');
            $barColor  = $aRate >= 70 ? 'bg-emerald-500' : ($aRate >= 40 ? 'bg-amber-500' : 'bg-red-500');
            $displayName = $r['user_login'] ?? $r['src'] ?? $r['voip_exten'] ?? 'Agent';
            $initials  = strtoupper(mb_substr($displayName, 0, 1));
            $avatarGrads = ['from-indigo-500 to-purple-600','from-rose-500 to-pink-600','from-amber-500 to-orange-600','from-cyan-500 to-blue-600','from-emerald-500 to-teal-600'];
            $grad = $avatarGrads[$rank % count($avatarGrads)];
          ?>
          <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20 transition-colors agent-row">
            <td class="px-5 py-3 agent-name">
              <div class="flex items-center gap-3">
                <div class="relative flex-shrink-0">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br <?= $grad ?> flex items-center justify-center text-white text-xs font-bold">
                    <?= $initials ?>
                  </div>
                  <?php if ($rank === 0): ?>
                  <div class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-star text-white" style="font-size:7px"></i>
                  </div>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="font-semibold text-sm text-slate-800 dark:text-white"><?= htmlspecialchars($displayName) ?></div>
                  <div class="text-xs text-slate-400 font-mono"><?= htmlspecialchars($r['voip_exten'] ?? $r['src'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-center font-bold text-sm text-slate-700 dark:text-slate-200"><?= number_format($aCalls) ?></td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                <?= number_format($aAns) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                <?= number_format($aNoAns) ?>
              </span>
            </td>
            <?php if (!$isGroupMember): ?>
            <td class="px-4 py-3 text-center font-mono text-xs text-slate-600 dark:text-slate-300"><?= gmdate('H:i:s', $aBill) ?></td>
            <?php endif; ?>
            <td class="px-4 py-3">
              <div class="flex items-center justify-center gap-2">
                <div class="w-14 bg-slate-200 dark:bg-slate-600 rounded-full h-1.5 hidden sm:block">
                  <div class="<?= $barColor ?> h-1.5 rounded-full transition-all duration-700" style="width:<?= $aRate ?>%"></div>
                </div>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold <?= $rateColor ?>">
                  %<?= $aRate ?>
                </span>
              </div>
            </td>
            <?php if ($canSeeCost): ?>
            <td class="px-4 py-3 text-center font-semibold text-xs text-amber-600 dark:text-amber-400">
              $<?= number_format($aCost, 2) ?>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══════════════ SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const isDark   = document.documentElement.classList.contains('dark');
const gridC    = isDark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.05)';
const textC    = isDark ? '#94a3b8' : '#64748b';
const tipOpts  = { backgroundColor:'rgba(15,23,42,.92)', titleColor:'#fff', bodyColor:'#e2e8f0', borderColor:'rgba(255,255,255,.1)', borderWidth:1, cornerRadius:10, padding:10 };

// ── Data from PHP
const trendData = <?= json_encode(array_map(fn($t) => [
  'd'       => $t['d'],
  'cost'    => (float)$t['cost'],
  'revenue' => (float)$t['revenue'],
  'calls'   => (int)($t['calls'] ?? 0),
], $trend ?? []), JSON_UNESCAPED_UNICODE) ?>;

const allAgentData = <?= json_encode($allAgents ?? [], JSON_UNESCAPED_UNICODE) ?>;
const dispData     = <?= json_encode($dispRows ?? [], JSON_UNESCAPED_UNICODE) ?>;

let trendChart, agentChart, callsBarChart, dispChart;

// ── Trend Chart
(function() {
  const labels  = trendData.map(t => t.d);
  const cost    = trendData.map(t => t.cost);
  const revenue = trendData.map(t => t.revenue);
  const ctx = document.getElementById('trendChart');
  if (!ctx) return;
  trendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Maliyet', data: cost,
          borderColor:'rgba(239,68,68,1)', backgroundColor:'rgba(239,68,68,.1)',
          borderWidth:2.5, fill:true, tension:.4,
          pointBackgroundColor:'rgba(239,68,68,1)', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        },
        <?php if ($isSuper): ?>
        {
          label: 'Gelir', data: revenue,
          borderColor:'rgba(16,185,129,1)', backgroundColor:'rgba(16,185,129,.1)',
          borderWidth:2.5, fill:true, tension:.4,
          pointBackgroundColor:'rgba(16,185,129,1)', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4, pointHoverRadius:7,
        },
        <?php endif; ?>
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      interaction:{ mode:'index', intersect:false },
      plugins:{ legend:{ display:false }, tooltip:{ ...tipOpts, callbacks:{ label: c => c.dataset.label+': $'+c.parsed.y.toFixed(2) } } },
      scales:{
        y:{ beginAtZero:true, grid:{ color:gridC }, ticks:{ color:textC, font:{size:11}, callback: v=>'$'+v.toFixed(2) } },
        x:{ grid:{ display:false }, ticks:{ color:textC, font:{size:11} } }
      },
      animation:{ duration:1200, easing:'easeInOutQuart' }
    }
  });
})();

// ── Calls Bar
(function() {
  const labels = trendData.map(t => t.d);
  const calls  = trendData.map(t => t.calls);
  const ctx = document.getElementById('callsBar');
  if (!ctx) return;
  callsBarChart = new Chart(ctx, {
    type:'bar',
    data:{
      labels,
      datasets:[{
        label:'Çağrı', data:calls,
        backgroundColor:'rgba(99,102,241,.75)', borderColor:'rgba(99,102,241,1)',
        borderWidth:1.5, borderRadius:6, borderSkipped:false,
        hoverBackgroundColor:'rgba(99,102,241,1)',
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ display:false }, tooltip:{ ...tipOpts, callbacks:{ label: c=>'Çağrı: '+c.parsed.y } } },
      scales:{
        y:{ beginAtZero:true, grid:{ color:gridC }, ticks:{ color:textC, font:{size:11} } },
        x:{ grid:{ display:false }, ticks:{ color:textC, font:{size:11} } }
      },
      animation:{ duration:1200, easing:'easeInOutQuart', delay: c => c.dataIndex * 60 }
    }
  });
})();

// ── Disposition Donut
(function() {
  const labels = dispData.map(d => d.d);
  const data   = dispData.map(d => +d.n||0);
  const ctx = document.getElementById('dispChart');
  if (!ctx) return;
  dispChart = new Chart(ctx, {
    type:'doughnut',
    data:{
      labels,
      datasets:[{ data, backgroundColor:['rgba(16,185,129,.85)','rgba(148,163,184,.85)','rgba(245,158,11,.85)','rgba(239,68,68,.85)','rgba(99,102,241,.85)'], borderColor:isDark?'#1e293b':'#fff', borderWidth:3, hoverOffset:6 }]
    },
    options:{
      responsive:true, maintainAspectRatio:false, cutout:'68%',
      plugins:{
        legend:{ display:false },
        tooltip:{ ...tipOpts, callbacks:{ label: c => { const total=c.dataset.data.reduce((a,b)=>a+b,0); return c.label+': '+c.parsed+' (%'+((c.parsed/total)*100).toFixed(1)+')'; } } }
      },
      animation:{ animateRotate:true, duration:1200 }
    }
  });
})();

// ── Top Agents
function buildAgentChart(metric) {
  const sorted = [...allAgentData].sort((a,b)=>(+b[metric])-(+a[metric])).slice(0,10);
  const labels = sorted.map(a => a.user_login || a.voip_exten || 'agent');
  const data   = sorted.map(a => +a[metric]||0);
  const colors = ['rgba(99,102,241,.8)','rgba(16,185,129,.8)','rgba(245,158,11,.8)','rgba(239,68,68,.8)','rgba(139,92,246,.8)','rgba(236,72,153,.8)','rgba(6,182,212,.8)','rgba(34,197,94,.8)','rgba(251,146,60,.8)','rgba(168,85,247,.8)'];
  const ctx = document.getElementById('topAgentsChart');
  if (!ctx) return;
  if (agentChart) agentChart.destroy();
  agentChart = new Chart(ctx, {
    type:'bar',
    data:{ labels, datasets:[{ label:metric==='calls'?'Çağrı':'Süre (sn)', data, backgroundColor:colors, borderRadius:6, borderWidth:0, hoverBackgroundColor:colors.map(c=>c.replace('.8','.95')) }] },
    options:{
      responsive:true, maintainAspectRatio:false, indexAxis:'y',
      plugins:{ legend:{ display:false }, tooltip:{ ...tipOpts, callbacks:{ label: c=>c.parsed.x+(metric==='calls'?' çağrı':' sn') } } },
      scales:{
        x:{ beginAtZero:true, grid:{ color:gridC }, ticks:{ color:textC, font:{size:11} } },
        y:{ grid:{ display:false }, ticks:{ color:textC, font:{size:11,weight:'600'} } }
      },
      animation:{ duration:900, easing:'easeInOutQuart', delay: c=>c.dataIndex*60 }
    }
  });
}
buildAgentChart('calls');

function switchAgentMetric(metric) {
  buildAgentChart(metric);
  document.querySelectorAll('.agent-metric-btn').forEach(b => {
    b.className = b.className.replace(/bg-indigo-100[^\s]*/g,'').replace(/text-indigo-600[^\s]*/g,'').replace(/border-indigo-200[^\s]*/g,'').replace(/dark:bg-indigo-900[^\s]*/g,'').replace(/dark:text-indigo-300[^\s]*/g,'').replace(/dark:border-indigo-700[^\s]*/g,'').trim();
    b.classList.add('bg-slate-100','dark:bg-slate-700','text-slate-500','dark:text-slate-400','border-slate-200','dark:border-slate-600');
  });
  const active = document.getElementById('btn-agent-'+metric);
  if (active) {
    active.classList.remove('bg-slate-100','dark:bg-slate-700','text-slate-500','dark:text-slate-400','border-slate-200','dark:border-slate-600');
    active.classList.add('bg-indigo-100','dark:bg-indigo-900/30','text-indigo-600','dark:text-indigo-300','border-indigo-200','dark:border-indigo-700/40');
  }
}

// ── Toggle dataset visibility
function toggleDataset(chartName, idx) {
  const chart = chartName === 'trend' ? trendChart : null;
  if (!chart) return;
  const meta = chart.getDatasetMeta(idx);
  meta.hidden = !meta.hidden;
  chart.update();
  const btn = idx === 0 ? document.getElementById('btn-cost') : document.getElementById('btn-rev');
  if (btn) btn.style.opacity = meta.hidden ? '.4' : '1';
}

// ── Date presets
function setPreset(range, btn) {
  const now = new Date();
  const pad = n => String(n).padStart(2,'0');
  const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  let f, t;
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  switch(range) {
    case 'today':     f = t = today; break;
    case 'yesterday': const y=new Date(today); y.setDate(y.getDate()-1); f=t=y; break;
    case 'week':      f=new Date(today); f.setDate(f.getDate()-today.getDay()); t=today; break;
    case 'month':     f=new Date(today.getFullYear(),today.getMonth(),1); t=today; break;
    case 'lastmonth': f=new Date(today.getFullYear(),today.getMonth()-1,1); t=new Date(today.getFullYear(),today.getMonth(),0); break;
    case 'last7':     f=new Date(today); f.setDate(f.getDate()-6); t=today; break;
    case 'last30':    f=new Date(today); f.setDate(f.getDate()-29); t=today; break;
    default: return;
  }
  document.getElementById('from-date').value = fmt(f)+'T00:00';
  document.getElementById('to-date').value   = fmt(t)+'T23:59';
  document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('bg-indigo-600','text-white','border-indigo-600'));
  if (btn) { btn.classList.add('bg-indigo-600','text-white','border-indigo-600'); }
}

// ── Agent search
function filterAgentTable(query) {
  const q = query.toLowerCase();
  document.querySelectorAll('.agent-row').forEach(row => {
    const name = row.querySelector('.agent-name')?.textContent.toLowerCase() || '';
    row.style.display = name.includes(q) ? '' : 'none';
  });
}

// ── Export
function exportReport(type) {
  document.getElementById('loading-overlay').classList.remove('hidden');
  const from = document.getElementById('from-date').value;
  const to   = document.getElementById('to-date').value;
  const gid  = document.querySelector('input[name="group_id"]')?.value || '';
  const url  = `/VoipPanelAi/reports/export?type=${type}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&group_id=${gid}`;
  const a = document.createElement('a');
  a.href = url; a.download = `rapor_${new Date().toISOString().split('T')[0]}.${type}`;
  document.body.appendChild(a); a.click(); document.body.removeChild(a);
  setTimeout(() => document.getElementById('loading-overlay').classList.add('hidden'), 1500);
}

// ── Form submit loader
document.getElementById('filterForm').addEventListener('submit', () => {
  document.getElementById('loading-overlay').classList.remove('hidden');
});

// ── Resize charts
window.addEventListener('resize', () => {
  setTimeout(() => {
    [trendChart, agentChart, callsBarChart, dispChart].forEach(c => c?.resize());
  }, 200);
}, {passive:true});
</script>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>