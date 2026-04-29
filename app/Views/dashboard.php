<?php
$title = __('dashboard_title') . ' - ' . __('papam_voip_panel');
require __DIR__ . '/partials/header.php';

$isSuper       = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'superadmin');
$isGroupAdmin  = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'groupadmin');
$isGroupMember = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '') === 'groupmember');
$user          = $_SESSION['user'] ?? [];
$currentHour   = (int)date('H');
$greeting      = $currentHour < 12 ? __('good_morning') : ($currentHour < 18 ? __('good_afternoon') : __('good_evening'));
$canSeeCost    = $isSuper || $isGroupAdmin;
$billsecFmt    = function(int $s): string { return sprintf('%dsa %02ddak', floor($s/3600), floor(($s%3600)/60)); };
?>

<!-- ══════════════════════════════════════════════════ HERO -->
<section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 mb-6 text-white">
  <div class="absolute inset-0 bg-black/10 pointer-events-none"></div>
  <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

  <div class="relative px-6 py-8 lg:px-10">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <!-- Left: greeting -->
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-2xl font-bold flex-shrink-0">
          <?= strtoupper(mb_substr($user['login'] ?? 'U', 0, 1)) ?>
        </div>
        <div>
          <p class="text-white/70 text-sm font-medium"><?= $greeting ?></p>
          <h1 class="text-2xl lg:text-3xl font-bold text-white"><?= htmlspecialchars($user['login'] ?? '') ?></h1>
          <div class="flex items-center gap-2 mt-1">
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold">
              <i class="fa-solid fa-shield text-xs"></i><?= ucfirst($user['role'] ?? '') ?>
            </span>
            <?php if (!$isSuper && !empty($ownGroupName)): ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white/15 rounded-full text-xs">
              <i class="fa-solid fa-users text-xs"></i><?= htmlspecialchars($ownGroupName) ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right: time + date -->
      <div class="flex items-center gap-3 text-sm">
        <div class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-xl px-4 py-2.5">
          <i class="fa-solid fa-calendar-days text-yellow-300"></i>
          <span class="font-medium"><?= date('d.m.Y') ?></span>
        </div>
        <div class="flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-xl px-4 py-2.5">
          <i class="fa-solid fa-clock text-green-300"></i>
          <span id="currentTime" class="font-mono font-medium"><?= date('H:i:s') ?></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════ LOW BALANCE WARNING -->
<?php if (!$isSuper && !$isGroupMember && isset($ownGroupBalance) && $ownGroupBalance !== null && $ownGroupBalance < 50): ?>
<div class="flex items-center gap-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-2xl p-4 mb-6">
  <div class="p-3 bg-red-100 dark:bg-red-900/40 rounded-xl flex-shrink-0">
    <i class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400 text-xl"></i>
  </div>
  <div class="flex-1 min-w-0">
    <p class="font-bold text-red-700 dark:text-red-400"><?= __('low_balance') ?>!</p>
    <p class="text-sm text-red-600 dark:text-red-500"><?= __('low_balance_warning') ?> — <?= __('current') ?>: <strong>$<?= number_format((float)$ownGroupBalance, 2) ?></strong></p>
  </div>
  <a href="<?= \App\Helpers\Url::to('/balance/topup') ?>"
     class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors text-sm">
    <i class="fa-solid fa-plus"></i><?= __('topup') ?>
  </a>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════ TOP STAT CARDS -->
