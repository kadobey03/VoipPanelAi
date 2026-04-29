<?php
$title = 'Agent Yönetimi - VoIP Panel';
require dirname(__DIR__).'/partials/header.php';

$isSuper      = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'superadmin';
$isGroupAdmin = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'groupadmin';

// ─── KPI hesapla ────────────────────────────────────────────────────────────
$kpiTotal   = 0;
$kpiOnline  = 0;
$kpiRinging = 0;
$kpiBusy    = 0;

if ($isSuper) {
    foreach (($agentsByGroup ?? []) as $gd) {
        foreach ($gd['agents'] ?? [] as $ag) {
            $kpiTotal++;
            $st = strtolower($ag['status'] ?? '');
            if ($st === 'up' || $st === 'online') $kpiOnline++;
            elseif ($st === 'ring' || $st === 'ringing') $kpiRinging++;
            elseif ($st === 'busy') $kpiBusy++;
        }
    }
} else {
    $gk = key($agentsByGroup ?? []);
    foreach (($agentsByGroup[$gk]['agents'] ?? []) as $ag) {
        $kpiTotal++;
        $st = strtolower($ag['status'] ?? '');
        if ($st === 'up' || $st === 'online') $kpiOnline++;
        elseif ($st === 'ring' || $st === 'ringing') $kpiRinging++;
        elseif ($st === 'busy') $kpiBusy++;
    }
}
$kpiOffline = $kpiTotal - $kpiOnline - $kpiRinging - $kpiBusy;

// ─── Yardımcı: status badge ─────────────────────────────────────────────────
function agentStatusBadge(string $status): string {
    $s = strtolower($status);
    if ($s === 'up' || $s === 'online')       return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Çevrimiçi</span>';
    if ($s === 'ring' || $s === 'ringing')    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Çalıyor</span>';
    if ($s === 'busy')                        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Meşgul</span>';
    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Çevrimdışı</span>';
}

// ─── Yardımcı: avatar rengi ──────────────────────────────────────────────────
function avatarGradient(string $name): string {
    $colors = [
        'from-rose-500 to-pink-600',
        'from-violet-500 to-purple-600',
        'from-blue-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-amber-500 to-orange-600',
        'from-cyan-500 to-sky-600',
    ];
    return $colors[abs(crc32($name)) % count($colors)];
}
?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl min-w-[200px]">
    <div class="w-12 h-12 rounded-full border-4 border-rose-200 border-t-rose-600 animate-spin"></div>
    <p class="font-semibold text-slate-700 dark:text-slate-200">Agent bilgileri yükleniyor…</p>
  </div>
</div>

<!-- ══════ HEADER ══════ -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg shadow-rose-500/30 flex-shrink-0">
      <i class="fa-solid fa-headset text-white text-xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Agent Yönetimi</h1>
      <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">
        Çağrı merkezinizin anlık durumu &nbsp;·&nbsp;
        <span class="text-rose-600 dark:text-rose-400 font-medium"><?= $kpiTotal ?> agent</span>
      </p>
    </div>
  </div>

  <?php if ($isSuper): ?>
  <form method="post" action="/VoipPanelAi/agents/sync">
    <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
      <i class="fa-solid fa-rotate"></i> Agentları Senkronize Et
    </button>
  </form>
  <?php endif; ?>
</div>

