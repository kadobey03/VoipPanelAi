<?php $title = __('cdr_history') . ' - ' . __('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
<?php
$isSuper      = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'superadmin';
$isGroupAdmin = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'groupadmin';
$isGroupMember= isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'groupmember';
$canSeeCost   = $isSuper || $isGroupAdmin;
$canSeeBillsec= !$isGroupMember;

// Pagination & sort helpers
$page       = (int)($_GET['page'] ?? 1);
$per        = (int)($_GET['per']  ?? 25);
$totalPages = $totalPages ?? 1;
$totalCalls = $totalCalls ?? 0;
$stats      = $stats ?? [];
$sortCol    = $_GET['sort'] ?? 'start';
$sortDir    = strtoupper($_GET['dir'] ?? 'DESC');

function buildUrl(array $overrides = []): string {
    $q = array_merge($_GET, $overrides);
    return \App\Helpers\Url::to('/calls/history') . '?' . http_build_query($q);
}
function sortUrl(string $col): string {
    global $sortCol, $sortDir;
    $dir = ($sortCol === $col && $sortDir === 'DESC') ? 'ASC' : 'DESC';
    return buildUrl(['sort' => $col, 'dir' => $dir, 'page' => 1]);
}
function sortIcon(string $col): string {
    global $sortCol, $sortDir;
    if ($sortCol !== $col) return '<i class="fa-solid fa-sort text-slate-400 ml-1 text-xs"></i>';
    return $sortDir === 'ASC'
        ? '<i class="fa-solid fa-sort-up text-indigo-500 ml-1 text-xs"></i>'
        : '<i class="fa-solid fa-sort-down text-indigo-500 ml-1 text-xs"></i>';
}

$totalAnswered  = (int)($stats['answered']   ?? 0);
$totalNoAnswer  = (int)($stats['no_answer']  ?? 0);
$totalBusy      = (int)($stats['busy']       ?? 0);
$totalFailed    = (int)($stats['failed']     ?? 0);
$totalBillsec   = (int)($stats['total_billsec'] ?? 0);
$totalCostApi   = (float)($stats['total_cost_api'] ?? 0);
$totalCharged   = (float)($stats['total_charged']  ?? 0);
$answerRate     = $totalCalls > 0 ? round($totalAnswered / $totalCalls * 100, 1) : 0;
$noAnswerRate   = $totalCalls > 0 ? round(($totalNoAnswer + $totalBusy) / $totalCalls * 100, 1) : 0;
$avgBillsec     = $totalAnswered > 0 ? (int)round($totalBillsec / $totalAnswered) : 0;
$billsecFormatted = sprintf('%dsa %02ddak', floor($totalBillsec/3600), floor(($totalBillsec%3600)/60));
$avgFormatted   = sprintf('%ddak %02dsn', floor($avgBillsec/60), $avgBillsec%60);

// Türkçe disposition etiketleri
function dispLabel(string $d): string {
    return match(strtoupper($d)) {
        'ANSWERED'          => 'Cevaplandı',
        'NO ANSWER','NO_ANSWER' => 'Cevapsız',
        'BUSY'              => 'Meşgul',
        'FAILED'            => 'Başarısız',
        default             => htmlspecialchars($d),
    };
}
?>

<!-- ═══════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
  <div class="flex items-center gap-4">
    <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl shadow-lg shadow-indigo-500/30">
      <i class="fa-solid fa-table-list text-white text-2xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white tracking-tight">
        <?= __('cdr_history') ?>
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5">
        <?php if ($totalCalls > 0): ?>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-semibold">
            <i class="fa-solid fa-circle-dot text-xs"></i>
            <?= number_format($totalCalls) ?> <?= __('total_calls') ?>
          </span>
        <?php else: ?>
          <span class="text-slate-400 dark:text-slate-500"><?= __('call_detail_records') ?></span>
        <?php endif; ?>
      </p>
    </div>
  </div>
  <button onclick="exportToCSV()"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all duration-200 text-sm">
    <i class="fa-solid fa-file-csv text-base"></i>
    <span><?= __('export_excel') ?></span>
  </button>
</div>

<!-- ═══════════════════════════════════════════════════════
     FILTER PANEL
═══════════════════════════════════════════════════════ -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 mb-5 overflow-hidden">
  <!-- Filter Header -->
  <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/60 dark:bg-slate-800/60">
    <div class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
      <i class="fa-solid fa-sliders text-indigo-500"></i>
      <?= __('filter') ?? 'Filtrele' ?>
    </div>
    <!-- Quick Presets -->
    <div class="flex items-center gap-1.5 flex-wrap">
      <span class="text-xs text-slate-400 dark:text-slate-500 mr-1 hidden sm:inline"><i class="fa-solid fa-bolt text-yellow-400"></i></span>
      <?php
      $presets = [
        'today'     => __('today')      ?? 'Bugün',
        'yesterday' => __('yesterday')  ?? 'Dün',
        'last7'     => __('last_7_days') ?? 'Son 7 Gün',
        'week'      => __('this_week')  ?? 'Bu Hafta',
        'month'     => __('this_month') ?? 'Bu Ay',
      ];
      foreach ($presets as $key => $label): ?>
        <button type="button" onclick="setQuickDate('<?= $key ?>')"
                class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 hover:bg-indigo-100 dark:bg-slate-700 dark:hover:bg-indigo-900/40 text-slate-600 dark:text-slate-300 hover:text-indigo-700 dark:hover:text-indigo-300 transition-all border border-transparent hover:border-indigo-200 dark:hover:border-indigo-700">
          <?= $label ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Filter Body -->
  <form method="get" action="<?= \App\Helpers\Url::to('/calls/history') ?>" id="filterForm" class="p-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">

      <!-- Start Date -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-calendar-days mr-1 text-indigo-400"></i><?= __('start_date') ?>
        </label>
        <input type="datetime-local" name="from"
               value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-d\TH:i', strtotime('-1 day'))) ?>"
               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder-slate-400">
      </div>

      <!-- End Date -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-calendar-check mr-1 text-indigo-400"></i><?= __('end_date') ?>
        </label>
        <input type="datetime-local" name="to"
               value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d\TH:i')) ?>"
               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
      </div>

      <!-- Src -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-phone mr-1 text-emerald-500"></i><?= __('src') ?>
        </label>
        <input name="src" value="<?= htmlspecialchars($_GET['src'] ?? '') ?>"
               placeholder="<?= __('caller_initiator') ?>"
               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all placeholder-slate-400">
      </div>

      <!-- Dst -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-phone-flip mr-1 text-purple-500"></i><?= __('dst') ?>
        </label>
        <input name="dst" value="<?= htmlspecialchars($_GET['dst'] ?? '') ?>"
               placeholder="<?= __('called_number') ?>"
               class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all placeholder-slate-400">
      </div>

      <!-- Disposition -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-circle-half-stroke mr-1 text-orange-500"></i><?= __('disposition') ?>
        </label>
        <select name="disposition"
                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
          <option value=""><?= __('all_dispositions') ?? 'Tümü' ?></option>
          <option value="ANSWERED"  <?= (($_GET['disposition'] ?? '') === 'ANSWERED')  ? 'selected' : '' ?>>Cevaplanan</option>
          <option value="NO ANSWER" <?= (($_GET['disposition'] ?? '') === 'NO ANSWER') ? 'selected' : '' ?>>Cevapsız</option>
          <option value="BUSY"      <?= (($_GET['disposition'] ?? '') === 'BUSY')      ? 'selected' : '' ?>>Meşgul</option>
          <option value="FAILED"    <?= (($_GET['disposition'] ?? '') === 'FAILED')    ? 'selected' : '' ?>>Başarısız</option>
        </select>
      </div>

      <?php if ($isSuper): ?>
      <!-- Group Filter -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-users mr-1 text-rose-500"></i><?= __('group') ?>
        </label>
        <select name="group_id"
                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
          <option value=""><?= __('all_groups') ?></option>
          <?php foreach (($groups ?? []) as $g): $gid = (int)$g['id']; ?>
            <option value="<?= $gid ?>" <?= (isset($_GET['group_id']) && (int)$_GET['group_id'] === $gid) ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <!-- Per Page -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid fa-list-ol mr-1 text-cyan-500"></i><?= __('count_per_page') ?>
        </label>
        <select name="per"
                class="w-full px-3 py-2 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700/60 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
          <?php foreach ([10, 25, 50, 100, 200] as $p): ?>
            <option value="<?= $p ?>" <?= $per === $p ? 'selected' : '' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>

    </div>

    <!-- Action Row -->
    <div class="flex flex-col sm:flex-row gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700/60">
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sortCol) ?>">
      <input type="hidden" name="dir"  value="<?= htmlspecialchars($sortDir) ?>">
      <input type="hidden" name="page" value="1">
      <button type="submit"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5 transition-all duration-200 text-sm">
        <i class="fa-solid fa-magnifying-glass"></i>
        <?= __('search') ?>
      </button>
      <a href="<?= \App\Helpers\Url::to('/calls/history') ?>"
         class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-semibold rounded-xl transition-all duration-200 text-sm">
        <i class="fa-solid fa-rotate-left"></i>
        <?= __('clear') ?>
      </a>
    </div>
  </form>