<div class="grid grid-cols-2 <?= $isSuper ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-4 mb-6">

  <?php if ($isSuper): ?>
  <!-- API Balance -->
  <div class="relative overflow-hidden bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-wallet text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('main_balance_api') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1" id="balance">
        <?= $balanceValue !== null ? '$' . number_format($balanceValue, 2) : '—' ?>
      </div>
      <div class="text-xs opacity-70"><?= __('current_status') ?></div>
    </div>
  </div>

  <!-- Groups Total -->
  <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-layer-group text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('groups_total') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1">$<?= number_format($groupsTotal, 2) ?></div>
      <div class="text-xs opacity-70"><?= $totalGroups ?> <?= __('group') ?> · <?= $totalUsers ?> <?= __('user') ?></div>
    </div>
  </div>

  <!-- Diff -->
  <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-scale-balanced text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('balance_difference') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1"><?= $diff !== null ? '$'.number_format($diff, 2) : '—' ?></div>
      <div class="text-xs opacity-70"><?= __('difference_main_groups') ?></div>
    </div>
  </div>

  <!-- Weekly Profit -->
  <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-arrow-trend-up text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('weekly_profit') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1">$<?= number_format($weeklyProfit, 2) ?></div>
      <div class="text-xs opacity-70"><?= __('last_7_days') ?></div>
    </div>
  </div>

  <?php elseif (!$isGroupMember): ?>
  <!-- Group Balance -->
  <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-piggy-bank text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('group_balance') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1">$<?= number_format((float)($ownGroupBalance ?? 0), 2) ?></div>
      <div class="text-xs opacity-70"><?= __('current_balance') ?></div>
    </div>
  </div>

  <!-- Weekly Spending -->
  <div class="relative overflow-hidden bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-sack-dollar text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('this_week_spending') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1">$<?= number_format($weeklyRevenue, 2) ?></div>
      <div class="text-xs opacity-70"><?= __('last_7_days') ?></div>
    </div>
  </div>

  <!-- Active Agents -->
  <div class="relative overflow-hidden bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-violet-500/20">
    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full"></div>
    <div class="relative">
      <div class="flex items-center gap-2 mb-3 opacity-80">
        <i class="fa-solid fa-headset text-sm"></i>
        <span class="text-xs font-semibold uppercase tracking-wide"><?= __('agent_status') ?></span>
      </div>
      <div class="text-3xl font-bold mb-1"><?= $activeAgents ?><span class="text-lg opacity-60"> / <?= $totalAgents ?></span></div>
      <div class="text-xs opacity-70">Aktif / Toplam</div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════ TODAY STATS -->
<?php if ($todayTotal > 0): ?>
<div class="mb-6">
  <div class="flex items-center gap-2 mb-3">
    <i class="fa-solid fa-sun text-amber-500"></i>
    <h2 class="text-base font-bold text-slate-800 dark:text-white">Bugün — <span class="text-slate-500 dark:text-slate-400 font-normal text-sm"><?= date('d.m.Y') ?></span></h2>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 <?= $canSeeCost ? 'lg:grid-cols-5' : 'lg:grid-cols-4' ?> gap-3">
    <!-- Total calls -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
          <i class="fa-solid fa-phone text-indigo-600 dark:text-indigo-400 text-xs"></i>
        </div>
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Çağrı</span>
      </div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= number_format($todayTotal) ?></div>
      <div class="text-xs text-slate-400 mt-0.5">Toplam</div>
    </div>

    <!-- Answered -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-emerald-200 dark:border-emerald-700/40 p-4 shadow-sm">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
          <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
        </div>
        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">Cevap</span>
      </div>
      <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?= number_format($todayAnswered) ?></div>
      <div class="text-xs text-slate-400 mt-0.5"><?= $todayAnswerRate ?>% oran</div>
    </div>

    <!-- Missed -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-red-200 dark:border-red-700/40 p-4 shadow-sm">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
          <i class="fa-solid fa-phone-slash text-red-500 dark:text-red-400 text-xs"></i>
        </div>
        <span class="text-xs font-semibold text-red-500 dark:text-red-400 uppercase">Cevapsız</span>
      </div>
      <div class="text-2xl font-bold text-red-500 dark:text-red-400"><?= number_format($todayTotal - $todayAnswered) ?></div>
      <div class="text-xs text-slate-400 mt-0.5"><?= $todayTotal > 0 ? round(($todayTotal - $todayAnswered) / $todayTotal * 100, 1) : 0 ?>% oran</div>
    </div>

    <!-- Billsec -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-blue-200 dark:border-blue-700/40 p-4 shadow-sm">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
          <i class="fa-solid fa-stopwatch text-blue-600 dark:text-blue-400 text-xs"></i>
        </div>
        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Konuşma</span>
      </div>
      <div class="text-xl font-bold text-blue-600 dark:text-blue-400"><?= $billsecFmt($todayBillsec) ?></div>
      <div class="text-xs text-slate-400 mt-0.5">Toplam süre</div>
    </div>

    <?php if ($canSeeCost): ?>
    <!-- Charged -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-cyan-200 dark:border-cyan-700/40 p-4 shadow-sm">
      <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center">
          <i class="fa-solid fa-coins text-cyan-600 dark:text-cyan-400 text-xs"></i>
        </div>
        <span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 uppercase">Tahsil</span>
      </div>
      <div class="text-xl font-bold text-cyan-600 dark:text-cyan-400">$<?= number_format($todayCharged, 2) ?></div>
      <div class="text-xs text-slate-400 mt-0.5">Bugün</div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════ CHARTS + DONUT -->