<!-- ══════ KPI CARDS ══════ -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <!-- Toplam -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-users text-slate-500 dark:text-slate-400 text-sm"></i>
      </div>
      <div>
        <div class="text-2xl font-bold text-slate-800 dark:text-white leading-none"><?= $kpiTotal ?></div>
        <div class="text-xs text-slate-400 mt-0.5">Toplam Agent</div>
      </div>
    </div>
  </div>
  <!-- Çevrimiçi -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-circle-check text-emerald-600 dark:text-emerald-400 text-sm"></i>
      </div>
      <div>
        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 leading-none"><?= $kpiOnline ?></div>
        <div class="text-xs text-slate-400 mt-0.5">Çevrimiçi</div>
      </div>
    </div>
  </div>
  <!-- Çalıyor -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-phone text-amber-600 dark:text-amber-400 text-sm"></i>
      </div>
      <div>
        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 leading-none"><?= $kpiRinging ?></div>
        <div class="text-xs text-slate-400 mt-0.5">Çalıyor</div>
      </div>
    </div>
  </div>
  <!-- Çevrimdışı -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-circle-xmark text-slate-400 text-sm"></i>
      </div>
      <div>
        <div class="text-2xl font-bold text-slate-500 dark:text-slate-400 leading-none"><?= $kpiOffline ?></div>
        <div class="text-xs text-slate-400 mt-0.5">Çevrimdışı</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════ SEARCH / FILTER ══════ -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-6">
  <div class="flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input id="agentSearch" type="text" placeholder="Agent adı veya extension ile ara…"
             class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 transition-all">
    </div>
    <select id="statusFilter"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 transition-all">
      <option value="">Tüm Durumlar</option>
      <option value="online">Çevrimiçi</option>
      <option value="ring">Çalıyor</option>
      <option value="busy">Meşgul</option>
      <option value="offline">Çevrimdışı</option>
    </select>
  </div>
</div>

<!-- ══════ FLASH MESSAGES ══════ -->
<?php if (!empty($_SESSION['success'])): ?>
<div class="mb-4 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-300 text-sm">
  <i class="fa-solid fa-circle-check flex-shrink-0"></i>
  <span><?= htmlspecialchars($_SESSION['success']) ?></span>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
<div class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-300 text-sm">
  <i class="fa-solid fa-triangle-exclamation flex-shrink-0"></i>
  <span><?= htmlspecialchars($_SESSION['error']) ?></span>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- ══════ AGENT GROUPS ══════ -->