</div>

<!-- ═══════════════════════════════════════════════════════
     KPI CARDS
═══════════════════════════════════════════════════════ -->
<?php if ($totalCalls > 0): ?>
<div class="grid grid-cols-2 <?= $canSeeBillsec ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-4 mb-5">

  <!-- Total Calls -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide"><?= __('total_calls') ?></span>
      <span class="w-8 h-8 flex items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
        <i class="fa-solid fa-phone text-blue-600 dark:text-blue-400 text-sm"></i>
      </span>
    </div>
    <span class="text-3xl font-bold text-slate-800 dark:text-white"><?= number_format($totalCalls) ?></span>
    <div class="text-xs text-slate-400 dark:text-slate-500">
      <?= __('cdr_page_results') ?>
    </div>
  </div>

  <!-- Answered -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-emerald-200/70 dark:border-emerald-700/30 shadow-sm p-4 flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide"><?= __('answered') ?></span>
      <span class="w-8 h-8 flex items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
        <i class="fa-solid fa-phone-volume text-emerald-600 dark:text-emerald-400 text-sm"></i>
      </span>
    </div>
    <span class="text-3xl font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($totalAnswered) ?></span>
    <div class="space-y-1">
      <div class="flex items-center justify-between text-xs text-slate-400">
        <span><?= __('answer_rate') ?></span>
        <span class="font-semibold text-emerald-600 dark:text-emerald-400"><?= $answerRate ?>%</span>
      </div>
      <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
        <div class="h-full bg-emerald-500 rounded-full transition-all" style="width:<?= $answerRate ?>%"></div>
      </div>
    </div>
  </div>

  <!-- Missed -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-red-200/70 dark:border-red-700/30 shadow-sm p-4 flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold text-red-500 dark:text-red-400 uppercase tracking-wide"><?= __('no_answer') ?></span>
      <span class="w-8 h-8 flex items-center justify-center rounded-xl bg-red-100 dark:bg-red-900/30">
        <i class="fa-solid fa-phone-slash text-red-500 dark:text-red-400 text-sm"></i>
      </span>
    </div>
    <span class="text-3xl font-bold text-red-500 dark:text-red-400"><?= number_format($totalNoAnswer + $totalBusy) ?></span>
    <div class="space-y-1">
      <div class="flex items-center justify-between text-xs text-slate-400">
        <span><?= __('busy') ?>: <?= $totalBusy ?> / <?= __('no_answer') ?>: <?= $totalNoAnswer ?></span>
        <span class="font-semibold text-red-500 dark:text-red-400"><?= $noAnswerRate ?>%</span>
      </div>
      <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
        <div class="h-full bg-red-500 rounded-full transition-all" style="width:<?= $noAnswerRate ?>%"></div>
      </div>
    </div>
  </div>

  <!-- Duration / Avg -->
  <?php if ($canSeeBillsec): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-cyan-200/70 dark:border-cyan-700/30 shadow-sm p-4 flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 uppercase tracking-wide"><?= __('billsec') ?></span>
      <span class="w-8 h-8 flex items-center justify-center rounded-xl bg-cyan-100 dark:bg-cyan-900/30">
        <i class="fa-solid fa-stopwatch text-cyan-600 dark:text-cyan-400 text-sm"></i>
      </span>
    </div>
    <span class="text-xl font-bold text-cyan-600 dark:text-cyan-400"><?= $billsecFormatted ?></span>
    <div class="text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
      <i class="fa-solid fa-calculator text-xs"></i>
      <?= __('total_talk_time') ?> · Ort. <?= $avgFormatted ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<!-- Cost Cards (superadmin/groupadmin) -->
