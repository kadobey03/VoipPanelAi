<?php $title=__('reports').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
<?php $isGroupMember = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='groupmember'; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl">
    <div class="animate-spin rounded-full h-12 w-12 border-4 border-emerald-500 border-t-transparent"></div>
    <div class="text-lg font-medium text-slate-700 dark:text-slate-300"><?= __('report_preparing') ?></div>
  </div>
</div>

<div class="animate-in slide-in-from-left-5 duration-500">
  <!-- Header Section -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-6 gap-4">
    <div class="flex items-center gap-3">
      <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg">
        <i class="fa-solid fa-chart-line text-white text-2xl"></i>
      </div>
      <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
          <?= __('reports_and_analysis') ?>
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-sm"><?= __('detailed_call_statistics') ?></p>
      </div>
    </div>

    <!-- Export Buttons -->
    <div class="flex gap-2 flex-wrap">
      <button onclick="exportReport('pdf')" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
        <i class="fa-solid fa-file-pdf"></i>
        <span class="hidden sm:inline">PDF</span>
      </button>
      <button onclick="exportReport('excel')" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
        <i class="fa-solid fa-file-excel"></i>
        <span class="hidden sm:inline">Excel</span>
      </button>
      <button onclick="exportReport('csv')" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
        <i class="fa-solid fa-file-csv"></i>
        <span class="hidden sm:inline">CSV</span>
      </button>
    </div>
  </div>

  <!-- Advanced Filters -->
  <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 mb-6">
    <div class="flex items-center gap-2 mb-4">
      <i class="fa-solid fa-filter text-indigo-600"></i>
      <h3 class="text-lg font-semibold text-slate-800 dark:text-white"><?= __('filters') ?></h3>
    </div>

    <form method="get" class="space-y-4">
      <!-- Predefined Date Ranges -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button type="button" onclick="setDateRange('today')" class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors">
          <?= __('today') ?>
        </button>
        <button type="button" onclick="setDateRange('yesterday')" class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors">
          <?= __('yesterday') ?>
        </button>
        <button type="button" onclick="setDateRange('week')" class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors">
          <?= __('this_week') ?>
        </button>
        <button type="button" onclick="setDateRange('month')" class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors">
          <?= __('this_month') ?>
        </button>
        <button type="button" onclick="setDateRange('lastmonth')" class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-sm hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors">
          <?= __('last_month') ?>
        </button>
      </div>

      <!-- Custom Date Inputs -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            <i class="fa-solid fa-calendar-start text-emerald-600 mr-1"></i><?= __('start_date') ?>
          </label>
          <input type="datetime-local" name="from" id="from-date"
                 class="w-full px-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                 value="<?= htmlspecialchars(str_replace(' ', 'T', substr($from,0,16))) ?>">
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            <i class="fa-solid fa-calendar-end text-rose-600 mr-1"></i><?= __('end_date') ?>
          </label>
          <input type="datetime-local" name="to" id="to-date"
                 class="w-full px-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all duration-200"
                 value="<?= htmlspecialchars(str_replace(' ', 'T', substr($to,0,16))) ?>">
        </div>

        <?php if ($isSuper): ?>
        <div class="space-y-2">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            <i class="fa-solid fa-users text-purple-600 mr-1"></i><?= __('group_selection') ?>
          </label>
          <input type="number" name="group_id"
                 class="w-full px-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                 value="<?= isset($_GET['group_id'])? (int)$_GET['group_id'] : '' ?>" placeholder="<?= __('group_id_placeholder') ?>">
        </div>
        <?php endif; ?>
      </div>

      <!-- Apply Filters Button -->
      <div class="flex justify-end pt-4">
        <button type="submit"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 font-medium">
          <i class="fa-solid fa-magnifying-glass"></i>
          <?= __('apply_filters') ?>
        </button>
      </div>
    </form>
  </div>