<?php if ($isSuper): ?>
  <?php foreach (($agentsByGroup ?? []) as $groupIndex => $groupData): ?>
    <div class="mb-6 agent-group-section">
      <!-- Group Header -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-3">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
            <i class="fa-solid fa-layer-group text-white text-xs"></i>
          </div>
          <h2 class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($groupData['groupName'] ?? 'Grup') ?></h2>
          <span class="ml-auto px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold">
            <?= count($groupData['agents'] ?? []) ?> agent
          </span>
        </div>
      </div>

      <!-- Agent Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <?php foreach (($groupData['agents'] ?? []) as $a): ?>
          <?php
          $exten       = $a['exten'] ?? '';
          $login       = $a['user_login'] ?? '';
          $initials    = strtoupper(mb_substr($login ?: $exten, 0, 2));
          $gradient    = avatarGradient($login ?: $exten);
          $status      = strtolower($a['status'] ?? '');
          $isActive    = ($a['active'] ?? 1) == 1;
          $userAgents  = $subscriptionsByExten[$exten] ?? [];
          $statusNorm  = ($status === 'up') ? 'online' : ($status === 'ringing' ? 'ring' : $status);
          ?>
          <div class="agent-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all overflow-hidden"
               data-name="<?= htmlspecialchars(strtolower($login)) ?>"
               data-exten="<?= htmlspecialchars($exten) ?>"
               data-status="<?= htmlspecialchars($statusNorm) ?>">

            <!-- Card top color strip based on status -->
            <div class="h-1 w-full <?php
              if ($status === 'up' || $status === 'online') echo 'bg-emerald-500';
              elseif ($status === 'ring' || $status === 'ringing') echo 'bg-amber-400';
              elseif ($status === 'busy') echo 'bg-red-500';
              else echo 'bg-slate-200 dark:bg-slate-600';
            ?>"></div>

            <div class="p-4">
              <!-- Avatar + Name + Status -->
              <div class="flex items-start gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br <?= $gradient ?> flex items-center justify-center flex-shrink-0 shadow-sm">
                  <span class="text-white font-bold text-sm"><?= $initials ?></span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-slate-800 dark:text-white text-sm truncate">
                    <?= htmlspecialchars($login ?: '—') ?>
                  </div>
                  <div class="text-xs text-slate-400 mt-0.5">#<?= htmlspecialchars($exten) ?></div>
                </div>
                <?= agentStatusBadge($a['status'] ?? '') ?>
              </div>

              <!-- Details -->
              <div class="space-y-1.5 text-xs mb-3">
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                  <span>Sistem Durumu</span>
                  <span class="font-medium <?= $isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' ?>">
                    <?= $isActive ? 'Aktif' : 'Pasif' ?>
                  </span>
                </div>
                <?php if (!empty($a['group_name'])): ?>
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                  <span>Grup</span>
                  <span class="font-medium text-slate-700 dark:text-slate-300 truncate max-w-[120px]">
                    <?= htmlspecialchars($a['group_name']) ?>
                  </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($a['las_call_time'])): ?>
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                  <span>Son Çağrı</span>
                  <span class="font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars((string)$a['las_call_time']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($a['lead'])): ?>
                <div class="flex justify-between text-slate-500 dark:text-slate-400">
                  <span>Lead</span>
                  <span class="font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($a['lead']) ?></span>
                </div>
                <?php endif; ?>
              </div>

              <!-- Subscription badges -->
              <?php if (!empty($userAgents)): ?>
              <div class="mb-3 rounded-xl bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800/40 p-2.5">
                <div class="flex items-center gap-1.5 mb-1.5">
                  <i class="fa-solid fa-crown text-violet-500 text-xs"></i>
                  <span class="text-xs font-semibold text-violet-700 dark:text-violet-300">Aktif Abonelikler (<?= count($userAgents) ?>)</span>
                </div>
                <?php foreach ($userAgents as $ua): ?>
                <div class="flex items-center justify-between mb-1 last:mb-0">
                  <span class="text-xs text-violet-700 dark:text-violet-300 font-medium truncate max-w-[120px]">
                    <?= htmlspecialchars($ua['product_name']) ?>
                  </span>
                  <div class="flex items-center gap-1">
                    <?php if ($isSuper): ?>
                    <form method="post" action="/VoipPanelAi/agents/remove-subscription" class="inline">
                      <input type="hidden" name="user_agent_id" value="<?= $ua['id'] ?>">
                      <button type="submit"
                              onclick="return confirm('Bu aboneliği iptal etmek istediğinizden emin misiniz?')"
                              class="w-5 h-5 flex items-center justify-center text-red-400 hover:text-red-600 transition-colors">
                        <i class="fa-solid fa-xmark text-xs"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                      $<?= number_format($ua['subscription_monthly_fee'] ?? 0, 2) ?>/ay
                    </span>
                  </div>
                </div>
                <?php if (!empty($ua['subscription_end'])): ?>
                <?php
                  $nextTs   = strtotime($ua['subscription_end']);
                  $daysLeft = ceil(($nextTs - time()) / 86400);
                ?>
                <div class="text-xs text-slate-400 flex justify-between mt-1">
                  <span>#<?= htmlspecialchars($ua['agent_number'] ?? '') ?></span>
                  <span class="<?= $daysLeft >= 0 ? 'text-blue-500' : 'text-red-500' ?>">
                    <?= date('d.m.Y', $nextTs) ?>
                    (<?= $daysLeft >= 0 ? $daysLeft . ' gün' : abs($daysLeft) . ' gün gecikmiş' ?>)
                  </span>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <!-- Action Buttons (superadmin only) -->
              <?php if ($isSuper): ?>
              <div class="grid gap-1.5">
                <form method="post" action="/VoipPanelAi/agents/toggle-active">
                  <input type="hidden" name="exten" value="<?= htmlspecialchars($exten) ?>">
                  <button type="submit"
                          class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                          <?= $isActive ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-800/40'
                                        : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800/40' ?>">
                    <i class="fa-solid <?= $isActive ? 'fa-ban' : 'fa-check' ?>"></i>
                    <?= $isActive ? 'Deaktif Et' : 'Aktif Et' ?>
                  </button>
                </form>

                <button onclick="openEditNameModal('<?= htmlspecialchars($exten) ?>', '<?= htmlspecialchars($login) ?>')"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800/40 transition-all">
                  <i class="fa-solid fa-pen-to-square"></i> Adını Değiştir
                </button>

                <?php if (!empty($userAgents)): ?>
                <button onclick="openEditSubscriptionModal('<?= htmlspecialchars($exten) ?>', '<?= htmlspecialchars($login) ?>', <?= htmlspecialchars(json_encode($userAgents)) ?>)"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/40 border border-amber-200 dark:border-amber-800/40 transition-all">
                  <i class="fa-solid fa-pen-to-square"></i> Abonelik Düzenle
                </button>
                <?php else: ?>
                <button onclick="openAddSubscriptionModal('<?= htmlspecialchars($exten) ?>', '<?= htmlspecialchars($login) ?>', '<?= htmlspecialchars($a['group_name'] ?? $groupData['groupName'] ?? '') ?>')"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400 hover:bg-violet-100 dark:hover:bg-violet-900/40 border border-violet-200 dark:border-violet-800/40 transition-all">
                  <i class="fa-solid fa-plus"></i> Abonelik Ekle
                </button>
                <?php endif; ?>
              </div>
              <?php elseif ($isGroupAdmin): ?>
              <div class="text-center pt-1">
                <?php if (!empty($userAgents)): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-violet-50 dark:bg-violet-900/20 text-violet-700 dark:text-violet-300 rounded-lg text-xs font-semibold border border-violet-200 dark:border-violet-800/40">
                  <i class="fa-solid fa-info-circle"></i> Abonelik Görüntüleniyor
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-800/40">
                  <i class="fa-solid fa-check"></i> Aktif Agent
                </span>
                <?php endif; ?>
              </div>
              <?php else: ?>
              <div class="text-center pt-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-800/40">
                  <i class="fa-solid fa-check"></i> Aktif Agent
                </span>
              </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

