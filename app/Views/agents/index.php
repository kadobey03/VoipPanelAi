<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl">
    <div class="animate-spin rounded-full h-12 w-12 border-4 border-rose-500 border-t-transparent"></div>
    <div class="text-lg font-medium text-slate-700 dark:text-slate-300">Agent bilgileri yükleniyor...</div>
  </div>
</div>

<div class="animate-in slide-in-from-left-5 duration-500">
  <!-- Modern Hero Header -->
  <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 mb-8 text-white">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-10 left-10 w-32 h-32 bg-white/20 rounded-full blur-xl"></div>
      <div class="absolute bottom-10 right-10 w-48 h-48 bg-white/20 rounded-full blur-2xl"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Content -->
    <div class="relative px-8 py-12 lg:px-12 lg:py-16">
      <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
              <i class="fa-solid fa-headset text-4xl"></i>
            </div>
            <div>
              <h1 class="text-4xl lg:text-5xl font-bold">Agent Durumları</h1>
              <p class="text-xl text-white/80 mt-2">Temsilci durumlarını gerçek zamanlı takip edin</p>
            </div>
          </div>

          <?php if ($isSuper): ?>
          <div class="flex gap-3">
            <form method="post" action="/VoipPanelAi/agents/sync" style="display:inline;">
              <button type="submit" class="group relative inline-flex items-center gap-3 px-6 py-4 bg-white/20 backdrop-blur-sm rounded-2xl hover:bg-white/30 transition-all duration-300 transform hover:scale-105">
                <div class="p-2 bg-white/30 rounded-lg group-hover:bg-white/40 transition-colors duration-300">
                  <i class="fa-solid fa-sync-alt text-lg"></i>
                </div>
                <span class="font-semibold">Agentleri Güncelle</span>
              </button>
            </form>
          </div>
          <?php endif; ?>
        </div>

        <?php
        $totalAgents = 0;
        $onlineAgents = 0;
        $activeAgents = 0;
        $ringingAgents = 0;

        if ($isSuper) {
          foreach (($agentsByGroup ?? []) as $groupName => $agents) {
            $totalAgents += count($agents);
            foreach ($agents as $agent) {
              $status = strtolower($agent['status'] ?? '');
              if ($status === 'online' || $status === 'up') $onlineAgents++;
              if ($status === 'ring') $ringingAgents++;
              if ($agent['active'] ?? 1) $activeAgents++;
            }
          }
        } else {
          $agents = $agentsByGroup[key($agentsByGroup ?? [])] ?? [];
          $totalAgents = count($agents);
          foreach ($agents as $agent) {
            $status = strtolower($agent['status'] ?? '');
            if ($status === 'online' || $status === 'up') $onlineAgents++;
            if ($status === 'ring') $ringingAgents++;
            if ($agent['active'] ?? 1) $activeAgents++;
          }
        }
        ?>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-white/30 rounded-xl">
                <i class="fa-solid fa-users text-2xl"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold"><?php echo $totalAgents; ?></div>
                <div class="text-sm opacity-80">Toplam Agent</div>
              </div>
            </div>
          </div>

          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-emerald-500/30 rounded-xl">
                <i class="fa-solid fa-circle-check text-2xl"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold"><?php echo $onlineAgents; ?></div>
                <div class="text-sm opacity-80">Çevrimiçi</div>
              </div>
            </div>
          </div>

          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-amber-500/30 rounded-xl">
                <i class="fa-solid fa-phone text-2xl"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold"><?php echo $ringingAgents; ?></div>
                <div class="text-sm opacity-80">Çalıyor</div>
              </div>
            </div>
          </div>

          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-blue-500/30 rounded-xl">
                <i class="fa-solid fa-toggle-on text-2xl"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold"><?php echo $activeAgents; ?></div>
                <div class="text-sm opacity-80">Aktif</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
  </section>