</div>

  <!-- Statistics Cards -->
  <?php
    $totCalls=0;$totCost=0.0;$totRev=0.0;$totProfit=0.0;$answered=0;$noans=0;
    foreach(($summary??[]) as $row){ $totCalls+=(int)$row['calls']; $totCost+=(float)$row['cost_api']; $totRev+=(float)$row['revenue']; $totProfit+=(float)$row['profit']; }
    foreach(($dispRows??[]) as $d){ $n=(int)$d['n']; $disp=strtoupper($d['d']); if(in_array($disp,['ANSWERED','ANSWER'])) $answered+=$n; elseif($disp==='NO ANSWER') $noans+=$n; }
    if(!$isSuper){ $totCalls=$callsCount??0; $answered=$answerCount??0; $noans=$noAnswerCount??0; }
    $answerRate = $totCalls > 0 ? round(($answered / $totCalls) * 100, 1) : 0;
  ?>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Calls -->
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-100">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-phone text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= (int)$totCalls ?>" id="total-calls">0</div>
          <div class="text-blue-100 text-sm"><?= __('total_calls') ?></div>
        </div>
      </div>
      <div class="w-full bg-white/20 rounded-full h-2">
        <div class="bg-white h-2 rounded-full transition-all duration-1000 ease-out" style="width: 100%"></div>
      </div>
    </div>

    <!-- Answered Calls -->
    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-200">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-circle-check text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= (int)$answered ?>" id="answered-calls">0</div>
          <div class="text-emerald-100 text-sm"><?= __('answered_calls') ?></div>
        </div>
      </div>
      <div class="w-full bg-white/20 rounded-full h-2">
        <div class="bg-white h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?= $totCalls > 0 ? ($answered / $totCalls) * 100 : 0 ?>%"></div>
      </div>
    </div>

    <!-- No Answer -->
    <div class="bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-300">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-circle-xmark text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= (int)$noans ?>" id="noanswer-calls">0</div>
          <div class="text-rose-100 text-sm"><?= __('no_answer_calls') ?></div>
        </div>
      </div>
      <div class="w-full bg-white/20 rounded-full h-2">
        <div class="bg-white h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?= $totCalls > 0 ? ($noans / $totCalls) * 100 : 0 ?>%"></div>
      </div>
    </div>

    <!-- Cost/Answer Rate -->
    <?php if ($isSuper): ?>
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-400">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-coins text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= number_format((float)$totCost,2) ?>" id="total-cost">0.00</div>
          <div class="text-amber-100 text-sm"><?= __('total_cost') ?></div>
        </div>
      </div>
      <div class="text-xs text-amber-100 mt-2">
        <i class="fa-solid fa-chart-line mr-1"></i><?= __('answer_rate') ?>: <span class="font-semibold"><?= $answerRate ?>%</span>
      </div>
    </div>
    <?php elseif (!$isGroupMember): ?>
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-400">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-wallet text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= number_format((float)($spent??0),2) ?>" id="total-spent">0.00</div>
          <div class="text-amber-100 text-sm"><?= __('spent_amount') ?></div>
        </div>
      </div>
      <div class="text-xs text-amber-100 mt-2">
        <i class="fa-solid fa-chart-line mr-1"></i><?= __('answer_rate') ?>: <span class="font-semibold"><?= $answerRate ?>%</span>
      </div>
    </div>
    <?php else: ?>
    <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-400">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-percent text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= $answerRate ?>" id="answer-rate">0</div>
          <div class="text-purple-100 text-sm"><?= __('answer_rate_percent') ?></div>
        </div>
      </div>
      <div class="w-full bg-white/20 rounded-full h-2 mt-2">
        <div class="bg-white h-2 rounded-full transition-all duration-1000 ease-out" style="width: <?= $answerRate ?>%"></div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($isSuper): ?>
  <!-- Super Admin Additional Stats -->
  <div class="grid md:grid-cols-3 gap-6 mb-8">
    <!-- Revenue Card -->
    <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-500">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-sack-dollar text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= number_format((float)$totRev,2) ?>" id="total-revenue">0.00</div>
          <div class="text-emerald-100 text-sm"><?= __('total_revenue') ?></div>
        </div>
      </div>
      <div class="flex items-center text-sm text-emerald-100">
        <i class="fa-solid fa-arrow-up mr-1"></i>
        <span><?= __('monthly_increase') ?></span>
      </div>
    </div>

    <!-- Profit Card -->
    <div class="bg-gradient-to-br from-fuchsia-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-600">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-arrow-trend-up text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= number_format((float)$totProfit,2) ?>" id="total-profit">0.00</div>
          <div class="text-fuchsia-100 text-sm"><?= __('net_profit') ?></div>
        </div>
      </div>
      <div class="flex items-center text-sm text-fuchsia-100">
        <i class="fa-solid fa-chart-line mr-1"></i>
        <span><?= __('profitability_rate') ?> <span class="font-semibold">
          <?= $totRev > 0 ? round(($totProfit / $totRev) * 100, 1) : 0 ?>%
        </span></span>
      </div>
    </div>

    <!-- Groups Overview Card -->
    <div class="bg-gradient-to-br from-slate-600 to-slate-700 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-700">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-users text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= count($summary ?? []) ?>" id="total-groups">0</div>
          <div class="text-slate-100 text-sm"><?= __('active_groups') ?></div>
        </div>
      </div>
      <div class="space-y-2">
        <?php foreach (array_slice(($summary??[]), 0, 3) as $row): $gid=(int)$row['group_id']; ?>
          <div class="flex items-center justify-between bg-white/10 rounded-lg p-2">
            <span class="text-sm truncate"><?= htmlspecialchars($groups[$gid] ?? ('#'.$gid)) ?></span>
            <span class="font-semibold text-sm text-emerald-300">$<?= number_format((float)$row['profit'],0) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php elseif (!$isGroupMember): ?>
  <!-- Group Admin/User Additional Stats -->
  <div class="grid md:grid-cols-2 gap-6 mb-8">
    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-500">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-piggy-bank text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-3xl font-bold" data-count="<?= number_format((float)($balance??0),2) ?>" id="remaining-balance">0.00</div>
          <div class="text-teal-100 text-sm"><?= __('remaining_balance') ?></div>
        </div>
      </div>
      <div class="flex items-center text-sm text-teal-100">
        <i class="fa-solid fa-wallet mr-1"></i>
        <span><?= __('this_month_spent') ?> <span class="font-semibold text-amber-300">
          $<?= number_format((float)($spent??0),2) ?>
        </span></span>
      </div>
    </div>

    <!-- Summary Overview Card -->
    <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 cursor-pointer group animate-in slide-in-from-bottom-4 duration-500 delay-600">
      <div class="flex items-center justify-between mb-4">
        <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-200">
          <i class="fa-solid fa-chart-pie text-2xl"></i>
        </div>
        <div class="text-right">
          <div class="text-2xl font-bold" data-count="<?= $answerRate ?>" id="summary-rate">0</div>
          <div class="text-indigo-100 text-sm"><?= __('success_rate') ?></div>
        </div>
      </div>
      <div class="grid grid-cols-3 gap-4 mt-4">
        <div class="text-center">
          <div class="text-lg font-bold text-emerald-300" data-count="<?= (int)$answered ?>" id="summary-answered">0</div>
          <div class="text-xs text-indigo-100"><?= __('answered_calls') ?></div>
        </div>
        <div class="text-center">
          <div class="text-lg font-bold text-rose-300" data-count="<?= (int)$noans ?>" id="summary-noans">0</div>
          <div class="text-xs text-indigo-100"><?= __('no_answer_calls') ?></div>
        </div>
        <div class="text-center">
          <div class="text-lg font-bold text-blue-300" data-count="<?= (int)$totCalls ?>" id="summary-total">0</div>
          <div class="text-xs text-indigo-100"><?= __('total') ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Charts Section -->
  <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 mb-8 animate-in slide-in-from-bottom-4 duration-500 delay-800">
    <div class="flex items-center gap-3 mb-6">
      <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl">
        <i class="fa-solid fa-chart-line text-white text-lg"></i>
      </div>
      <div>
        <h3 class="text-xl font-bold text-slate-800 dark:text-white"><?= __('daily_trend_analysis') ?></h3>
        <p class="text-slate-500 dark:text-slate-400 text-sm"><?= __('cost_and_revenue_trends') ?></p>
      </div>
    </div>
    <div class="relative">
      <canvas id="trend" height="140"></canvas>
      <div class="absolute top-4 right-4 flex gap-2">
        <button onclick="toggleChartData('cost')" class="px-3 py-1 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-lg text-xs hover:bg-red-200 dark:hover:bg-red-900/60 transition-colors">
          <?= __('cost') ?>
        </button>
        <button onclick="toggleChartData('revenue')" class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs hover:bg-emerald-200 dark:hover:bg-emerald-900/60 transition-colors">
          <?= __('revenue') ?>
        </button>
      </div>
    </div>
  </div>

  <!-- Agent Performance Table -->
  <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 mb-8 animate-in slide-in-from-bottom-4 duration-500 delay-900">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl">
          <i class="fa-solid fa-users text-white text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800 dark:text-white"><?= __('agent_performance_summary') ?></h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm"><?= __('detailed_agent_statistics') ?></p>
        </div>
      </div>
      <div class="flex gap-2">
        <button onclick="toggleTableView()" class="px-4 py-2 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-xl hover:bg-indigo-200 dark:hover:bg-indigo-900/60 transition-colors flex items-center gap-2">
          <i class="fa-solid fa-table"></i>
          <span class="hidden sm:inline"><?= __('view_button') ?></span>
        </button>
      </div>
    </div>

    <?php if ($isSuper): ?>
      <?php foreach (($agentsByGroup ?? []) as $groupName => $agents): ?>
        <div class="mb-6">
          <div class="flex items-center gap-2 mb-4 p-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700/50 dark:to-slate-600/50 rounded-xl">
            <i class="fa-solid fa-user-group text-slate-600 dark:text-slate-300"></i>
            <h4 class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($groupName) ?></h4>
            <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs">
              <?= count($agents) ?> <?= __('agent') ?>
            </span>
          </div>
          <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-600">
            <table class="min-w-full">
              <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600">
                <tr>
                  <th class="p-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('agent') ?></th>
                  <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-phone mr-1"></i><?= __('calls_count') ?>
                  </th>
                  <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i><?= __('answer_count') ?>
                  </th>
                  <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-clock mr-1 text-blue-600"></i><?= __('billsec_duration') ?>
                  </th>
                  <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-coins mr-1 text-amber-600"></i><?= __('cost_amount') ?>
                  </th>
                  <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('success_percent') ?></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
                <?php foreach ($agents as $r):
                  $successRate = ((int)($r['calls'] ?? 0)) > 0 ? round((((int)($r['answer'] ?? 0)) / ((int)($r['calls'] ?? 0))) * 100, 1) : 0;
                ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">
                          <?= strtoupper(substr(htmlspecialchars($r['user_login'] ?? ''), 0, 1)) ?>
                        </span>
                      </div>
                      <div>
                        <div class="font-medium text-slate-900 dark:text-white">
                          <?= htmlspecialchars($r['user_login'] ?? '') ?>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                          <?= htmlspecialchars($r['voip_exten'] ?? '') ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="p-4 text-center font-semibold text-slate-900 dark:text-white">
                    <?= (int)($r['calls'] ?? 0) ?>
                  </td>
                  <td class="p-4 text-center">
                    <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-semibold">
                      <?= (int)($r['answer'] ?? 0) ?>
                    </span>
                  </td>
                  <td class="p-4 text-center font-semibold text-slate-900 dark:text-white">
                    <?= number_format((int)($r['billsec'] ?? 0)) ?>s
                  </td>
                  <td class="p-4 text-center font-semibold text-amber-700 dark:text-amber-300">
                    $<?= number_format((float)($r['cost'] ?? 0),2) ?>
                  </td>
                  <td class="p-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <div class="w-12 bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full transition-all duration-1000"
                             style="width: <?= $successRate ?>%"></div>
                      </div>
                      <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        <?= $successRate ?>%
                      </span>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-600">
        <table class="min-w-full">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600">
            <tr>
              <th class="p-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('agent') ?></th>
              <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                <i class="fa-solid fa-phone mr-1"></i><?= __('calls_count') ?>
              </th>
              <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                <i class="fa-solid fa-circle-check mr-1 text-emerald-600"></i><?= __('answer_count') ?>
              </th>
              <?php if (!$isGroupMember): ?>
              <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300">
                <i class="fa-solid fa-clock mr-1 text-blue-600"></i><?= __('billsec_duration') ?>
              </th>
              <?php endif; ?>
              <th class="p-4 text-center text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('success_percent') ?></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
            <?php foreach (($agentsByGroup[key($agentsByGroup ?? [])] ?? []) as $r):
              $successRate = ((int)($r['calls'] ?? 0)) > 0 ? round((((int)($r['answer'] ?? 0)) / ((int)($r['calls'] ?? 0))) * 100, 1) : 0;
            ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">
                      <?= strtoupper(substr(htmlspecialchars($r['user_login'] ?? ''), 0, 1)) ?>
                    </span>
                  </div>
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white">
                      <?= htmlspecialchars($r['user_login'] ?? '') ?>
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      <?= htmlspecialchars($r['voip_exten'] ?? '') ?>
                    </div>
                  </div>
                </div>
              </td>
              <td class="p-4 text-center font-semibold text-slate-900 dark:text-white">
                <?= (int)($r['calls'] ?? 0) ?>
              </td>
              <td class="p-4 text-center">
                <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-sm font-semibold">
                  <?= (int)($r['answer'] ?? 0) ?>
                </span>
              </td>
              <?php if (!$isGroupMember): ?>
              <td class="p-4 text-center font-semibold text-slate-900 dark:text-white">
                <?= number_format((int)($r['billsec'] ?? 0)) ?>s
              </td>
              <?php endif; ?>
              <td class="p-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <div class="w-12 bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full transition-all duration-1000"
                         style="width: <?= $successRate ?>%"></div>
                  </div>
                  <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <?= $successRate ?>%
                  </span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Additional Charts -->
  <div class="grid lg:grid-cols-2 gap-6 mb-8">
    <!-- Top Agents Chart -->
    <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 animate-in slide-in-from-left-5 duration-500 delay-1000">
      <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl">
          <i class="fa-solid fa-trophy text-white text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800 dark:text-white">
            <?php if (!$isGroupMember): ?><?= __('top_agents_billsec') ?><?php else: ?><?= __('top_agents_calls') ?><?php endif; ?>
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm"><?= __('top_performers') ?></p>
        </div>
      </div>
      <div class="relative">
        <canvas id="topAgents" height="180"></canvas>
        <div class="absolute top-2 right-2 flex gap-1">
          <button onclick="changeTopAgentsMetric('calls')" class="px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded text-xs hover:bg-blue-200 dark:hover:bg-blue-900/60 transition-colors">
            <?= __('calls_count') ?>
          </button>
          <?php if (!$isGroupMember): ?>
          <button onclick="changeTopAgentsMetric('billsec')" class="px-2 py-1 bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded text-xs hover:bg-purple-200 dark:hover:bg-purple-900/60 transition-colors">
            <?= __('duration_time') ?>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Disposition Distribution Chart -->
    <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 animate-in slide-in-from-right-5 duration-500 delay-1100">
      <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl">
          <i class="fa-solid fa-chart-pie text-white text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800 dark:text-white"><?= __('call_status_distribution') ?></h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm"><?= __('disposition_analysis') ?></p>
        </div>
      </div>
      <div class="relative">
        <canvas id="dispChart" height="180"></canvas>
        <div class="absolute top-2 right-2">
          <button onclick="toggleDispChart()" class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-xs hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
            <i class="fa-solid fa-rotate"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // ===== UTILITY FUNCTIONS =====
    function animateNumber(element, targetValue, duration = 1000) {
      const startValue = 0;
      const startTime = performance.now();

      function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        const currentValue = startValue + (targetValue - startValue) * progress;
        element.textContent = typeof targetValue === 'number' && targetValue % 1 === 0
          ? Math.floor(currentValue).toLocaleString('tr-TR')
          : currentValue.toFixed(2).replace('.', ',');

        if (progress < 1) {
          requestAnimationFrame(update);
        }
      }

      requestAnimationFrame(update);
    }

    function showLoading() {
      document.getElementById('loading-overlay').classList.remove('hidden');
    }

    function hideLoading() {
      document.getElementById('loading-overlay').classList.add('hidden');
    }

    // ===== DATE RANGE FUNCTIONS =====
    function setDateRange(range) {
      const now = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      let fromDate, toDate;

      switch(range) {
        case 'today':
          fromDate = toDate = today;
          break;
        case 'yesterday':
          fromDate = toDate = new Date(today);
          fromDate.setDate(fromDate.getDate() - 1);
          toDate.setDate(toDate.getDate() - 1);
          break;
        case 'week':
          fromDate = new Date(today);
          fromDate.setDate(fromDate.getDate() - today.getDay());
          toDate = new Date(today);
          break;
        case 'month':
          fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
          toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
          break;
        case 'lastmonth':
          fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
          toDate = new Date(today.getFullYear(), today.getMonth(), 0);
          break;
      }

      document.getElementById('from-date').value = fromDate.toISOString().slice(0, 16);
      document.getElementById('to-date').value = toDate.toISOString().slice(0, 16);

      // Add visual feedback
      const buttons = document.querySelectorAll('[onclick*="setDateRange"]');
      buttons.forEach(btn => btn.classList.remove('ring-2', 'ring-indigo-500'));
      event.target.classList.add('ring-2', 'ring-indigo-500');
    }

    // ===== EXPORT FUNCTIONS =====
    function exportReport(type) {
      showLoading();

      const formData = new FormData();
      formData.append('export_type', type);
      formData.append('from', document.getElementById('from-date').value);
      formData.append('to', document.getElementById('to-date').value);
      formData.append('group_id', document.querySelector('input[name="group_id"]')?.value || '');

      fetch('/reports/export', {
        method: 'POST',
        body: formData
      })
      .then(response => response.blob())
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `rapor_${new Date().toISOString().split('T')[0]}.${type}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      })
      .catch(error => {
        console.error('Export error:', error);
        alert('<?= __('export_error') ?>');
      })
      .finally(() => {
        hideLoading();
      });
    }

    // ===== CHART MANAGEMENT =====
    let trendChart, topAgentsChart, dispChart;

    // Initialize Charts
    function initCharts() {
      initTrendChart();
      initTopAgentsChart();
      initDispChart();
    }

    function initTrendChart() {
      const labels = <?= json_encode(array_map(function($t){return $t['d'];}, $trend ?? []), JSON_UNESCAPED_UNICODE) ?>;
      const cost = <?= json_encode(array_map(function($t){return (float)$t['cost'];}, $trend ?? [])) ?>;
      const revenue = <?= json_encode(array_map(function($t){return (float)$t['revenue'];}, $trend ?? [])) ?>;

      const ctx = document.getElementById('trend').getContext('2d');
      trendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: '<?= __('cost') ?>',
              data: cost,
              borderColor: 'rgba(239,68,68,1)',
              backgroundColor: 'rgba(239,68,68,0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: 'rgba(239,68,68,1)',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8
            },
            {
              label: '<?= __('revenue') ?>',
              data: revenue,
              borderColor: 'rgba(16,185,129,1)',
              backgroundColor: 'rgba(16,185,129,0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: 'rgba(16,185,129,1)',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 20
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0,0,0,0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              cornerRadius: 8,
              displayColors: true
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0,0,0,0.05)'
              },
              ticks: {
                callback: function(value) {
                  return '$' + value.toFixed(2);
                }
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          },
          animation: {
            duration: 1000,
            easing: 'easeOutQuart'
          }
        }
      });
    }

    function initTopAgentsChart() {
      const agentStats = <?= json_encode($allAgents ?? [], JSON_UNESCAPED_UNICODE) ?>;

      <?php if ($isGroupMember): ?>
      const topAgents = (agentStats||[]).slice().sort((a,b)=>(+b.calls)-(+a.calls)).slice(0,10);
      const aLabels = topAgents.map(a=> (a.user_login||a.voip_exten||'agent'));
      const aData = topAgents.map(a=> +a.calls||0);
      const metric = 'calls';
      <?php else: ?>
      const topAgents = (agentStats||[]).slice().sort((a,b)=>(+b.billsec)-(+a.billsec)).slice(0,10);
      const aLabels = topAgents.map(a=> (a.user_login||a.voip_exten||'agent'));
      const aData = topAgents.map(a=> +a.billsec||0);
      const metric = 'billsec';
      <?php endif; ?>

      const ctx = document.getElementById('topAgents').getContext('2d');
      topAgentsChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: aLabels,
          datasets: [{
            label: metric === 'calls' ? '<?= __('calls_count') ?>' : '<?= __('billsec_duration') ?>',
            data: aData,
            backgroundColor: [
              'rgba(59,130,246,0.8)',
              'rgba(16,185,129,0.8)',
              'rgba(245,158,11,0.8)',
              'rgba(239,68,68,0.8)',
              'rgba(139,92,246,0.8)',
              'rgba(236,72,153,0.8)',
              'rgba(6,182,212,0.8)',
              'rgba(34,197,94,0.8)',
              'rgba(251,146,60,0.8)',
              'rgba(168,85,247,0.8)'
            ],
            borderRadius: 8,
            borderWidth: 0,
            hoverBackgroundColor: [
              'rgba(59,130,246,1)',
              'rgba(16,185,129,1)',
              'rgba(245,158,11,1)',
              'rgba(239,68,68,1)',
              'rgba(139,92,246,1)',
              'rgba(236,72,153,1)',
              'rgba(6,182,212,1)',
              'rgba(34,197,94,1)',
              'rgba(251,146,60,1)',
              'rgba(168,85,247,1)'
            ]
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(0,0,0,0.8)',
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  return context.parsed.y + (metric === 'calls' ? ' <?= __('calls_text') ?>' : ' <?= __('seconds_text') ?>');
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0,0,0,0.05)'
              }
            },
            x: {
              grid: {
                display: false
              },
              ticks: {
                maxRotation: 45,
                minRotation: 45
              }
            }
          },
          animation: {
            duration: 1000,
            easing: 'easeOutQuart',
            delay: function(context) {
              return context.dataIndex * 100;
            }
          }
        }
      });
    }

    function initDispChart() {
      const disp = <?= json_encode($dispRows ?? [], JSON_UNESCAPED_UNICODE) ?>;
      const dLabels = (disp||[]).map(x=>x.d);
      const dData = (disp||[]).map(x=>+x.n||0);

      const ctx = document.getElementById('dispChart').getContext('2d');
      dispChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: dLabels,
          datasets: [{
            data: dData,
            backgroundColor: [
              '#10b981',
              '#ef4444',
              '#f59e0b',
              '#3b82f6',
              '#8b5cf6',
              '#ec4899',
              '#06b6d4',
              '#84cc16'
            ],
            borderWidth: 0,
            hoverBorderWidth: 2,
            hoverBorderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                padding: 20,
                usePointStyle: true
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0,0,0,0.8)',
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((context.parsed / total) * 100).toFixed(1);
                  return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                }
              }
            }
          },
          animation: {
            animateScale: true,
            duration: 1000,
            easing: 'easeOutQuart'
          }
        }
      });
    }

    // Chart control functions
    function toggleChartData(type) {
      if (!trendChart) return;

      const datasetIndex = type === 'cost' ? 0 : 1;
      const meta = trendChart.getDatasetMeta(datasetIndex);
      meta.hidden = meta.hidden === null ? !trendChart.data.datasets[datasetIndex].hidden : null;

      trendChart.update();
    }

    function changeTopAgentsMetric(metric) {
      if (!topAgentsChart) return;

      const agentStats = <?= json_encode($allAgents ?? [], JSON_UNESCAPED_UNICODE) ?>;
      const sorted = agentStats.slice().sort((a,b)=>(+b[metric])-(+a[metric])).slice(0,10);
      const labels = sorted.map(a=> (a.user_login||a.voip_exten||'agent'));
      const data = sorted.map(a=> +a[metric]||0);

      topAgentsChart.data.labels = labels;
      topAgentsChart.data.datasets[0].data = data;
      topAgentsChart.data.datasets[0].label = metric === 'calls' ? '<?= __('calls_count') ?>' : '<?= __('billsec_duration') ?>';
      topAgentsChart.update();
    }

    function toggleDispChart() {
      if (!dispChart) return;

      // Rotate colors
      const colors = dispChart.data.datasets[0].backgroundColor;
      colors.unshift(colors.pop());
      dispChart.update();
    }

    function toggleTableView() {
      // Simple table view toggle - could be expanded
      alert('<?= __('table_view_changed') ?>');
    }

    // ===== INITIALIZATION =====
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize charts
      initCharts();

      // Animate numbers
      document.querySelectorAll('[data-count]').forEach(element => {
        const targetValue = parseFloat(element.getAttribute('data-count')) || 0;
        animateNumber(element, targetValue);
      });

      // Hide loading on page load
      hideLoading();

      // Add smooth scrolling for better UX
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
          });
        });
      });
    });

    // ===== FORM SUBMISSION HANDLING =====
    document.querySelector('form').addEventListener('submit', function() {
      showLoading();
    });

    // ===== RESPONSIVE HANDLING =====
    window.addEventListener('resize', function() {
      // Re-initialize charts on resize for better responsiveness
      setTimeout(() => {
        if (trendChart) trendChart.resize();
        if (topAgentsChart) topAgentsChart.resize();
        if (dispChart) dispChart.resize();
      }, 250);
    });
  </script>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
