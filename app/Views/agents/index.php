<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl">
    <div class="animate-spin rounded-full h-12 w-12 border-4 border-rose-500 border-t-transparent"></div>
    <div class="text-lg font-medium text-slate-700 dark:text-slate-300">Agent bilgileri yükleniyor...</div>
  </div>
</div>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl shadow-lg">
              <i class="fa-solid fa-headset text-3xl text-white"></i>
            </div>
            <div>
              <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">Agent Yönetimi</h1>
              <p class="text-lg text-slate-600 dark:text-slate-400 mt-2">Temsilci durumlarını takip edin ve yönetin</p>
            </div>
          </div>

          <?php if ($isSuper): ?>
          <div class="flex gap-4">
            <form method="post" action="/agents/sync" class="inline">
              <button type="submit" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-rose-500 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:shadow-rose-500/25 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-sync-alt"></i>
                Agentleri Güncelle
              </button>
            </form>
          </div>
          <?php endif; ?>
        </div>

        <!-- Stats Cards -->
        <?php
        $totalAgents = 0;
        $onlineAgents = 0;
        $activeAgents = 0;
        $ringingAgents = 0;

        if ($isSuper) {
          foreach (($agentsByGroup ?? []) as $groupName => $groupData) {
            $agents = $groupData['agents'] ?? [];
            $totalAgents += count($agents);
            foreach ($agents as $agent) {
              $status = strtolower($agent['status'] ?? '');
              if ($status === 'online' || $status === 'up') $onlineAgents++;
              if ($status === 'ring') $ringingAgents++;
              if ($agent['active'] ?? 1) $activeAgents++;
            }
          }
        } else {
          $groupKey = key($agentsByGroup ?? []);
          $agents = ($agentsByGroup[$groupKey]['agents'] ?? []) ?: [];
          $totalAgents = count($agents);
          
          foreach ($agents as $agent) {
            $status = strtolower($agent['status'] ?? '');
            if ($status === 'online' || $status === 'up') $onlineAgents++;
            if ($status === 'ring') $ringingAgents++;
            if ($agent['active'] ?? 1) $activeAgents++;
          }
        }
        ?>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-6 border border-emerald-200/50 dark:border-emerald-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-emerald-500 rounded-lg">
                <i class="fa-solid fa-users text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-emerald-800 dark:text-emerald-300"><?php echo $totalAgents; ?></div>
                <div class="text-sm text-emerald-600 dark:text-emerald-400">Toplam Agent</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border border-green-200/50 dark:border-green-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-green-500 rounded-lg">
                <i class="fa-solid fa-circle-check text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-green-800 dark:text-green-300"><?php echo $onlineAgents; ?></div>
                <div class="text-sm text-green-600 dark:text-green-400">Çevrimiçi</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl p-6 border border-amber-200/50 dark:border-amber-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-amber-500 rounded-lg">
                <i class="fa-solid fa-phone text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-amber-800 dark:text-amber-300"><?php echo $ringingAgents; ?></div>
                <div class="text-sm text-amber-600 dark:text-amber-400">Çalıyor</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200/50 dark:border-blue-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-blue-500 rounded-lg">
                <i class="fa-solid fa-toggle-on text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-800 dark:text-blue-300"><?php echo $activeAgents; ?></div>
                <div class="text-sm text-blue-600 dark:text-blue-400">Aktif</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Agents Grid -->
    <?php if ($isSuper): ?>
      <!-- Super Admin View -->
      <?php foreach (($agentsByGroup ?? []) as $groupIndex => $groupData): ?>
        <div class="mb-8">
          <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-3 mb-6">
              <div class="p-2 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl">
                <i class="fa-solid fa-users text-white text-lg"></i>
              </div>
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                <?php echo htmlspecialchars($groupData['groupName'] ?? 'Grup'); ?>
              </h3>
              <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium">
                <?php echo count($groupData['agents'] ?? []); ?> Agent
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <?php foreach (($groupData['agents'] ?? []) as $agentIndex => $a): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
                  <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                          <span class="text-white font-bold">
                            <?php echo strtoupper(substr(htmlspecialchars($a['user_login'] ?? 'A'), 0, 1)); ?>
                          </span>
                        </div>
                        <div>
                          <h4 class="font-bold text-slate-900 dark:text-white">
                            <?php echo htmlspecialchars($a['user_login'] ?? ''); ?>
                          </h4>
                          <p class="text-sm text-slate-600 dark:text-slate-400">
                            #<?php echo htmlspecialchars($a['exten'] ?? ''); ?>
                          </p>
                        </div>
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                          <?php
                          $status = strtolower($a['status'] ?? '');
                          if ($status === 'up' || $status === 'online') echo 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                          elseif ($status === 'ring' || $status === 'ringing') echo 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
                          elseif ($status === 'busy') echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                          else echo 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
                          ?>">
                          <?php
                          if ($status === 'up' || $status === 'online') echo 'Çevrimiçi';
                          elseif ($status === 'ring' || $status === 'ringing') echo 'Çalıyor';
                          elseif ($status === 'busy') echo 'Meşgul';
                          else echo 'Çevrimdışı';
                          ?>
                        </span>
                      </div>
                    </div>

                    <div class="space-y-3 mb-4">
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Durum:</span>
                        <span class="font-medium <?php echo ($a['active'] ?? 1) ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'; ?>">
                          <?php echo ($a['active'] ?? 1) ? 'Aktif' : 'Pasif'; ?>
                        </span>
                      </div>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Son Çağrı:</span>
                        <span class="font-medium"><?php echo htmlspecialchars((string)($a['las_call_time'] ?? '-')); ?></span>
                      </div>
                    </div>

                    <form method="post" action="/agents/toggle-active" class="inline">
                      <input type="hidden" name="exten" value="<?php echo htmlspecialchars($a['exten']); ?>">
                      <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                        <?php echo ($a['active'] ?? 1) ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-emerald-500 hover:bg-emerald-600 text-white'; ?>">
                        <i class="fa-solid fa-<?php echo ($a['active'] ?? 1) ? 'ban' : 'check'; ?> mr-2"></i>
                        <?php echo ($a['active'] ?? 1) ? 'Deaktif Et' : 'Aktif Et'; ?>
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    <?php else: ?>
      <!-- Group Admin View -->
      <?php
      $groupKey = key($agentsByGroup ?? []);
      $groupData = $agentsByGroup[$groupKey] ?? [];
      $agents = $groupData['agents'] ?? [];
      ?>
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center gap-3 mb-6">
          <div class="p-2 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl">
            <i class="fa-solid fa-users text-white text-lg"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">
            <?php echo htmlspecialchars($groupData['groupName'] ?? 'Kendi Grubunuz'); ?>
          </h3>
          <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-sm font-medium">
            <?php echo count($agents); ?> Aktif Agent
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <?php foreach ($agents as $agentIndex => $a): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
              <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-pink-600 rounded-full flex items-center justify-center shadow-lg">
                      <span class="text-white font-bold">
                        <?php echo strtoupper(substr(htmlspecialchars($a['user_login'] ?? 'A'), 0, 1)); ?>
                      </span>
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">
                        <?php echo htmlspecialchars($a['user_login'] ?? ''); ?>
                      </h4>
                      <p class="text-sm text-slate-600 dark:text-slate-400">
                        #<?php echo htmlspecialchars($a['exten'] ?? ''); ?>
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                      <?php
                      $status = strtolower($a['status'] ?? '');
                      if ($status === 'up' || $status === 'online') echo 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                      elseif ($status === 'ring' || $status === 'ringing') echo 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
                      elseif ($status === 'busy') echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                      else echo 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
                      ?>">
                      <?php
                      if ($status === 'up' || $status === 'online') echo 'Çevrimiçi';
                      elseif ($status === 'ring' || $status === 'ringing') echo 'Çalıyor';
                      elseif ($status === 'busy') echo 'Meşgul';
                      else echo 'Çevrimdışı';
                      ?>
                    </span>
                  </div>
                </div>

                <div class="space-y-3 mb-4">
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Durum:</span>
                    <span class="font-medium <?php echo ($a['active'] ?? 1) ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'; ?>">
                      <?php echo ($a['active'] ?? 1) ? 'Aktif' : 'Pasif'; ?>
                    </span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Son Çağrı:</span>
                    <span class="font-medium"><?php echo htmlspecialchars((string)($a['las_call_time'] ?? '-')); ?></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-slate-600 dark:text-slate-400">Lead:</span>
                    <span class="font-medium"><?php echo htmlspecialchars($a['lead'] ?? '-'); ?></span>
                  </div>
                </div>

                <div class="text-center">
                  <span class="inline-flex items-center px-3 py-2 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 rounded-lg text-sm font-medium">
                    <i class="fa-solid fa-check mr-2"></i>Aktif
                  </span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const loadingOverlay = document.getElementById('loading-overlay');
  if (loadingOverlay) {
    loadingOverlay.classList.add('hidden');
  }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>