<?php if (!$isGroupMember): ?>
<div class="grid lg:grid-cols-3 gap-5 mb-6">
  <!-- Trend Chart (2/3) -->
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm">
          <?= $isSuper ? 'Gelir / Maliyet Trendi' : 'Harcama Trendi' ?>
        </h3>
        <p class="text-xs text-slate-400 mt-0.5"><?= __('last_7_days_analysis') ?></p>
      </div>
      <div class="p-2.5 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl">
        <i class="fa-solid fa-chart-line text-white text-sm"></i>
      </div>
    </div>
    <div style="height:220px;position:relative">
      <canvas id="trendLine"></canvas>
    </div>
  </div>

  <!-- Disposition Donut (1/3) -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-bold text-slate-800 dark:text-white text-sm">Çağrı Dağılımı</h3>
        <p class="text-xs text-slate-400 mt-0.5">Bugün</p>
      </div>
      <div class="p-2.5 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl">
        <i class="fa-solid fa-chart-pie text-white text-sm"></i>
      </div>
    </div>
    <div style="height:180px;position:relative">
      <canvas id="dispositionDonut"></canvas>
    </div>
    <!-- Legend -->
    <div class="grid grid-cols-2 gap-1.5 mt-3">
      <?php
      $dispColors = ['ANSWERED'=>['bg-emerald-500','Cevap'], 'NO ANSWER'=>['bg-slate-400','Cevapsız'], 'BUSY'=>['bg-amber-500','Meşgul'], 'FAILED'=>['bg-red-500','Başarısız']];
      foreach ($dispColors as $d => [$color, $label]):
      ?>
      <div class="flex items-center gap-1.5">
        <div class="w-2.5 h-2.5 rounded-full <?= $color ?> flex-shrink-0"></div>
        <span class="text-xs text-slate-500 dark:text-slate-400 truncate"><?= $label ?>: <strong class="text-slate-700 dark:text-slate-200"><?= $dispositionData[$d] ?? 0 ?></strong></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Call Count Bar Chart (full width) -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-6">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="font-bold text-slate-800 dark:text-white text-sm"><?= __('daily_call_count') ?></h3>
      <p class="text-xs text-slate-400 mt-0.5"><?= __('call_count_last_7_days') ?></p>
    </div>
    <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl">
      <i class="fa-solid fa-phone text-white text-sm"></i>
    </div>
  </div>
  <div style="height:200px;position:relative">
    <canvas id="callsBar"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════ BOTTOM GRID: Quick Access + Recent Calls + System Status -->