<?php else: ?>
  <!-- ── Non-super view ── -->
  <?php
  $gk        = key($agentsByGroup ?? []);
  $groupData = $agentsByGroup[$gk] ?? [];
  $agents    = $groupData['agents'] ?? [];
  ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-4">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
        <i class="fa-solid fa-layer-group text-white text-xs"></i>
      </div>
      <h2 class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($groupData['groupName'] ?? 'Grubunuz') ?></h2>
      <span class="ml-auto px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold">
        <?= count($agents) ?> agent
      </span>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php foreach ($agents as $a): ?>
      <?php
      $exten      = $a['exten'] ?? '';
      $login      = $a['user_login'] ?? '';
      $initials   = strtoupper(mb_substr($login ?: $exten, 0, 2));
      $gradient   = avatarGradient($login ?: $exten);
      $status     = strtolower($a['status'] ?? '');
      $isActive   = ($a['active'] ?? 1) == 1;
      $statusNorm = ($status === 'up') ? 'online' : ($status === 'ringing' ? 'ring' : $status);
      ?>
      <div class="agent-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all overflow-hidden"
           data-name="<?= htmlspecialchars(strtolower($login)) ?>"
           data-exten="<?= htmlspecialchars($exten) ?>"
           data-status="<?= htmlspecialchars($statusNorm) ?>">

        <div class="h-1 w-full <?php
          if ($status === 'up' || $status === 'online') echo 'bg-emerald-500';
          elseif ($status === 'ring' || $status === 'ringing') echo 'bg-amber-400';
          elseif ($status === 'busy') echo 'bg-red-500';
          else echo 'bg-slate-200 dark:bg-slate-600';
        ?>"></div>

        <div class="p-4">
          <div class="flex items-start gap-3 mb-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br <?= $gradient ?> flex items-center justify-center flex-shrink-0 shadow-sm">
              <span class="text-white font-bold text-sm"><?= $initials ?></span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-slate-800 dark:text-white text-sm truncate">
                <?= htmlspecialchars($login ?: '—') ?>
              </div>
              <div class="text-xs text-slate-400 mt-0.5">#<?= htmlspecialchars($exten) ?></div>
            </div>
            <?= agentStatusBadge($a['status'] ?? '') ?>
          </div>

          <div class="space-y-1.5 text-xs mb-3">
            <div class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Sistem Durumu</span>
              <span class="font-medium <?= $isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' ?>">
                <?= $isActive ? 'Aktif' : 'Pasif' ?>
              </span>
            </div>
            <?php if (!empty($a['las_call_time'])): ?>
            <div class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Son Çağrı</span>
              <span class="font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars((string)$a['las_call_time']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($a['lead'])): ?>
            <div class="flex justify-between text-slate-500 dark:text-slate-400">
              <span>Lead</span>
              <span class="font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($a['lead']) ?></span>
            </div>
            <?php endif; ?>
          </div>

          <div class="text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-200 dark:border-emerald-800/40">
              <i class="fa-solid fa-check"></i> Aktif Agent
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Empty state -->
<div id="emptyState" class="hidden text-center py-16">
  <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
    <i class="fa-solid fa-headset text-slate-400 text-2xl"></i>
  </div>
  <p class="text-slate-500 dark:text-slate-400 font-medium">Arama kriterine uygun agent bulunamadı</p>