</div>
  <!-- Advanced Filters and Search -->
  <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 p-6 mb-8">
    <div class="flex items-center gap-2 mb-4">
      <i class="fa-solid fa-filter text-rose-600"></i>
      <h3 class="text-lg font-semibold text-slate-800 dark:text-white">Filtreler ve Arama</h3>
    </div>
  
    <div class="flex flex-col lg:flex-row gap-4">
      <!-- Search Input -->
      <div class="flex-1">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
          </div>
          <input type="text" id="agentSearch" placeholder="Agent ara (login, exten, lead)..."
                 class="w-full pl-12 pr-4 py-3 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-all duration-200">
        </div>
      </div>
  
      <!-- Status Filter -->
      <div class="flex gap-2 flex-wrap">
        <button onclick="filterByStatus('all')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
          <i class="fa-solid fa-list mr-2"></i>Tümü
        </button>
        <button onclick="filterByStatus('online')" class="px-4 py-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-xl hover:bg-emerald-200 dark:hover:bg-emerald-900/60 transition-colors">
          <i class="fa-solid fa-circle mr-2"></i>Çevrimiçi
        </button>
        <button onclick="filterByStatus('ringing')" class="px-4 py-2 bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-xl hover:bg-amber-200 dark:hover:bg-amber-900/60 transition-colors">
          <i class="fa-solid fa-phone mr-2"></i>Çalıyor
        </button>
        <button onclick="filterByStatus('offline')" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
          <i class="fa-solid fa-circle-xmark mr-2"></i>Çevrimdışı
        </button>
      </div>
    </div>
  </div>
  
  <?php if (!empty($error)): ?>
  <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
    <div class="flex items-center gap-3">
      <i class="fa-solid fa-exclamation-triangle text-red-500"></i>
      <span class="text-red-700 dark:text-red-300 font-medium"><?= htmlspecialchars($error) ?></span>
    </div>
  </div>
  <?php endif; ?>
<!-- Agents Grid -->
<?php if ($isSuper): ?>

  <?php foreach (($agentsByGroup ?? []) as $groupIndex => $groupData): ?>
    <div class="mb-8 animate-in slide-in-from-bottom-4 duration-500" style="animation-delay: <?= crc32($groupIndex) % 500 ?>ms">
      <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl">
          <i class="fa-solid fa-users text-white text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-bold text-slate-800 dark:text-white">
            <?= htmlspecialchars($groupData['groupName'] ?? 'Grup') ?>
          </h3>
          <p class="text-slate-500 dark:text-slate-400 text-sm">
            <?= count($groupData['agents'] ?? []) ?> Agent • <?= htmlspecialchars($groupData['groupName'] ?? '') ?>
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach (($groupData['agents'] ?? []) as $agentIndex => $a): ?>
        <div class="agent-card group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-rose-500/25 transition-all duration-300 transform hover:-translate-y-2 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden animate-in slide-in-from-bottom-4 duration-500" style="animation-delay: <?= ($groupIndex * 100) + ($agentIndex * 50) ?>ms">
          <!-- Status Indicator -->
          <div class="absolute top-4 right-4 z-10">
            <?php
            $status = strtolower($a['status'] ?? '');
            $statusClass = '';
            $statusColor = '';
            $pulseClass = '';

            if ($status === 'up' || $status === 'online') {
              $statusClass = 'bg-emerald-500';
              $statusColor = 'emerald';
              $pulseClass = 'animate-pulse';
            } elseif ($status === 'ring' || $status === 'ringing') {
              $statusClass = 'bg-amber-500';
              $statusColor = 'amber';
              $pulseClass = 'animate-bounce';
            } elseif ($status === 'busy') {
              $statusClass = 'bg-red-500';
              $statusColor = 'red';
            } else {
              $statusClass = 'bg-slate-400';
              $statusColor = 'slate';
            }
            ?>
            <div class="w-4 h-4 <?= $statusClass ?> rounded-full ring-4 ring-white dark:ring-slate-900 shadow-lg <?= $pulseClass ?>">
              <div class="absolute inset-0 rounded-full bg-current opacity-75 animate-ping"></div>
            </div>
          </div>

          <!-- Card Header -->
          <div class="p-6 pb-4">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white font-bold text-lg">
                  <?= strtoupper(substr(htmlspecialchars($a['user_login'] ?? 'A'), 0, 1)) ?>
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="text-lg font-bold text-slate-900 dark:text-white truncate">
                  <?= htmlspecialchars($a['user_login'] ?? '') ?>
                </h4>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  <i class="fa-solid fa-hashtag mr-1"></i>
                  <?= htmlspecialchars($a['exten'] ?? '') ?>
                </p>
              </div>
            </div>

            <!-- Status Badge -->
            <div class="mb-4">
              <?php
              $statusText = '';
              $statusBadgeClass = '';

              if ($status === 'up' || $status === 'online') {
                $statusText = 'Çevrimiçi';
                $statusBadgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
              } elseif ($status === 'ring' || $status === 'ringing') {
                $statusText = 'Çalıyor';
                $statusBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
              } elseif ($status === 'busy') {
                $statusText = 'Meşgul';
                $statusBadgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
              } else {
                $statusText = 'Çevrimdışı';
                $statusBadgeClass = 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
              }
              ?>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $statusBadgeClass ?>">
                <i class="fa-solid fa-circle mr-2 text-xs"></i>
                <?= $statusText ?>
              </span>
            </div>

            <!-- Agent Details -->
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600 dark:text-slate-400">
                  <i class="fa-solid fa-user-headset mr-1"></i>Son Çağrı
                </span>
                <span class="text-sm font-medium text-slate-900 dark:text-white">
                  <?= htmlspecialchars((string)($a['las_call_time'] ?? '-')) ?>
                </span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600 dark:text-slate-400">
                  <i class="fa-solid fa-user-tie mr-1"></i>Lead
                </span>
                <span class="text-sm font-medium text-slate-900 dark:text-white truncate max-w-20">
                  <?= htmlspecialchars($a['lead'] ?? '-') ?>
                </span>
              </div>

              <?php if ($isSuper): ?>
              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-600 dark:text-slate-400">
                  <i class="fa-solid fa-toggle-on mr-1"></i>Durum
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                  <?= ($a['active'] ?? 1) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300' ?>">
                  <?= ($a['active'] ?? 1) ? 'Aktif' : 'Pasif' ?>
                </span>
              </div>
              <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 mt-6 pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
              <button onclick="showAgentDetails(<?= $groupIndex ?>, <?= $agentIndex ?>)"
                      class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-medium bg-rose-100 text-rose-800 hover:bg-rose-200 dark:bg-rose-900/50 dark:text-rose-300 dark:hover:bg-rose-900/70 transition-colors duration-200">
                <i class="fa-solid fa-eye mr-1"></i>Detay
              </button>

              <?php if ($isSuper): ?>
              <form method="post" action="/VoipPanelAi/agents/toggleHidden" style="display:inline;">
                <input type="hidden" name="exten" value="<?= htmlspecialchars($a['exten']) ?>">
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium
                  <?= ($a['active'] ?? 1) ? 'bg-slate-600 text-white hover:bg-slate-700' : 'bg-emerald-600 text-white hover:bg-emerald-700' ?>
                  transition-colors duration-200">
                  <i class="fa-solid fa-<?= ($a['active'] ?? 1) ? 'ban' : 'check' ?> mr-1"></i>
                  <?= ($a['active'] ?? 1) ? 'Deaktif' : 'Aktif' ?>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </div>

          <!-- Hover Effect Overlay -->
          <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>