<div class="grid lg:grid-cols-3 gap-5 mb-6">

  <!-- Quick Access (1/3) -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <h3 class="font-bold text-slate-800 dark:text-white text-sm mb-4 flex items-center gap-2">
      <i class="fa-solid fa-bolt text-yellow-500"></i><?= __('quick_access') ?>
    </h3>
    <div class="space-y-2">
      <?php
      $quickLinks = [
          ['/calls/history',    'fa-phone',       'bg-blue-500',    'Çağrı Geçmişi',   'CDR sorguları'],
          ['/reports',          'fa-chart-bar',   'bg-purple-500',  __('reports'),      __('detailed_analysis')],
      ];
      if (!$isGroupMember) {
          array_unshift($quickLinks, ['/agents', 'fa-headset', 'bg-rose-500', __('agent_status'), __('agent_management')]);
      }
      if (!$isGroupMember) {
          $quickLinks[] = ['/numbers', 'fa-address-book', 'bg-amber-500', __('external_numbers'), __('number_management')];
      }
      if (!$isGroupMember) {
          $quickLinks[] = ['/groups', 'fa-layer-group', 'bg-teal-500', __('groups'), __('view_and_manage_groups')];
      }
      if (!$isGroupMember) {
          $quickLinks[] = ['/users', 'fa-users', 'bg-indigo-500', __('users'), __('user_management')];
      }
      if ($isSuper) {
          $quickLinks[] = ['/balance', 'fa-wallet', 'bg-fuchsia-500', __('main_balance'), __('api_balance_management')];
      }
      $quickLinks[] = ['/profile', 'fa-user-gear', 'bg-slate-500', __('profile'), __('account_settings')];
      foreach ($quickLinks as [$href, $icon, $bg, $name, $sub]):
      ?>
      <a href="<?= \App\Helpers\Url::to($href) ?>"
         class="flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
        <div class="w-9 h-9 rounded-lg <?= $bg ?> flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 transition-transform">
          <i class="fa-solid <?= $icon ?> text-white text-sm"></i>
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-semibold text-slate-800 dark:text-white text-sm truncate"><?= $name ?></div>
          <div class="text-xs text-slate-400 dark:text-slate-500 truncate"><?= $sub ?></div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 transition-colors flex-shrink-0"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Recent Calls (2/3) -->
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-bold text-slate-800 dark:text-white text-sm flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>Son Çağrılar
      </h3>
      <a href="<?= \App\Helpers\Url::to('/calls/history') ?>" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
        Tümünü gör <i class="fa-solid fa-arrow-right text-xs"></i>
      </a>
    </div>

    <?php if (!empty($recentCalls)): ?>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 dark:border-slate-700">
            <th class="text-left text-xs font-bold text-slate-400 uppercase pb-2 pr-3">Tarih</th>
            <th class="text-left text-xs font-bold text-slate-400 uppercase pb-2 pr-3">Src</th>
            <th class="text-left text-xs font-bold text-slate-400 uppercase pb-2 pr-3">Dst</th>
            <th class="text-left text-xs font-bold text-slate-400 uppercase pb-2 pr-3">Durum</th>
            <th class="text-right text-xs font-bold text-slate-400 uppercase pb-2">Süre</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
          <?php foreach ($recentCalls as $c):
            $disp = strtoupper($c['disposition']);
            $dispClass = match(true) {
              $disp === 'ANSWERED' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
              $disp === 'BUSY'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
              $disp === 'FAILED'   => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
              default              => 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
            };
          ?>
          <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition-colors">
            <td class="py-2 pr-3 whitespace-nowrap">
              <div class="text-xs font-medium text-slate-700 dark:text-slate-300"><?= date('d.m', strtotime($c['start'])) ?></div>
              <div class="text-xs text-slate-400"><?= date('H:i', strtotime($c['start'])) ?></div>
            </td>
            <td class="py-2 pr-3 font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap"><?= htmlspecialchars($c['src']) ?></td>
            <td class="py-2 pr-3 font-mono text-xs font-semibold text-purple-600 dark:text-purple-400 whitespace-nowrap"><?= htmlspecialchars($c['dst']) ?></td>
            <td class="py-2 pr-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold <?= $dispClass ?>">
                <?= $disp ?>
              </span>
            </td>
            <td class="py-2 text-right font-mono text-xs text-slate-600 dark:text-slate-300"><?= gmdate('H:i:s', (int)$c['duration']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
        <i class="fa-solid fa-phone-slash text-2xl text-slate-300 dark:text-slate-500"></i>
      </div>
      <p class="text-slate-500 dark:text-slate-400 text-sm">Henüz çağrı yok</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══════════════════════════════════════════════════ SYSTEM STATUS -->
<div class="grid sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-globe text-emerald-600 dark:text-emerald-400"></i>
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-slate-800 dark:text-white text-sm"><?= __('api_connection') ?></div>
      <div class="text-xs text-slate-400"><?= __('voip_api_status') ?></div>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
      <i class="fa-solid fa-circle text-emerald-500 text-xs animate-pulse"></i>
      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"><?= __('active') ?></span>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-database text-blue-600 dark:text-blue-400"></i>
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-slate-800 dark:text-white text-sm"><?= __('database') ?></div>
      <div class="text-xs text-slate-400"><?= __('mysql_connection') ?></div>
    </div>
    <div class="flex items-center gap-1.5 flex-shrink-0">
      <i class="fa-solid fa-circle text-emerald-500 text-xs animate-pulse"></i>
      <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400"><?= __('active') ?></span>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center flex-shrink-0">
      <i class="fa-solid fa-rotate text-purple-600 dark:text-purple-400"></i>
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-slate-800 dark:text-white text-sm"><?= __('last_sync') ?></div>
      <div class="text-xs text-slate-400"><?= __('data_update') ?></div>
    </div>
    <div class="text-right flex-shrink-0">
      <div class="text-xs font-semibold text-slate-700 dark:text-slate-300"><?= date('H:i') ?></div>
      <div class="text-xs text-slate-400"><?= __('automatic') ?></div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════ SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Live clock
setInterval(() => {
  const el = document.getElementById('currentTime');
  if (el) el.textContent = new Date().toLocaleTimeString('tr-TR', {hour12:false});
}, 1000);

<?php if (!$isGroupMember): ?>
const isDark   = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
const textColor = isDark ? '#94a3b8' : '#64748b';

const tooltipDefaults = {
  backgroundColor: 'rgba(15,23,42,0.9)',
  titleColor: '#fff',
  bodyColor: '#fff',
  borderColor: 'rgba(255,255,255,0.1)',
  borderWidth: 1,
  cornerRadius: 10,
  padding: 10,
};

// ── Trend Line ───────────────────────────────────────────────────
const trendCtx = document.getElementById('trendLine');
if (trendCtx) {
  const labels  = <?= json_encode($chartLabels  ?? []) ?>;
  const revenue = <?= json_encode($chartRevenue ?? []) ?>;
  const cost    = <?= json_encode($chartCost    ?? []) ?>;
  new Chart(trendCtx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        <?php if ($isSuper): ?>
        {
          label: 'Gelir', data: revenue,
          borderColor: 'rgba(16,185,129,1)', backgroundColor: 'rgba(16,185,129,0.1)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointBackgroundColor: 'rgba(16,185,129,1)', pointRadius: 4, pointHoverRadius: 6,
        },
        {
          label: 'Maliyet', data: cost,
          borderColor: 'rgba(239,68,68,1)', backgroundColor: 'rgba(239,68,68,0.07)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointBackgroundColor: 'rgba(239,68,68,1)', pointRadius: 4, pointHoverRadius: 6,
        },
        <?php else: ?>
        {
          label: 'Harcama', data: revenue,
          borderColor: 'rgba(99,102,241,1)', backgroundColor: 'rgba(99,102,241,0.1)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointBackgroundColor: 'rgba(99,102,241,1)', pointRadius: 4, pointHoverRadius: 6,
        },
        <?php endif; ?>
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, color: textColor, font: { size: 11, weight: '600' } } },
        tooltip: { ...tooltipDefaults, callbacks: { label: c => c.dataset.label + ': $' + c.parsed.y.toFixed(2) } }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => '$'+v.toFixed(2) } },
        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
      },
      animation: { duration: 1200, easing: 'easeInOutQuart' }
    }
  });
}

