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
            <form method="post" action="/VoipPanelAi/agents/sync" class="inline">
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
                <?php
                // Agent'ın abonelik durumunu kontrol et
                $userAgents = [];
                if ($isSuper) {
                  $agentExten = $a['exten'] ?? '';
                  $agentLogin = $a['user_login'] ?? '';
                  $agentGroup = $a['group_name'] ?? '';
                  
                  // Çoklu kontrol stratejisi: exten, login, group bazlı arama
                  if ($agentExten || $agentLogin) {
                    $userIds = [];
                    
                    // 1. Önce agent'ın user_login'i ile users tablosundaki kullanıcıyı bul
                    if ($agentLogin) {
                      $stmt = $db->prepare('SELECT u.id as user_id FROM users u WHERE u.login = ?');
                      $stmt->bind_param('s', $agentLogin);
                      $stmt->execute();
                      $userResult = $stmt->get_result()->fetch_assoc();
                      $stmt->close();
                      if ($userResult) {
                        $userIds[] = $userResult['user_id'];
                      }
                    }
                    
                    // 2. Eğer bulunamazsa, exten ile users.exten kolonunda ara
                    if (empty($userIds) && $agentExten) {
                      try {
                        $stmt = $db->prepare('SELECT u.id as user_id FROM users u WHERE u.exten = ?');
                        $stmt->bind_param('s', $agentExten);
                        $stmt->execute();
                        $userResult = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($userResult) {
                          $userIds[] = $userResult['user_id'];
                        }
                      } catch (\Throwable $e) {
                        // exten kolonu yoksa pas geç
                      }
                    }
                    
                    // 3. Eğer hala bulunamazsa, group bazlı arama yap
                    if (empty($userIds) && $agentGroup) {
                      $stmt = $db->prepare('
                        SELECT u.id as user_id FROM users u
                        JOIN groups g ON u.group_id = g.id
                        WHERE g.name = ? OR g.api_group_name = ?
                        ORDER BY u.role = "groupadmin" DESC
                        LIMIT 1
                      ');
                      $stmt->bind_param('ss', $agentGroup, $agentGroup);
                      $stmt->execute();
                      $userResult = $stmt->get_result()->fetch_assoc();
                      $stmt->close();
                      if ($userResult) {
                        $userIds[] = $userResult['user_id'];
                      }
                    }
                    
                    // Bulunan kullanıcıların aktif agent aboneliklerini getir
                    if (!empty($userIds)) {
                      $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                      $stmt = $db->prepare("
                        SELECT ua.*, ap.name as product_name, ap.subscription_monthly_fee, ap.phone_prefix
                        FROM user_agents ua
                        JOIN agent_products ap ON ua.agent_product_id = ap.id
                        WHERE ua.user_id IN ($placeholders) AND ua.status = 'active'
                      ");
                      $stmt->bind_param(str_repeat('i', count($userIds)), ...$userIds);
                      $stmt->execute();
                      $userAgents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                      $stmt->close();
                    }
                  }
                }
                ?>
                <div class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-800 dark:to-slate-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-slate-200/50 dark:border-slate-600/50 overflow-hidden">
                  <div class="p-6">
                    <!-- Agent Header -->
                    <div class="flex items-center justify-between mb-4">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                          <span class="text-white font-bold text-lg">
                            <?php echo strtoupper(substr(htmlspecialchars($a['user_login'] ?? 'A'), 0, 1)); ?>
                          </span>
                        </div>
                        <div>
                          <h4 class="font-bold text-slate-900 dark:text-white text-lg">
                            <?php echo htmlspecialchars($a['user_login'] ?? ''); ?>
                          </h4>
                          <p class="text-sm text-slate-600 dark:text-slate-400">
                            Extension: #<?php echo htmlspecialchars($a['exten'] ?? ''); ?>
                          </p>
                          <?php if (!empty($userAgents)): ?>
                          <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">
                            <?php echo count($userAgents); ?> Aktif Abonelik
                          </p>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="flex flex-col items-end gap-1">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                          <?php
                          $status = strtolower($a['status'] ?? '');
                          if ($status === 'up' || $status === 'online') echo 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                          elseif ($status === 'ring' || $status === 'ringing') echo 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300';
                          elseif ($status === 'busy') echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                          else echo 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
                          ?>">
                          <?php
                          if ($status === 'up' || $status === 'online') echo '🟢 Çevrimiçi';
                          elseif ($status === 'ring' || $status === 'ringing') echo '🟡 Çalıyor';
                          elseif ($status === 'busy') echo '🔴 Meşgul';
                          else echo '⚪ Çevrimdışı';
                          ?>
                        </span>
                      </div>
                    </div>

                    <!-- Agent Details -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 mb-4 space-y-2">
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Sistem Durumu:</span>
                        <span class="font-semibold <?php echo ($a['active'] ?? 1) ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'; ?>">
                          <?php echo ($a['active'] ?? 1) ? '✅ Aktif' : '❌ Pasif'; ?>
                        </span>
                      </div>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Grup:</span>
                        <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($a['group_name'] ?? $groupData['groupName'] ?? '-'); ?></span>
                      </div>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Son Çağrı:</span>
                        <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars((string)($a['las_call_time'] ?? '-')); ?></span>
                      </div>
                      <?php if ($a['lead'] ?? false): ?>
                      <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">Lead:</span>
                        <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($a['lead']); ?></span>
                      </div>
                      <?php endif; ?>
                    </div>

                    <!-- Abonelikler -->
                    <?php if (!empty($userAgents)): ?>
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl p-3 mb-4">
                      <h5 class="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-2">
                        <i class="fa-solid fa-crown mr-1"></i>Aktif Abonelikler
                      </h5>
                      <?php foreach ($userAgents as $userAgent): ?>
                      <div class="flex justify-between items-center text-xs mb-1 last:mb-0">
                        <span class="text-purple-700 dark:text-purple-400"><?php echo htmlspecialchars($userAgent['product_name']); ?></span>
                        <div class="flex items-center gap-2">
                          <span class="text-purple-800 dark:text-purple-300 font-medium">#<?php echo htmlspecialchars($userAgent['agent_number']); ?></span>
                          <?php if ($isSuper): ?>
                          <form method="post" action="/VoipPanelAi/agents/remove-subscription" class="inline">
                            <input type="hidden" name="user_agent_id" value="<?php echo $userAgent['id']; ?>">
                            <button type="submit" onclick="return confirm('Bu aboneliği iptal etmek istediğinizden emin misiniz?')"
                                    class="text-red-500 hover:text-red-700 text-xs">
                              <i class="fa-solid fa-times"></i>
                            </button>
                          </form>
                          <?php endif; ?>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <?php if ($isSuper): ?>
                    <div class="grid grid-cols-1 gap-2">
                      <!-- Toggle Active/Inactive -->
                      <form method="post" action="/VoipPanelAi/agents/toggle-active" class="inline">
                        <input type="hidden" name="exten" value="<?php echo htmlspecialchars($a['exten']); ?>">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 transform hover:scale-105
                          <?php echo ($a['active'] ?? 1) ? 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white shadow-lg' : 'bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white shadow-lg'; ?>">
                          <i class="fa-solid fa-<?php echo ($a['active'] ?? 1) ? 'ban' : 'check'; ?> mr-2"></i>
                          <?php echo ($a['active'] ?? 1) ? 'Deaktif Et' : 'Aktif Et'; ?>
                        </button>
                      </form>

                      <!-- Edit Name Button -->
                      <button onclick="openEditNameModal('<?php echo htmlspecialchars($a['exten']); ?>', '<?php echo htmlspecialchars($a['user_login'] ?? ''); ?>')"
                              class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fa-solid fa-edit mr-2"></i>
                        Adını Değiştir
                      </button>

                      <!-- Add/Edit Subscription Button -->
                      <?php if (!empty($userAgents)): ?>
                      <button onclick="openEditSubscriptionModal('<?php echo htmlspecialchars($a['exten']); ?>', '<?php echo htmlspecialchars($a['user_login'] ?? ''); ?>', <?php echo htmlspecialchars(json_encode($userAgents)); ?>)"
                              class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fa-solid fa-edit mr-2"></i>
                        Abonelik Düzenle
                      </button>
                      <?php else: ?>
                      <button onclick="openAddSubscriptionModal('<?php echo htmlspecialchars($a['exten']); ?>', '<?php echo htmlspecialchars($a['user_login'] ?? ''); ?>')"
                              class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-semibold rounded-xl shadow-lg transition-all duration-200 transform hover:scale-105">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Abonelik Ekle
                      </button>
                      <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center">
                      <span class="inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 rounded-xl text-sm font-semibold">
                        <i class="fa-solid fa-check mr-2"></i>Aktif Agent
                      </span>
                    </div>
                    <?php endif; ?>
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

<!-- Agent Adı Değiştirme Modali -->
<div id="editNameModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-edit mr-2 text-blue-600"></i>Agent Adını Değiştir
        </h3>
        <button onclick="closeEditNameModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>
      
      <form id="editNameForm" method="post" action="/VoipPanelAi/agents/update-agent-name">
        <input type="hidden" id="editNameExten" name="exten" value="">
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Mevcut Ad:
          </label>
          <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
            <span id="currentAgentName" class="text-slate-900 dark:text-white font-medium"></span>
          </div>
        </div>
        
        <div class="mb-6">
          <label for="newAgentName" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Yeni Ad:
          </label>
          <input type="text" id="newAgentName" name="new_name" required
                 class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                 placeholder="Yeni agent adını girin">
        </div>
        
        <div class="flex gap-3">
          <button type="button" onclick="closeEditNameModal()"
                  class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            İptal
          </button>
          <button type="submit"
                  class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-colors font-semibold">
            <i class="fa-solid fa-save mr-2"></i>Kaydet
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Abonelik Ekleme Modali -->
<div id="addSubscriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-plus mr-2 text-purple-600"></i>Abonelik Ekle
        </h3>
        <button onclick="closeAddSubscriptionModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>
      
      <form id="addSubscriptionForm" method="post" action="/VoipPanelAi/agents/add-subscription">
        <input type="hidden" id="subscriptionExten" name="agent_exten" value="">
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Agent:
          </label>
          <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
            <span id="subscriptionAgentName" class="text-slate-900 dark:text-white font-medium"></span>
            <span class="text-slate-500 dark:text-slate-400 ml-2">- Extension: #<span id="subscriptionAgentExten"></span></span>
          </div>
        </div>
<div class="mb-4">
  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
    Abonelik Sahibi:
  </label>
  <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
    <span class="text-slate-900 dark:text-white font-medium">
      Agent'ın grubunun yöneticisine otomatik atanacak
    </span>
    <span class="text-slate-500 dark:text-slate-400 text-sm block mt-1">
      Grup: <span id="subscriptionAgentGroup" class="font-medium">-</span>
    </span>
  </div>
</div>


        <div class="mb-4">
          <label for="agentProductId" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Agent Ürünü Seçin:
          </label>
          <select id="agentProductId" name="agent_product_id" required
                  class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            <option value="">Ürün Seçin...</option>
            <?php
            // Agent ürünlerini getir
            $stmt = $db->prepare('SELECT id, name, price, subscription_monthly_fee FROM agent_products WHERE is_active = 1 ORDER BY name');
            $stmt->execute();
            $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            foreach ($products as $product):
            ?>
            <option value="<?php echo $product['id']; ?>"
                    data-price="<?php echo $product['price']; ?>"
                    data-monthly="<?php echo $product['subscription_monthly_fee'] ?? 0; ?>">
              <?php echo htmlspecialchars($product['name']); ?>
              - Fiyat: $<?php echo number_format($product['price'], 2); ?>
              <?php if ($product['subscription_monthly_fee'] > 0): ?>
                (Aylık: $<?php echo number_format($product['subscription_monthly_fee'], 2); ?>)
              <?php endif; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-4">
          <label for="agentNumber" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Agent Numarası:
          </label>
          <input type="text" id="agentNumber" name="agent_number" required
                 class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                 placeholder="Örn: 05551234567">
        </div>

        <!-- Başlangıç Tarihi -->
        <div class="mb-4">
          <label for="subscriptionStartDate" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            <i class="fa-solid fa-calendar mr-1"></i>Abonelik Başlangıç Tarihi:
          </label>
          <input type="date" id="subscriptionStartDate" name="subscription_start_date"
                 class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                 value="<?php echo date('Y-m-d'); ?>">
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Boş bırakılırsa bugünden itibaren başlar
          </p>
        </div>

        <!-- Ödeme Durumu -->
        <div class="mb-4">
          <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-lg p-4">
            <label class="flex items-center cursor-pointer">
              <input type="checkbox" id="subscriptionPaid" name="subscription_paid" class="sr-only">
              <div class="relative">
                <input type="checkbox" id="subscriptionPaidCheckbox" name="subscription_paid"
                       class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-emerald-600 dark:ring-offset-gray-800">
              </div>
              <div class="ml-3">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                  <i class="fa-solid fa-credit-card mr-1 text-emerald-600"></i>
                  Ödeme yapıldı / Manuel olarak işaretle
                </span>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  ✅ <strong>İşaretli:</strong> Ödeme yapıldı olarak kaydedilir<br>
                  ⚠️ <strong>İşaretsiz:</strong> Otomatik bakiyeden düşer
                </p>
              </div>
            </label>
          </div>
        </div>

        <div id="priceInfo" class="mb-4 p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg hidden">
          <h4 class="font-semibold text-purple-800 dark:text-purple-300 mb-2">Fiyat Bilgileri:</h4>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-purple-700 dark:text-purple-400">Kurulum Ücreti:</span>
              <span id="setupPrice" class="font-semibold text-purple-800 dark:text-purple-300"></span>
            </div>
            <div id="monthlyFeeInfo" class="flex justify-between hidden">
              <span class="text-purple-700 dark:text-purple-400">Aylık Abonelik:</span>
              <span id="monthlyPrice" class="font-semibold text-purple-800 dark:text-purple-300"></span>
            </div>
          </div>
        </div>
        
        <div class="flex gap-3">
          <button type="button" onclick="closeAddSubscriptionModal()"
                  class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            İptal
          </button>
          <button type="submit"
                  class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition-colors font-semibold">
            <i class="fa-solid fa-plus mr-2"></i>Abonelik Ekle
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Abonelik Düzenleme Modali -->
<div id="editSubscriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-edit mr-2 text-amber-600"></i>Abonelik Düzenle
        </h3>
        <button onclick="closeEditSubscriptionModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>
      
      <form id="editSubscriptionForm" method="post" action="/VoipPanelAi/agents/update-subscription">
        <input type="hidden" id="editSubscriptionId" name="user_agent_id" value="">
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Agent:
          </label>
          <div class="p-3 bg-slate-100 dark:bg-slate-700 rounded-lg">
            <span id="editSubscriptionAgentName" class="text-slate-900 dark:text-white font-medium"></span>
            <span class="text-slate-500 dark:text-slate-400 ml-2">- Extension: #<span id="editSubscriptionAgentExten"></span></span>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            Mevcut Abonelik:
          </label>
          <div class="p-3 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg">
            <div id="currentSubscriptionInfo">
              <!-- Burada mevcut abonelik bilgileri gösterilecek -->
            </div>
          </div>
        </div>

        <!-- Abonelik Başlangıç Tarihi -->
        <div class="mb-4">
          <label for="editSubscriptionStartDate" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            <i class="fa-solid fa-calendar-start mr-1"></i>Abonelik Başlangıç Tarihi:
          </label>
          <input type="date" id="editSubscriptionStartDate" name="subscription_start_date"
                 class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        <!-- Sonraki Ödeme Tarihi -->
        <div class="mb-4">
          <label for="editNextPaymentDate" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            <i class="fa-solid fa-calendar-check mr-1"></i>Sonraki Ödeme Tarihi:
          </label>
          <input type="date" id="editNextPaymentDate" name="next_payment_date"
                 class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Bu tarihte otomatik ödeme alınacak
          </p>
        </div>

        <!-- Abonelik Durumu -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
            <i class="fa-solid fa-toggle-on mr-1"></i>Abonelik Durumu:
          </label>
          <select id="editSubscriptionStatus" name="subscription_status"
                  class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            <option value="active">🟢 Aktif</option>
            <option value="suspended">🟡 Askıya Alınmış</option>
            <option value="cancelled">🔴 İptal Edilmiş</option>
          </select>
        </div>

        <!-- Manuel Ödeme Seçeneği -->
        <div class="mb-6">
          <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 rounded-lg p-4">
            <label class="flex items-center cursor-pointer">
              <input type="checkbox" id="editManualPayment" name="mark_paid"
                     class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-emerald-600 dark:ring-offset-gray-800">
              <div class="ml-3">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                  <i class="fa-solid fa-hand-holding-dollar mr-1 text-emerald-600"></i>
                  Sonraki ödemeyi manuel ödendi işaretle
                </span>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  Sonraki ödeme tarihi 1 ay ileri alınır
                </p>
              </div>
            </label>
          </div>
        </div>
        
        <div class="flex gap-3">
          <button type="button" onclick="closeEditSubscriptionModal()"
                  class="flex-1 px-4 py-3 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            İptal
          </button>
          <button type="submit"
                  class="flex-1 px-4 py-3 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-lg hover:from-amber-700 hover:to-amber-800 transition-colors font-semibold">
            <i class="fa-solid fa-save mr-2"></i>Güncelle
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Agent ad değiştirme modal fonksiyonları
function openEditNameModal(exten, currentName) {
  document.getElementById('editNameExten').value = exten;
  document.getElementById('currentAgentName').textContent = currentName;
  document.getElementById('newAgentName').value = currentName;
  document.getElementById('editNameModal').classList.remove('hidden');
  document.getElementById('editNameModal').classList.add('flex');
  document.getElementById('newAgentName').focus();
}

function closeEditNameModal() {
  document.getElementById('editNameModal').classList.add('hidden');
  document.getElementById('editNameModal').classList.remove('flex');
  document.getElementById('editNameForm').reset();
}

// Abonelik ekleme modal fonksiyonları
function openAddSubscriptionModal(exten, agentName) {
  document.getElementById('subscriptionExten').value = exten;
  document.getElementById('subscriptionAgentName').textContent = agentName;
  document.getElementById('subscriptionAgentExten').textContent = exten;
  
  // Agent kartından grup bilgisini al
  const agentCard = event.target.closest('.bg-gradient-to-br');
  if (agentCard) {
    const groupSpan = agentCard.querySelector('[class*="text-slate-900 dark:text-white"]:not([class*="font-bold"])');
    const groupName = groupSpan ? groupSpan.textContent : 'Bilinmeyen';
    document.getElementById('subscriptionAgentGroup').textContent = groupName;
  }
  
  document.getElementById('addSubscriptionModal').classList.remove('hidden');
  document.getElementById('addSubscriptionModal').classList.add('flex');
}

function closeAddSubscriptionModal() {
  document.getElementById('addSubscriptionModal').classList.add('hidden');
  document.getElementById('addSubscriptionModal').classList.remove('flex');
  document.getElementById('addSubscriptionForm').reset();
  document.getElementById('priceInfo').classList.add('hidden');
}

// Abonelik düzenleme modal fonksiyonları
function openEditSubscriptionModal(exten, agentName, userAgents) {
  document.getElementById('editSubscriptionAgentName').textContent = agentName;
  document.getElementById('editSubscriptionAgentExten').textContent = exten;
  
  // İlk abonelik bilgilerini göster
  if (userAgents && userAgents.length > 0) {
    const subscription = userAgents[0]; // İlk aboneliği al
    document.getElementById('editSubscriptionId').value = subscription.id;
    
    // Mevcut abonelik bilgilerini göster
    const subscriptionInfo = document.getElementById('currentSubscriptionInfo');
    let infoHTML = `
      <div class="space-y-2">
        <div class="flex justify-between">
          <span class="text-purple-700 dark:text-purple-400">Ürün:</span>
          <span class="font-medium text-purple-800 dark:text-purple-300">${subscription.product_name}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-purple-700 dark:text-purple-400">Agent Numarası:</span>
          <span class="font-medium text-purple-800 dark:text-purple-300">#${subscription.agent_number}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-purple-700 dark:text-purple-400">Aylık Ücret:</span>
          <span class="font-medium text-purple-800 dark:text-purple-300">$${parseFloat(subscription.subscription_monthly_fee || 0).toFixed(2)}</span>
        </div>
      </div>
    `;
    subscriptionInfo.innerHTML = infoHTML;
    
    // Form alanlarını doldur
    if (subscription.created_at) {
      const startDate = new Date(subscription.created_at);
      document.getElementById('editSubscriptionStartDate').value = startDate.toISOString().split('T')[0];
    }
    
    if (subscription.next_subscription_due) {
      const nextDate = new Date(subscription.next_subscription_due);
      document.getElementById('editNextPaymentDate').value = nextDate.toISOString().split('T')[0];
    }
    
    document.getElementById('editSubscriptionStatus').value = subscription.status || 'active';
  }
  
  document.getElementById('editSubscriptionModal').classList.remove('hidden');
  document.getElementById('editSubscriptionModal').classList.add('flex');
}

function closeEditSubscriptionModal() {
  document.getElementById('editSubscriptionModal').classList.add('hidden');
  document.getElementById('editSubscriptionModal').classList.remove('flex');
  document.getElementById('editSubscriptionForm').reset();
}

// Ürün seçildiğinde fiyat bilgilerini göster
document.getElementById('agentProductId').addEventListener('change', function() {
  const selectedOption = this.options[this.selectedIndex];
  const priceInfo = document.getElementById('priceInfo');
  const setupPrice = document.getElementById('setupPrice');
  const monthlyPrice = document.getElementById('monthlyPrice');
  const monthlyFeeInfo = document.getElementById('monthlyFeeInfo');
  
  if (selectedOption.value) {
    const price = parseFloat(selectedOption.dataset.price);
    const monthly = parseFloat(selectedOption.dataset.monthly || 0);
    
    setupPrice.textContent = '$' + price.toFixed(2);
    
    if (monthly > 0) {
      monthlyPrice.textContent = '$' + monthly.toFixed(2);
      monthlyFeeInfo.classList.remove('hidden');
    } else {
      monthlyFeeInfo.classList.add('hidden');
    }
    
    priceInfo.classList.remove('hidden');
  } else {
    priceInfo.classList.add('hidden');
  }
});

// Modal dışında tıklandığında kapat
document.getElementById('editNameModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditNameModal();
  }
});

document.getElementById('addSubscriptionModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeAddSubscriptionModal();
  }
});

document.getElementById('editSubscriptionModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditSubscriptionModal();
  }
});

// Escape tuşu ile modal kapatma
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeEditNameModal();
    closeAddSubscriptionModal();
    closeEditSubscriptionModal();
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const loadingOverlay = document.getElementById('loading-overlay');
  if (loadingOverlay) {
    loadingOverlay.classList.add('hidden');
  }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>