<?php else: ?>
  <!-- Single Group View for Non-Super Users -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php
    $agents = $agentsByGroup[key($agentsByGroup ?? [])] ?? [];
    foreach ($agents as $agentIndex => $a):
    ?>
    <div class="agent-card group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-rose-500/25 transition-all duration-300 transform hover:-translate-y-2 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden animate-in slide-in-from-bottom-4 duration-500" style="animation-delay: <?= $agentIndex * 50 ?>ms">
      <!-- Status Indicator -->
      <div class="absolute top-4 right-4 z-10">
        <?php
        $status = strtolower($a['status'] ?? '');
        $statusClass = '';
        $statusColor = '';
        $pulseClass = '';

        if ($status === 'up' || $status === 'online') {
          $statusClass = 'bg-emerald-500';
          $statusColor = 'emerald';
          $pulseClass = 'animate-pulse';
        } elseif ($status === 'ring' || $status === 'ringing') {
          $statusClass = 'bg-amber-500';
          $statusColor = 'amber';
          $pulseClass = 'animate-bounce';
        } elseif ($status === 'busy') {
          $statusClass = 'bg-red-500';
          $statusColor = 'red';
        } else {
          $statusClass = 'bg-slate-400';
          $statusColor = 'slate';
        }
        ?>
        <div class="w-4 h-4 <?= $statusClass ?> rounded-full ring-4 ring-white dark:ring-slate-900 shadow-lg <?= $pulseClass ?>">
          <div class="absolute inset-0 rounded-full bg-current opacity-75 animate-ping"></div>
        </div>
      </div>

      <!-- Card Header -->
      <div class="p-6 pb-4">
        <div class="flex items-center gap-4 mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
            <span class="text-white font-bold text-lg">
              <?= strtoupper(substr(htmlspecialchars($a['user_login'] ?? 'A'), 0, 1)) ?>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <h4 class="text-lg font-bold text-slate-900 dark:text-white truncate">
              <?= htmlspecialchars($a['user_login'] ?? '') ?>
            </h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              <i class="fa-solid fa-hashtag mr-1"></i>
              <?= htmlspecialchars($a['exten'] ?? '') ?>
            </p>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-4">
          <?php
          $statusText = '';
          $statusBadgeClass = '';

          if ($status === 'up' || $status === 'online') {
            $statusText = 'Çevrimiçi';
            $statusBadgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
          } elseif ($status === 'ring' || $status === 'ringing') {
            $statusText = 'Çalıyor';
            $statusBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
          } elseif ($status === 'busy') {
            $statusText = 'Meşgul';
            $statusBadgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
          } else {
            $statusText = 'Çevrimdışı';
            $statusBadgeClass = 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
          }
          ?>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $statusBadgeClass ?>">
            <i class="fa-solid fa-circle mr-2 text-xs"></i>
            <?= $statusText ?>
          </span>
        </div>

        <!-- Agent Details -->
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600 dark:text-slate-400">
              <i class="fa-solid fa-user-headset mr-1"></i>Son Çağrı
            </span>
            <span class="text-sm font-medium text-slate-900 dark:text-white">
              <?= htmlspecialchars((string)($a['las_call_time'] ?? '-')) ?>
            </span>
          </div>

          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600 dark:text-slate-400">
              <i class="fa-solid fa-user-tie mr-1"></i>Lead
            </span>
            <span class="text-sm font-medium text-slate-900 dark:text-white truncate max-w-20">
              <?= htmlspecialchars($a['lead'] ?? '-') ?>
            </span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2 mt-6 pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
          <button onclick="showAgentDetails(-1, <?= $agentIndex ?>)"
                  class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg text-xs font-medium bg-rose-100 text-rose-800 hover:bg-rose-200 dark:bg-rose-900/50 dark:text-rose-300 dark:hover:bg-rose-900/70 transition-colors duration-200">
            <i class="fa-solid fa-eye mr-1"></i>Detay
          </button>
        </div>
      </div>

      <!-- Hover Effect Overlay -->
      <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Agent Details Modal -->