<?php if ($canSeeCost): ?>
<div class="grid grid-cols-2 <?= $isSuper ? 'lg:grid-cols-2' : 'lg:grid-cols-1' ?> gap-4 mb-5">
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-green-200/70 dark:border-green-700/30 shadow-sm p-4 flex items-center gap-4">
    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-green-100 dark:bg-green-900/30 shrink-0">
      <i class="fa-solid fa-coins text-green-600 dark:text-green-400"></i>
    </span>
    <div>
      <div class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide"><?= __('charged_amount') ?></div>
      <div class="text-xl font-bold text-green-600 dark:text-green-400">$<?= number_format($totalCharged, 4) ?></div>
      <div class="text-xs text-slate-400"><?= __('cdr_total_billed') ?></div>
    </div>
  </div>
  <?php if ($isSuper): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-yellow-200/70 dark:border-yellow-700/30 shadow-sm p-4 flex items-center gap-4">
    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-yellow-100 dark:bg-yellow-900/30 shrink-0">
      <i class="fa-solid fa-dollar-sign text-yellow-600 dark:text-yellow-400"></i>
    </span>
    <div>
      <div class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase tracking-wide"><?= __('cost_api') ?></div>
      <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">$<?= number_format($totalCostApi, 4) ?></div>
      <div class="text-xs text-slate-400"><?= __('cdr_api_cost') ?></div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     RESULTS INFO BAR
