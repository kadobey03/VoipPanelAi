<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>

<div class="animate-in slide-in-from-left-5 duration-500">
  <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 mb-8 text-white">
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
            <form method="post" action="/agents/sync" style="display:inline;">
              <button type="submit" class="px-4 py-2 bg-white/20 rounded">Agentleri Güncelle</button>
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

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $totalAgents; ?></div>
              <div class="text-sm opacity-80">Toplam Agent</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $onlineAgents; ?></div>
              <div class="text-sm opacity-80">Çevrimiçi</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $ringingAgents; ?></div>
              <div class="text-sm opacity-80">Çalıyor</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $activeAgents; ?></div>
              <div class="text-sm opacity-80">Aktif</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php if (!empty($error)): ?>
<div class="mb-6 p-4 bg-red-100 border">
  <span><?php echo htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php if ($isSuper): ?>
    <?php foreach (($agentsByGroup ?? []) as $groupIndex => $groupData): ?>
      <div class="mb-8">
        <h3><?php echo htmlspecialchars($groupData['groupName'] ?? 'Grup'); ?></h3>
        <?php foreach (($groupData['agents'] ?? []) as $agentIndex => $a): ?>
        <div class="bg-white p-4 m-2 border rounded shadow">
          <div><strong>Exten:</strong> <?php echo htmlspecialchars($a['exten'] ?? ''); ?></div>
          <div><strong>Login:</strong> <?php echo htmlspecialchars($a['user_login'] ?? ''); ?></div>
          <div><strong>Status:</strong> <?php echo htmlspecialchars($a['status'] ?? ''); ?></div>
          <div><strong>Active:</strong> <?php echo htmlspecialchars($a['active'] ?? ''); ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <?php
    $agents = $agentsByGroup[key($agentsByGroup ?? [])]['agents'] ?? [];
    foreach ($agents as $agentIndex => $a):
    ?>
    <div class="bg-white p-4 m-2 border rounded shadow">
      <div><strong>Exten:</strong> <?php echo htmlspecialchars($a['exten'] ?? ''); ?></div>
      <div><strong>Login:</strong> <?php echo htmlspecialchars($a['user_login'] ?? ''); ?></div>
      <div><strong>Status:</strong> <?php echo htmlspecialchars($a['status'] ?? ''); ?></div>
      <div><strong>Active:</strong> <?php echo htmlspecialchars($a['active'] ?? ''); ?></div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

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
  const loadingOverlay = document.getElementById('loading-overlay');
  if (loadingOverlay) {
    loadingOverlay.classList.add('hidden');
  }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>