<div id="agentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-user-headset mr-2 text-rose-500"></i>Agent Detayları
        </h3>
        <button onclick="closeAgentModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <div id="agentModalContent" class="p-6">
        <!-- Modal content will be populated by JavaScript -->
      </div>
    </div>
  </div>
</div>
</div>

<script>
// Store agents data for modal
const agentsData = <?php echo json_encode($agentsByGroup ?? []); ?>;

// Modal functions
function showAgentDetails(groupIndex, agentIndex) {
  let agent;
  let groupName = '';

  if (groupIndex >= 0 && agentsData && typeof agentsData === 'object') {
    const groups = Object.values(agentsData);
    if (groups[groupIndex] && groups[groupIndex]['agents']) {
      agent = groups[groupIndex]['agents'][agentIndex];
      groupName = groups[groupIndex]['groupName'] || Object.keys(agentsData)[groupIndex];
    }
  } else {
    // Single group view for non-super users
    const groupKey = Object.keys(agentsData)[0];
    const groupData = agentsData[groupKey];
    if (groupData && groupData['agents']) {
      agent = groupData['agents'][agentIndex];
      groupName = groupData['groupName'] || groupKey;
    }
  }

  if (!agent) return;

  const status = agent.status ? agent.status.toLowerCase() : '';
  let statusClass = 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
  let statusText = agent.status || 'Bilinmiyor';

  if (status === 'up' || status === 'online') {
    statusClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
    statusText = 'Çevrimiçi';
  } else if (status === 'ring' || status === 'ringing') {
    statusClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
    statusText = 'Çalıyor';
  } else if (status === 'busy') {
    statusClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
    statusText = 'Meşgul';
  }

  const modalContent = document.getElementById('agentModalContent');
  modalContent.innerHTML = `
    <div class="space-y-6">
      <!-- Agent Header -->
      <div class="flex items-center space-x-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg">
          <span class="text-white font-bold text-xl">
            ${agent.user_login ? agent.user_login.charAt(0).toUpperCase() : 'A'}
          </span>
        </div>
        <div>
          <h4 class="text-xl font-bold text-slate-900 dark:text-white">${agent.user_login || 'Bilinmiyor'}</h4>
          <p class="text-slate-600 dark:text-slate-400">Grup: ${groupName}</p>
        </div>
      </div>

      <!-- Agent Status -->
      <div class="flex items-center justify-between p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50">
        <div class="flex items-center gap-3">
          <div class="w-4 h-4 ${status === 'up' || status === 'online' ? 'bg-emerald-500' : status === 'ring' || status === 'ringing' ? 'bg-amber-500' : status === 'busy' ? 'bg-red-500' : 'bg-slate-400'} rounded-full"></div>
          <span class="font-medium text-slate-900 dark:text-white">Durum</span>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
          ${statusText}
        </span>
      </div>

      <!-- Agent Details Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Temel Bilgiler</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Login:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${agent.user_login || '-'}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Extension:</span>
              <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400">#${agent.exten || '-'}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Lead:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${agent.lead || '-'}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Son Çağrı:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${agent.las_call_time || '-'}</span>
            </div>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Durum Bilgileri</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Aktif:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                ${agent.active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300'}">
                ${agent.active ? 'Evet' : 'Hayır'}
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Grup:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${groupName}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Raw Durum:</span>
              <span class="font-mono text-sm text-slate-600 dark:text-slate-400">${agent.status || 'Bilinmiyor'}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  document.getElementById('agentModal').classList.remove('hidden');
}

function closeAgentModal() {
  document.getElementById('agentModal').classList.add('hidden');
}

// Filter functions
function filterByStatus(status) {
  const cards = document.querySelectorAll('.agent-card');
  const buttons = document.querySelectorAll('[onclick*="filterByStatus"]');

  // Update button states
  buttons.forEach(btn => {
    btn.classList.remove('ring-2', 'ring-rose-500', 'bg-rose-100', 'text-rose-800', 'dark:bg-rose-900/50', 'dark:text-rose-300');
    btn.classList.add('bg-slate-100', 'dark:bg-slate-700', 'text-slate-700', 'dark:text-slate-300');
  });

  if (status !== 'all') {
    event.target.classList.remove('bg-slate-100', 'dark:bg-slate-700', 'text-slate-700', 'dark:text-slate-300');
    event.target.classList.add('ring-2', 'ring-rose-500', 'bg-rose-100', 'text-rose-800', 'dark:bg-rose-900/50', 'dark:text-rose-300');
  }

  // Filter cards
  cards.forEach(card => {
    if (status === 'all') {
      card.style.display = 'block';
      card.classList.remove('animate-out', 'slide-out-to-bottom-4');
      setTimeout(() => {
        card.classList.add('animate-in', 'slide-in-from-bottom-4');
      }, 100);
    } else {
      const statusIndicator = card.querySelector('.absolute.top-4.right-4 div');
      let currentStatus = 'offline';

      if (statusIndicator && statusIndicator.classList.contains('bg-emerald-500')) {
        currentStatus = 'online';
      } else if (statusIndicator && statusIndicator.classList.contains('bg-amber-500')) {
        currentStatus = 'ringing';
      } else if (statusIndicator && statusIndicator.classList.contains('bg-red-500')) {
        currentStatus = 'busy';
      }

      if (currentStatus === status) {
        card.style.display = 'block';
        card.classList.remove('animate-out', 'slide-out-to-bottom-4');
        setTimeout(() => {
          card.classList.add('animate-in', 'slide-in-from-bottom-4');
        }, 100);
      } else {
        card.classList.add('animate-out', 'slide-out-to-bottom-4');
        setTimeout(() => {
          card.style.display = 'none';
        }, 300);
      }
    }
  });
}

// Search functionality
document.getElementById('agentSearch').addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const cards = document.querySelectorAll('.agent-card');

  cards.forEach(card => {
    const loginElement = card.querySelector('.text-lg.font-bold');
    const extenElement = card.querySelector('.fa-hashtag');
    const leadElement = card.querySelector('.fa-user-tie');

    const login = loginElement ? loginElement.textContent.toLowerCase() : '';
    const exten = extenElement ? extenElement.parentNode.textContent.toLowerCase() : '';
    const lead = leadElement ? leadElement.parentNode.textContent.toLowerCase() : '';

    if (login.includes(searchTerm) || exten.includes(searchTerm) || lead.includes(searchTerm)) {
      card.style.display = 'block';
      card.classList.remove('animate-out', 'slide-out-to-bottom-4');
      setTimeout(() => {
        card.classList.add('animate-in', 'slide-in-from-bottom-4');
      }, 100);
    } else {
      card.classList.add('animate-out', 'slide-out-to-bottom-4');
      setTimeout(() => {
        card.style.display = 'none';
      }, 300);
    }
  });
});

// Close modals when clicking outside or pressing Escape
document.addEventListener('click', function(event) {
  if (event.target.id === 'agentModal') {
    closeAgentModal();
  }
});

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeAgentModal();
  }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  // Hide loading overlay
  document.getElementById('loading-overlay').classList.add('hidden');

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
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>