═══════════════════════════════════════════════════════ -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 gap-2">
  <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
    <i class="fa-solid fa-list-ul text-indigo-400"></i>
    <?php if ($totalCalls > 0): ?>
      <span>
        <span class="font-semibold text-slate-700 dark:text-slate-200"><?= number_format($totalCalls) ?></span> <?= __('total_calls') ?>
        <span class="mx-1.5 text-slate-300 dark:text-slate-600">·</span>
        <?= sprintf(__('page_info'), $page, $totalPages) ?>
        <span class="mx-1.5 text-slate-300 dark:text-slate-600">·</span>
        <?= number_format(($page - 1) * $per + 1) ?>–<?= number_format(min($page * $per, $totalCalls)) ?> <?= __('cdr_shown') ?>
      </span>
    <?php else: ?>
      <span><?= __('no_records_found') ?></span>
    <?php endif; ?>
  </div>
  <?php if ($totalCalls > 0): ?>
  <div class="flex items-center gap-1.5 text-xs">
    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-medium">
      <i class="fa-solid fa-check"></i> <?= $totalAnswered ?> <?= __('answered') ?>
    </span>
    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 font-medium">
      <i class="fa-solid fa-xmark"></i> <?= $totalNoAnswer + $totalBusy ?> <?= __('missed') ?>
    </span>
  </div>
  <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════
     DATA TABLE