// ── Calls Bar ────────────────────────────────────────────────────
const callsCtx = document.getElementById('callsBar');
if (callsCtx) {
  const labels = <?= json_encode($chartLabels ?? []) ?>;
  const calls  = <?= json_encode($chartCalls  ?? []) ?>;
  new Chart(callsCtx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Çağrı', data: calls,
        backgroundColor: 'rgba(99,102,241,0.75)', borderColor: 'rgba(99,102,241,1)',
        borderWidth: 1.5, borderRadius: 6, borderSkipped: false,
        hoverBackgroundColor: 'rgba(99,102,241,1)',
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { ...tooltipDefaults, callbacks: { label: c => 'Çağrı: ' + c.parsed.y } }
      },
      scales: {
        y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
      },
      animation: { duration: 1200, easing: 'easeInOutQuart', delay: c => c.dataIndex * 80 }
    }
  });
}

// ── Disposition Donut ────────────────────────────────────────────
const donutCtx = document.getElementById('dispositionDonut');
if (donutCtx) {
  const dispData = [
    <?= (int)($dispositionData['ANSWERED']  ?? 0) ?>,
    <?= (int)($dispositionData['NO ANSWER'] ?? 0) ?>,
    <?= (int)($dispositionData['BUSY']      ?? 0) ?>,
    <?= (int)($dispositionData['FAILED']    ?? 0) ?>,
  ];
  new Chart(donutCtx, {
    type: 'doughnut',
    data: {
      labels: ['Cevap', 'Cevapsız', 'Meşgul', 'Başarısız'],
      datasets: [{
        data: dispData,
        backgroundColor: ['rgba(16,185,129,0.85)','rgba(148,163,184,0.85)','rgba(245,158,11,0.85)','rgba(239,68,68,0.85)'],
        borderColor: isDark ? '#1e293b' : '#fff',
        borderWidth: 3,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: {
        legend: { display: false },
        tooltip: { ...tooltipDefaults, callbacks: { label: c => c.label + ': ' + c.parsed } }
      },
      animation: { animateRotate: true, duration: 1200 }
    }
  });
}
<?php endif; ?>
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>