</div>


<!-- ══════════════════════════════════════════════
     MODALS
════════════════════════════════════════════════ -->

<!-- Agent Adı Değiştirme Modalı -->
<div id="editNameModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square text-blue-600"></i>
        <h3 class="font-bold text-slate-800 dark:text-white">Agent Adını Değiştir</h3>
      </div>
      <button onclick="closeEditNameModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="editNameForm" method="post" action="/VoipPanelAi/agents/update-agent-name" class="p-5 space-y-4">
      <input type="hidden" id="editNameExten" name="exten" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Mevcut Ad</label>
        <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-700 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-200">
          <span id="currentAgentName"></span>
        </div>
      </div>
      <div>
        <label for="newAgentName" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Yeni Ad</label>
        <input type="text" id="newAgentName" name="new_name" required
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
               placeholder="Yeni agent adını girin">
      </div>
      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeEditNameModal()"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          İptal
        </button>
        <button type="submit"
                class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
          <i class="fa-solid fa-floppy-disk mr-1.5"></i>Kaydet
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Abonelik Ekleme Modalı -->
<div id="addSubscriptionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-plus text-violet-600"></i>
        <h3 class="font-bold text-slate-800 dark:text-white">Abonelik Ekle</h3>
      </div>
      <button onclick="closeAddSubscriptionModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="addSubscriptionForm" method="post" action="/VoipPanelAi/agents/add-subscription" class="p-5 space-y-4">
      <input type="hidden" id="subscriptionExten" name="agent_exten" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Agent</label>
        <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-700 rounded-xl text-sm">
          <span id="subscriptionAgentName" class="font-semibold text-slate-800 dark:text-white"></span>
          <span class="text-slate-400 ml-2">— Extension: #<span id="subscriptionAgentExten"></span></span>
          <div class="text-xs text-slate-400 mt-0.5">Grup: <span id="subscriptionAgentGroup" class="font-medium text-slate-600 dark:text-slate-300">—</span></div>
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Abonelik Sahibi</label>
        <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-700 rounded-xl text-xs text-slate-500 dark:text-slate-400">
          Agent'ın grubunun yöneticisine otomatik atanacak
        </div>
      </div>
      <div>
        <label for="agentProductId" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Agent Ürünü</label>
        <select id="agentProductId" name="agent_product_id" required
                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
          <option value="">Ürün seçin…</option>
          <?php foreach (($agentProducts ?? []) as $product): ?>
          <option value="<?= $product['id'] ?>"
                  data-price="<?= $product['price'] ?>"
                  data-monthly="<?= $product['subscription_monthly_fee'] ?? 0 ?>">
            <?= htmlspecialchars($product['name']) ?> — $<?= number_format($product['price'], 2) ?>
            <?php if (($product['subscription_monthly_fee'] ?? 0) > 0): ?>
              (Aylık: $<?= number_format($product['subscription_monthly_fee'], 2) ?>)
            <?php endif; ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Fiyat bilgisi (dinamik) -->
      <div id="priceInfo" class="hidden px-3 py-2.5 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800/40 rounded-xl text-xs space-y-1">
        <div class="flex justify-between text-violet-700 dark:text-violet-300">
          <span>Kurulum Ücreti:</span><span id="setupPrice" class="font-bold"></span>
        </div>
        <div id="monthlyFeeInfo" class="hidden flex justify-between text-violet-700 dark:text-violet-300">
          <span>Aylık Abonelik:</span><span id="monthlyPrice" class="font-bold"></span>
        </div>
      </div>

      <div>
        <label for="agentNumber" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Agent Numarası</label>
        <input type="text" id="agentNumber" name="agent_number" required
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
               placeholder="Örn: 05551234567">
      </div>
      <div>
        <label for="subscriptionStartDate" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">
          <i class="fa-solid fa-calendar mr-1"></i>Başlangıç Tarihi
        </label>
        <input type="date" id="subscriptionStartDate" name="subscription_start_date"
               value="<?= date('Y-m-d') ?>"
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
        <p class="text-xs text-slate-400 mt-1">Boş bırakılırsa bugünden başlar.</p>
      </div>
      <div class="px-3 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" id="subscriptionPaidCheckbox" name="subscription_paid"
                 class="mt-0.5 w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
          <div>
            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">
              <i class="fa-solid fa-credit-card mr-1 text-emerald-600"></i>Manuel ödeme olarak işaretle
            </div>
            <p class="text-xs text-slate-400 mt-0.5">
              ✅ İşaretli: Ödeme yapıldı kaydedilir &nbsp;·&nbsp; ⚠️ İşaretsiz: Bakiyeden otomatik düşer
            </p>
          </div>
        </label>
      </div>
      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeAddSubscriptionModal()"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          İptal
        </button>
        <button type="submit"
                class="flex-1 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold transition-colors">
          <i class="fa-solid fa-plus mr-1.5"></i>Abonelik Ekle
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Abonelik Düzenleme Modalı -->
<div id="editSubscriptionModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-pen-to-square text-amber-600"></i>
        <h3 class="font-bold text-slate-800 dark:text-white">Abonelik Düzenle</h3>
      </div>
      <button onclick="closeEditSubscriptionModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition-colors">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <form id="editSubscriptionForm" method="post" action="/VoipPanelAi/agents/update-subscription" class="p-5 space-y-4">
      <input type="hidden" id="editSubscriptionId" name="user_agent_id" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Agent</label>
        <div class="px-3 py-2.5 bg-slate-50 dark:bg-slate-700 rounded-xl text-sm">
          <span id="editSubscriptionAgentName" class="font-semibold text-slate-800 dark:text-white"></span>
          <span class="text-slate-400 ml-2">— Extension: #<span id="editSubscriptionAgentExten"></span></span>
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Mevcut Abonelik</label>
        <div id="currentSubscriptionInfo" class="px-3 py-2.5 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800/40 rounded-xl text-xs space-y-1"></div>
      </div>
      <div>
        <label for="editSubscriptionStartDate" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">
          <i class="fa-solid fa-calendar-plus mr-1"></i>Abonelik Başlangıç Tarihi
        </label>
        <input type="date" id="editSubscriptionStartDate" name="subscription_start_date"
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
      </div>
      <div>
        <label for="editNextPaymentDate" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">
          <i class="fa-solid fa-calendar-check mr-1"></i>Sonraki Ödeme Tarihi
        </label>
        <input type="date" id="editNextPaymentDate" name="next_payment_date"
               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
        <p class="text-xs text-slate-400 mt-1">Bu tarihte otomatik ödeme alınacak.</p>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">
          <i class="fa-solid fa-toggle-on mr-1"></i>Abonelik Durumu
        </label>
        <select id="editSubscriptionStatus" name="subscription_status"
                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
          <option value="active">🟢 Aktif</option>
          <option value="suspended">🟡 Askıya Alınmış</option>
          <option value="cancelled">🔴 İptal Edilmiş</option>
        </select>
      </div>
      <div class="px-3 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl">
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" id="editManualPayment" name="mark_paid"
                 class="mt-0.5 w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
          <div>
            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300">
              <i class="fa-solid fa-hand-holding-dollar mr-1 text-emerald-600"></i>Sonraki ödemeyi manuel ödendi işaretle
            </div>
            <p class="text-xs text-slate-400 mt-0.5">Sonraki ödeme tarihi 1 ay ileri alınır.</p>
          </div>
        </label>
      </div>
      <div class="flex gap-3 pt-1">
        <button type="button" onclick="closeEditSubscriptionModal()"
                class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
          İptal
        </button>
        <button type="submit"
                class="flex-1 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-colors">
          <i class="fa-solid fa-floppy-disk mr-1.5"></i>Güncelle
        </button>
      </div>
    </form>
  </div>