═══════════════════════════════════════════════════════ -->
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-5">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
      <!-- Sticky Header -->
      <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-900/70 backdrop-blur-sm">
        <tr>
          <th class="px-3 py-3 text-left">
            <a href="<?= sortUrl('start') ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
              <i class="fa-solid fa-calendar-days text-indigo-400"></i><?= __('date') ?><?= sortIcon('start') ?>
            </a>
          </th>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-phone text-emerald-500"></i><?= __('src') ?>
            </span>
          </th>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-phone-flip text-purple-500"></i><?= __('dst') ?>
            </span>
          </th>
          <?php if ($isSuper): ?>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-users text-blue-500"></i><?= __('group') ?>
            </span>
          </th>
          <?php endif; ?>
          <th class="px-3 py-3 text-left">
            <a href="<?= sortUrl('duration') ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
              <i class="fa-solid fa-clock text-slate-400"></i><?= __('duration') ?><?= sortIcon('duration') ?>
            </a>
          </th>
          <?php if ($canSeeBillsec): ?>
          <th class="px-3 py-3 text-left">
            <a href="<?= sortUrl('billsec') ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
              <i class="fa-solid fa-stopwatch text-red-400"></i><?= __('billsec') ?><?= sortIcon('billsec') ?>
            </a>
          </th>
          <?php endif; ?>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-circle-half-stroke text-orange-500"></i><?= __('disposition') ?>
            </span>
          </th>
          <?php if ($canSeeCost): ?>
          <th class="px-3 py-3 text-left">
            <a href="<?= sortUrl('amount_charged') ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
              <i class="fa-solid fa-coins text-cyan-500"></i><?= __('charged_amount') ?><?= sortIcon('amount_charged') ?>
            </a>
          </th>
          <?php endif; ?>
          <?php if ($isSuper): ?>
          <th class="px-3 py-3 text-left">
            <a href="<?= sortUrl('cost_api') ?>"
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors whitespace-nowrap">
              <i class="fa-solid fa-dollar-sign text-green-500"></i><?= __('cost_api') ?><?= sortIcon('cost_api') ?>
            </a>
          </th>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-percent text-yellow-500"></i><?= __('margin_percent') ?>
            </span>
          </th>
          <?php endif; ?>
          <th class="px-3 py-3 text-left">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
              <i class="fa-solid fa-headphones text-pink-500"></i><?= __('record') ?>
            </span>
          </th>
          <th class="px-3 py-3 text-center w-12">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-eye text-violet-500"></i>
            </span>
          </th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
        <?php if (!empty($calls ?? [])): ?>
          <?php foreach ($calls as $index => $c): ?>
          <?php
            $disp = strtoupper($c['disposition']);
            $dispBadge = match(true) {
                $disp === 'ANSWERED'                        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50',
                in_array($disp, ['NO ANSWER','NO_ANSWER'])  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-700/50',
                $disp === 'BUSY'                            => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200 dark:border-amber-700/50',
                $disp === 'FAILED'                          => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border border-red-200 dark:border-red-700/50',
                default                                     => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600',
            };
            $dispIcon = match(true) {
                $disp === 'ANSWERED'                        => 'fa-phone text-emerald-500',
                in_array($disp, ['NO ANSWER','NO_ANSWER'])  => 'fa-phone-slash text-red-400',
                $disp === 'BUSY'                            => 'fa-phone-volume text-amber-500',
                $disp === 'FAILED'                          => 'fa-triangle-exclamation text-red-500',
                default                                     => 'fa-circle-question text-slate-400',
            };
          ?>
          <tr class="hover:bg-indigo-50/50 dark:hover:bg-slate-700/30 transition-colors duration-100">
            <!-- Date -->
            <td class="px-3 py-2.5 whitespace-nowrap">
              <div class="font-medium text-slate-800 dark:text-slate-200 text-sm"><?= date('d.m.Y', strtotime($c['start'])) ?></div>
              <div class="text-xs text-slate-400 dark:text-slate-500 font-mono"><?= date('H:i:s', strtotime($c['start'])) ?></div>
            </td>
            <!-- Src -->
            <td class="px-3 py-2.5 whitespace-nowrap font-mono font-semibold text-sm text-emerald-600 dark:text-emerald-400">
              <?= htmlspecialchars($c['src']) ?>
            </td>
            <!-- Dst -->
            <td class="px-3 py-2.5 whitespace-nowrap font-mono font-semibold text-sm text-purple-600 dark:text-purple-400">
              <?= htmlspecialchars($c['dst']) ?>
            </td>
            <!-- Group (superadmin) -->
            <?php if ($isSuper): ?>
            <?php $gid = (int)$c['group_id']; $gn = $groupNamesById[$gid] ?? ($groupNamesByApi[$gid] ?? ('#'.$gid)); ?>
            <td class="px-3 py-2.5 whitespace-nowrap">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 max-w-[120px] truncate">
                <i class="fa-solid fa-users text-xs shrink-0"></i><span class="truncate"><?= htmlspecialchars($gn) ?></span>
              </span>
            </td>
            <?php endif; ?>
            <!-- Duration -->
            <td class="px-3 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-400 font-mono text-xs">
              <?= gmdate('H:i:s', (int)$c['duration']) ?>
            </td>
            <!-- Billsec -->
            <?php if ($canSeeBillsec): ?>
            <td class="px-3 py-2.5 whitespace-nowrap font-mono text-xs <?= (int)$c['billsec'] > 0 ? 'text-red-500 dark:text-red-400 font-semibold' : 'text-slate-400 dark:text-slate-600' ?>">
              <?= gmdate('H:i:s', (int)$c['billsec']) ?>
            </td>
            <?php endif; ?>
            <!-- Disposition Badge -->
            <td class="px-3 py-2.5 whitespace-nowrap">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $dispBadge ?>">
                <i class="fa-solid <?= $dispIcon ?> text-xs"></i>
                <?= dispLabel($c['disposition']) ?>
              </span>
            </td>
            <!-- Charged Amount -->
            <?php if ($canSeeCost): ?>
            <td class="px-3 py-2.5 whitespace-nowrap font-mono text-xs <?= (float)$c['amount_charged'] > 0 ? 'text-cyan-600 dark:text-cyan-400 font-semibold' : 'text-slate-300 dark:text-slate-600' ?>">
              <?= (float)$c['amount_charged'] > 0 ? '$'.number_format((float)$c['amount_charged'], 6) : '—' ?>
            </td>
            <?php endif; ?>
            <!-- Cost API (superadmin) -->
            <?php if ($isSuper): ?>
            <td class="px-3 py-2.5 whitespace-nowrap font-mono text-xs text-green-600 dark:text-green-400">
              <?= $c['cost_api'] > 0 ? '$'.number_format((float)$c['cost_api'], 6) : '<span class="text-slate-300 dark:text-slate-600">—</span>' ?>
            </td>
            <!-- Margin -->
            <td class="px-3 py-2.5 whitespace-nowrap">
              <?php $margin = (float)$c['margin_percent']; ?>
              <?php if ($margin > 0): ?>
              <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold
                <?= $margin > 50 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($margin > 20 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300') ?>">
                <?= number_format($margin, 1) ?>%
              </span>
              <?php else: ?>
                <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <!-- Record -->
            <td class="px-3 py-2.5 whitespace-nowrap">
              <?php if (!empty($c['call_id']) && $disp === 'ANSWERED'): ?>
                <button onclick="playAudio('<?= htmlspecialchars($c['call_id']) ?>')"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-pink-100 text-pink-700 hover:bg-pink-200 dark:bg-pink-900/40 dark:text-pink-300 dark:hover:bg-pink-900/60 transition-colors">
                  <i class="fa-solid fa-play text-xs"></i>
                  <span class="hidden sm:inline"><?= __('listen') ?></span>
                </button>
              <?php else: ?>
                <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
              <?php endif; ?>
            </td>
            <!-- Detail Button -->
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
              <button onclick="showCallDetails(<?= $index ?>)"
                      title="<?= __('call_details') ?>"
                      class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs bg-violet-100 text-violet-700 hover:bg-violet-200 dark:bg-violet-900/40 dark:text-violet-300 dark:hover:bg-violet-900/60 transition-colors">
                <i class="fa-solid fa-eye"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="20" class="px-4 py-20 text-center">
              <div class="flex flex-col items-center justify-center gap-4">
                <div class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center">
                  <i class="fa-solid fa-inbox text-4xl text-slate-300 dark:text-slate-500"></i>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-1"><?= __('no_records_found') ?></h3>
                  <p class="text-sm text-slate-400 dark:text-slate-500"><?= __('no_records_message') ?></p>
                </div>
                <a href="<?= \App\Helpers\Url::to('/calls/history') ?>"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-semibold">
                  <i class="fa-solid fa-rotate-left"></i><?= __('clear') ?>
                </a>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PAGINATION
═══════════════════════════════════════════════════════ -->
<?php if ($totalPages > 1): ?>
<div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 px-5 py-4">
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

    <!-- Info -->
    <div class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2 whitespace-nowrap">
      <i class="fa-solid fa-layer-group text-indigo-400"></i>
      <span>
        <?= sprintf(__('page_info'), $page, $totalPages) ?>
        <span class="mx-1.5 text-slate-300 dark:text-slate-600">·</span>
        <?= $per ?> <?= __('cdr_per_page') ?>
        <span class="mx-1.5 text-slate-300 dark:text-slate-600">·</span>
        <span class="font-semibold text-slate-700 dark:text-slate-200"><?= number_format($totalCalls) ?></span> <?= __('cdr_total') ?>
      </span>
    </div>

    <!-- Page Numbers -->
    <div class="flex items-center gap-1 flex-wrap justify-center">
      <?php if ($page > 1): ?>
        <a href="<?= buildUrl(['page' => 1]) ?>" title="<?= __('first_page') ?>"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300 transition-all text-xs shadow-sm">
          <i class="fa-solid fa-angles-left"></i>
        </a>
        <a href="<?= buildUrl(['page' => $page - 1]) ?>" title="<?= __('previous_page') ?>"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300 transition-all text-xs shadow-sm">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
      <?php endif; ?>

      <?php
      $window = [1];
      for ($i = max(2, $page - 2); $i <= min($totalPages - 1, $page + 2); $i++) { $window[] = $i; }
      if ($totalPages > 1) $window[] = $totalPages;
      $window = array_unique($window);
      $prev = null;
      foreach ($window as $pn):
        if ($prev !== null && $pn - $prev > 1): ?>
          <span class="w-9 h-9 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm select-none">…</span>
        <?php endif; ?>
        <?php $isActive = ($pn === $page); ?>
        <a href="<?= buildUrl(['page' => $pn]) ?>"
           class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-semibold transition-all shadow-sm
             <?= $isActive
                 ? 'bg-gradient-to-br from-indigo-600 to-blue-600 text-white border border-indigo-600 shadow-indigo-200 dark:shadow-indigo-900/30'
                 : 'bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300' ?>">
          <?= $pn ?>
        </a>
      <?php $prev = $pn; endforeach; ?>

      <?php if ($page < $totalPages): ?>
        <a href="<?= buildUrl(['page' => $page + 1]) ?>" title="<?= __('next_page') ?>"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300 transition-all text-xs shadow-sm">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
        <a href="<?= buildUrl(['page' => $totalPages]) ?>" title="<?= __('last_page') ?>"
           class="w-9 h-9 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 dark:hover:text-indigo-300 hover:border-indigo-300 transition-all text-xs shadow-sm">
          <i class="fa-solid fa-angles-right"></i>
        </a>
      <?php endif; ?>
    </div>

    <!-- Go to page -->
    <form method="get" action="<?= \App\Helpers\Url::to('/calls/history') ?>" class="flex items-center gap-2">
      <?php foreach (array_diff_key($_GET, ['page' => 1]) as $k => $v): ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
      <?php endforeach; ?>
      <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= __('go_to_page') ?></span>
      <input type="number" name="page" min="1" max="<?= $totalPages ?>" value="<?= $page ?>"
             class="w-16 px-2 py-1.5 text-sm text-center border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
      <button type="submit"
              class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition-colors font-semibold">
        <?= __('go') ?>
      </button>
    </form>

  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     CALL DETAIL MODAL
═══════════════════════════════════════════════════════ -->
<div id="callModal"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 items-center justify-center"
     onclick="if(event.target===this)closeModal()">
  <div class="flex items-center justify-center min-h-screen p-4 w-full">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto border border-slate-200 dark:border-slate-700 animate-fade-up">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
          <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl">
            <i class="fa-solid fa-phone text-indigo-600 dark:text-indigo-400"></i>
          </span>
          <?= __('call_details') ?>
        </h3>
        <button onclick="closeModal()"
                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div id="modalContent" class="p-5"></div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     AUDIO MODAL
═══════════════════════════════════════════════════════ -->
<div id="audioModal"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50"
     onclick="if(event.target===this)closeAudioModal()">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700">
      <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-3">
          <span class="p-2 bg-pink-100 dark:bg-pink-900/40 rounded-xl">
            <i class="fa-solid fa-headphones text-pink-600 dark:text-pink-400"></i>
          </span>
          <?= __('call_record') ?>
        </h3>
        <button onclick="closeAudioModal()"
                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>
      <div class="p-5">
        <div id="audioLoading" class="text-center py-4 text-slate-500 dark:text-slate-400 hidden">
          <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-pink-500"></i>
          <p class="text-sm"><?= __('loading') ?>...</p>
        </div>
        <audio id="audioPlayer" controls class="w-full rounded-xl mt-1">
          <p><?= __('browser_no_audio_support') ?></p>
        </audio>
      </div>
    </div>
  </div>
</div>

<script>
const callsData      = <?php echo json_encode($calls ?? []); ?>;
const groupNamesById  = <?php echo json_encode($groupNamesById ?? []); ?>;
const groupNamesByApi = <?php echo json_encode($groupNamesByApi ?? []); ?>;
const isSuper         = <?php echo $isSuper      ? 'true' : 'false'; ?>;
const isGroupAdmin    = <?php echo $isGroupAdmin  ? 'true' : 'false'; ?>;
const isGroupMember   = <?php echo $isGroupMember ? 'true' : 'false'; ?>;

// ── Helpers ─────────────────────────────────────────────────────────────────
function formatDateTime(dt) {
  if (!dt) return '—';
  const d = new Date(dt.replace(' ', 'T'));
  return d.toLocaleDateString('tr-TR') + ' ' + d.toLocaleTimeString('tr-TR');
}
function formatDuration(sec) {
  sec = parseInt(sec) || 0;
  const h = Math.floor(sec / 3600);
  const m = Math.floor((sec % 3600) / 60);
  const s = sec % 60;
  if (h > 0) return `${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  return `${m}:${String(s).padStart(2,'0')}`;
}
function dispLabelJs(d) {
  const map = { 'ANSWERED':'Cevaplandı','NO ANSWER':'Cevapsız','NO_ANSWER':'Cevapsız','BUSY':'Meşgul','FAILED':'Başarısız' };
  return map[(d||'').toUpperCase()] || d;
}
function getDispositionStyle(d) {
  const disp = (d || '').toUpperCase();
  if (disp === 'ANSWERED') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
  if (disp === 'BUSY')     return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
  if (disp === 'FAILED')   return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
  return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
}

// ── Call Detail Modal ────────────────────────────────────────────────────────
function showCallDetails(index) {
  const call = callsData[index];
  if (!call) return;

  const gid   = parseInt(call.group_id);
  const gName = groupNamesById[gid] || groupNamesByApi[gid] || ('#' + gid);
  const dispClass = getDispositionStyle(call.disposition);

  const rows = [
    ['<?= __('date') ?>',        formatDateTime(call.start), 'fa-calendar-days text-indigo-500'],
    ['<?= __('src') ?>',         `<span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">${call.src}</span>`, 'fa-phone text-emerald-500'],
    ['<?= __('dst') ?>',         `<span class="font-mono font-semibold text-purple-600 dark:text-purple-400">${call.dst}</span>`, 'fa-phone-flip text-purple-500'],
    ['<?= __('disposition') ?>', `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ${dispClass}"><i class="fa-solid fa-circle text-xs"></i>${dispLabelJs(call.disposition)}</span>`, 'fa-circle-half-stroke text-orange-500'],
    ['<?= __('duration') ?>',    formatDuration(call.duration), 'fa-clock text-slate-500'],
    ...((!isGroupMember) ? [['<?= __('billsec') ?>', formatDuration(call.billsec), 'fa-stopwatch text-red-400']] : []),
    ...(isSuper ? [['<?= __('group') ?>', `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300"><i class="fa-solid fa-users text-xs"></i>${gName}</span>`, 'fa-users text-blue-500']] : []),
    ...((isSuper || isGroupAdmin) ? [['<?= __('charged_amount') ?>', `<span class="font-mono font-semibold text-cyan-600 dark:text-cyan-400">$${parseFloat(call.amount_charged||0).toFixed(6)}</span>`, 'fa-coins text-cyan-500']] : []),
    ...(isSuper ? [
      ['<?= __('cost_api') ?>',       `<span class="font-mono font-semibold text-green-600 dark:text-green-400">$${parseFloat(call.cost_api||0).toFixed(6)}</span>`, 'fa-dollar-sign text-green-500'],
      ['<?= __('margin_percent') ?>', `<span class="font-semibold text-yellow-600 dark:text-yellow-400">${parseFloat(call.margin_percent||0).toFixed(2)}%</span>`, 'fa-percent text-yellow-500'],
    ] : []),
    ...(call.call_id ? [['<?= __('call_id') ?>', `<span class="font-mono text-xs text-slate-400 dark:text-slate-500 break-all">${call.call_id}</span>`, 'fa-fingerprint text-slate-400']] : []),
  ];

  const tableRows = rows.map(([label, value, icon]) => `
    <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/20 transition-colors">
      <td class="py-3 pr-6 whitespace-nowrap w-36">
        <span class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
          <i class="fa-solid ${icon} w-4 text-center shrink-0"></i>${label}
        </span>
      </td>
      <td class="py-3 text-sm text-slate-800 dark:text-slate-100 font-medium">${value}</td>
    </tr>`).join('');

  document.getElementById('modalContent').innerHTML = `
    <div class="overflow-hidden rounded-xl border border-slate-100 dark:border-slate-700">
      <table class="w-full">${tableRows}</table>
    </div>
    ${call.call_id && call.disposition.toUpperCase() === 'ANSWERED' ? `
    <div class="mt-4 flex justify-center">
      <button onclick="playAudio('${call.call_id}');closeModal();"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white font-semibold transition-all shadow-lg shadow-pink-500/20 hover:-translate-y-0.5">
        <i class="fa-solid fa-play"></i><?= __('listen_call') ?>
      </button>
    </div>` : ''}
  `;

  const modal = document.getElementById('callModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeModal() {
  const modal = document.getElementById('callModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

// ── Audio Modal ──────────────────────────────────────────────────────────────
function playAudio(callId) {
  const url    = `<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=${callId}`;
  const player = document.getElementById('audioPlayer');
  player.src   = url;
  player.load();
  document.getElementById('audioModal').classList.remove('hidden');
  document.getElementById('audioModal').classList.add('flex');
}

function closeAudioModal() {
  const modal  = document.getElementById('audioModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  const player = document.getElementById('audioPlayer');
  player.pause();
  player.src = '';
}

// ── Quick Date Presets ───────────────────────────────────────────────────────
function setQuickDate(range) {
  const form   = document.getElementById('filterForm');
  const fromEl = form.querySelector('[name="from"]');
  const toEl   = form.querySelector('[name="to"]');
  const now    = new Date();
  const pad    = n => String(n).padStart(2, '0');
  const fmt    = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  const sod    = d => { const x = new Date(d); x.setHours(0,0,0,0); return x; };
  const eod    = d => { const x = new Date(d); x.setHours(23,59,59,0); return x; };

  if (range === 'today') {
    fromEl.value = fmt(sod(now)); toEl.value = fmt(eod(now));
  } else if (range === 'yesterday') {
    const y = new Date(now); y.setDate(y.getDate() - 1);
    fromEl.value = fmt(sod(y)); toEl.value = fmt(eod(y));
  } else if (range === 'last7') {
    const d7 = new Date(now); d7.setDate(d7.getDate() - 6);
    fromEl.value = fmt(sod(d7)); toEl.value = fmt(eod(now));
  } else if (range === 'week') {
    const w = new Date(now);
    w.setDate(w.getDate() - w.getDay() + (w.getDay() === 0 ? -6 : 1));
    fromEl.value = fmt(sod(w)); toEl.value = fmt(eod(now));
  } else if (range === 'month') {
    const m = new Date(now.getFullYear(), now.getMonth(), 1);
    fromEl.value = fmt(sod(m)); toEl.value = fmt(eod(now));
  }
}

// ── CSV Export ───────────────────────────────────────────────────────────────
function exportToCSV() {
  const calls = callsData;
  if (!calls.length) { alert('<?= __('no_export_data') ?>'); return; }

  let headers = ['Tarih','Src'];
  if (isSuper) headers.push('Grup');
  headers.push('Dst','Durum','Süre');
  if (!isGroupMember) headers.push('Billsec');
  if (isSuper || isGroupAdmin) headers.push('Tahsil($)');
  if (isSuper) headers.push('CostAPI($)', 'Margin%');

  const escape = v => `"${String(v || '').replace(/"/g, '""')}"`;

  const rows = calls.map(c => {
    const gid   = parseInt(c.group_id);
    const gName = groupNamesById[gid] || groupNamesByApi[gid] || ('#' + gid);
    const cols  = [c.start, c.src];
    if (isSuper) cols.push(gName);
    cols.push(c.dst, c.disposition, formatDuration(c.duration));
    if (!isGroupMember) cols.push(formatDuration(c.billsec));
    if (isSuper || isGroupAdmin) cols.push(parseFloat(c.amount_charged || 0).toFixed(6));
    if (isSuper) {
      cols.push(parseFloat(c.cost_api || 0).toFixed(6));
      cols.push(parseFloat(c.margin_percent || 0).toFixed(2));
    }
    return cols.map(escape).join(',');
  });

  const csv  = '\uFEFF' + [headers.map(escape).join(','), ...rows].join('\r\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = `cdr_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
}

// ── Keyboard shortcuts ───────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeModal(); closeAudioModal(); }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>