</div>


<!-- ══════ SCRIPTS ══════ -->
<script>
/* ── Arama / Filtre ──────────────────────────────────────────────────── */
(function() {
  const searchInput  = document.getElementById('agentSearch');
  const statusFilter = document.getElementById('statusFilter');

  function filterCards() {
    const query  = searchInput.value.trim().toLowerCase();
    const status = statusFilter.value.toLowerCase();

    let anyVisible = false;
    document.querySelectorAll('.agent-card').forEach(card => {
      const name   = card.dataset.name   || '';
      const exten  = card.dataset.exten  || '';
      const cStatus= card.dataset.status || 'offline';

      const matchSearch = !query || name.includes(query) || exten.includes(query);
      const matchStatus = !status
        || (status === 'online'  && (cStatus === 'online' || cStatus === 'up'))
        || (status === 'ring'    && (cStatus === 'ring'   || cStatus === 'ringing'))
        || (status === 'busy'    && cStatus === 'busy')
        || (status === 'offline' && cStatus !== 'online' && cStatus !== 'up' && cStatus !== 'ring' && cStatus !== 'ringing' && cStatus !== 'busy');

      const show = matchSearch && matchStatus;
      card.style.display = show ? '' : 'none';
      if (show) anyVisible = true;
    });

    document.getElementById('emptyState').classList.toggle('hidden', anyVisible);
  }

  searchInput.addEventListener('input', filterCards);
  statusFilter.addEventListener('change', filterCards);
})();

/* ── Modal: Agent Adı ────────────────────────────────────────────────── */
function openEditNameModal(exten, currentName) {
  document.getElementById('editNameExten').value        = exten;
  document.getElementById('currentAgentName').textContent = currentName;
  document.getElementById('newAgentName').value         = currentName;
  const m = document.getElementById('editNameModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
  document.getElementById('newAgentName').focus();
}
function closeEditNameModal() {
  const m = document.getElementById('editNameModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
  document.getElementById('editNameForm').reset();
}

/* ── Modal: Abonelik Ekle ────────────────────────────────────────────── */
function openAddSubscriptionModal(exten, agentName, groupName) {
  document.getElementById('subscriptionExten').value             = exten;
  document.getElementById('subscriptionAgentName').textContent   = agentName;
  document.getElementById('subscriptionAgentExten').textContent  = exten;
  document.getElementById('subscriptionAgentGroup').textContent  = groupName || '—';
  const m = document.getElementById('addSubscriptionModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeAddSubscriptionModal() {
  const m = document.getElementById('addSubscriptionModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
  document.getElementById('addSubscriptionForm').reset();
  document.getElementById('priceInfo').classList.add('hidden');
}

/* ── Modal: Abonelik Düzenle ─────────────────────────────────────────── */
function openEditSubscriptionModal(exten, agentName, userAgents) {
  document.getElementById('editSubscriptionAgentName').textContent  = agentName;
  document.getElementById('editSubscriptionAgentExten').textContent = exten;

  if (userAgents && userAgents.length > 0) {
    const sub = userAgents[0];
    document.getElementById('editSubscriptionId').value = sub.id;

    const info = document.getElementById('currentSubscriptionInfo');
    info.innerHTML = `
      <div class="flex justify-between text-violet-700 dark:text-violet-300"><span>Ürün:</span><span class="font-semibold">${sub.product_name}</span></div>
      <div class="flex justify-between text-violet-700 dark:text-violet-300"><span>Agent No:</span><span class="font-semibold">#${sub.agent_number || '—'}</span></div>
      <div class="flex justify-between text-violet-700 dark:text-violet-300"><span>Aylık:</span><span class="font-semibold">$${parseFloat(sub.subscription_monthly_fee || 0).toFixed(2)}</span></div>
    `;

    if (sub.created_at) {
      document.getElementById('editSubscriptionStartDate').value = sub.created_at.split(' ')[0] || sub.created_at.split('T')[0];
    }
    if (sub.next_subscription_due) {
      document.getElementById('editNextPaymentDate').value = sub.next_subscription_due.split(' ')[0] || sub.next_subscription_due.split('T')[0];
    }
    document.getElementById('editSubscriptionStatus').value = sub.status || 'active';
  }

  const m = document.getElementById('editSubscriptionModal');
  m.classList.remove('hidden');
  m.classList.add('flex');
}
function closeEditSubscriptionModal() {
  const m = document.getElementById('editSubscriptionModal');
  m.classList.add('hidden');
  m.classList.remove('flex');
  document.getElementById('editSubscriptionForm').reset();
}

/* ── Ürün seçildiğinde fiyat bilgisi ────────────────────────────────── */
document.getElementById('agentProductId').addEventListener('change', function() {
  const opt     = this.options[this.selectedIndex];
  const priceEl = document.getElementById('priceInfo');
  if (!opt.value) { priceEl.classList.add('hidden'); return; }

  const price   = parseFloat(opt.dataset.price   || 0);
  const monthly = parseFloat(opt.dataset.monthly || 0);

  document.getElementById('setupPrice').textContent  = '$' + price.toFixed(2);
  const mEl = document.getElementById('monthlyFeeInfo');
  if (monthly > 0) {
    document.getElementById('monthlyPrice').textContent = '$' + monthly.toFixed(2);
    mEl.classList.remove('hidden');
  } else {
    mEl.classList.add('hidden');
  }
  priceEl.classList.remove('hidden');
});

/* ── Modal dışı tıklama / Escape ─────────────────────────────────────── */
['editNameModal','addSubscriptionModal','editSubscriptionModal'].forEach(id => {
  document.getElementById(id).addEventListener('click', function(e) {
    if (e.target === this) {
      this.classList.add('hidden');
      this.classList.remove('flex');
    }
  });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeEditNameModal();
    closeAddSubscriptionModal();
    closeEditSubscriptionModal();
  }
});

/* ── Loading overlay ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const ov = document.getElementById('loading-overlay');
  if (ov) ov.classList.add('hidden');
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>