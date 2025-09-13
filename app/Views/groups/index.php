<?php
$title='Gruplar - PapaM VoIP Panel';
require dirname(__DIR__).'/partials/header.php';
$isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'superadmin';
?>

<!-- Hero Section -->
<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 mb-8 text-white">
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
            <i class="fa-solid fa-layer-group text-4xl"></i>
          </div>
          <div>
            <h1 class="text-4xl lg:text-5xl font-bold">Grup Yönetimi</h1>
            <p class="text-xl text-white/80 mt-2">Sistem gruplarını görüntüleyin ve yönetin</p>
          </div>
        </div>

        <?php if ($isSuper): ?>
        <a href="<?= \App\Helpers\Url::to('/groups/create') ?>" class="group relative inline-flex items-center gap-3 px-6 py-4 bg-white/20 backdrop-blur-sm rounded-2xl hover:bg-white/30 transition-all duration-300 transform hover:scale-105">
          <div class="p-2 bg-white/30 rounded-lg group-hover:bg-white/40 transition-colors duration-300">
            <i class="fa-solid fa-plus text-lg"></i>
          </div>
          <span class="font-semibold">Yeni Grup</span>
        </a>
        <?php endif; ?>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <?php
        $totalGroups = count($groups);
        $totalBalance = array_sum(array_column($groups, 'balance'));
        $activeGroups = count(array_filter($groups, fn($g) => !empty($g['api_group_name'])));
        ?>
        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/30 rounded-xl">
              <i class="fa-solid fa-layer-group text-2xl"></i>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $totalGroups; ?></div>
              <div class="text-sm opacity-80">Toplam Grup</div>
            </div>
          </div>
        </div>

        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/30 rounded-xl">
              <i class="fa-solid fa-wallet text-2xl"></i>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold">$<?php echo number_format($totalBalance, 2); ?></div>
              <div class="text-sm opacity-80">Toplam Bakiye</div>
            </div>
          </div>
        </div>

        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 bg-white/30 rounded-xl">
              <i class="fa-solid fa-link text-2xl"></i>
            </div>
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $activeGroups; ?></div>
              <div class="text-sm opacity-80">API Bağlantılı</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Decorative Elements -->
  <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
</section>

<!-- Groups Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
  <?php foreach ($groups as $index => $g): ?>
  <div class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-2 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
    <!-- Header -->
    <div class="p-6 pb-4">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center space-x-3">
          <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg">
            <i class="fa-solid fa-layer-group text-white text-xl"></i>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">
              <?= htmlspecialchars($g['name']) ?>
            </h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 font-mono">
              #<?= (int)$g['id'] ?>
            </p>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="flex items-center space-x-2">
          <?php if (!empty($g['api_group_name'])): ?>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
            <i class="fa-solid fa-circle text-emerald-500 mr-1 text-xs"></i>API Bağlı
          </span>
          <?php else: ?>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300">
            <i class="fa-solid fa-circle text-slate-400 mr-1 text-xs"></i>Bağlı Değil
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Balance Card -->
      <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl p-4 mb-4 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <i class="fa-solid fa-dollar-sign text-emerald-200"></i>
            <span class="text-sm opacity-80">Bakiye</span>
          </div>
          <div class="text-2xl font-bold">
            $<?= number_format((float)$g['balance'], 2) ?>
          </div>
        </div>
      </div>

      <!-- Additional Info -->
      <div class="grid grid-cols-1 gap-4 mb-4">
        <div class="text-center">
          <div class="text-sm text-slate-600 dark:text-slate-400 mb-1">API Grup</div>
          <div class="font-medium text-sm text-slate-800 dark:text-white truncate">
            <?php if (!empty($g['api_group_name'])): ?>
              <?= htmlspecialchars($g['api_group_name']) ?>
            <?php else: ?>
              <span class="text-slate-400">Eşleşmedi</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
        <div class="flex space-x-2">
          <button onclick="showGroupDetails(<?= $index ?>)"
                  class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900/70 transition-colors duration-200">
            <i class="fa-solid fa-eye mr-1"></i>Detay
          </button>

          <?php if ($isSuper): ?>
          <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>"
             class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900/70 transition-colors duration-200">
            <i class="fa-solid fa-edit mr-1"></i>Düzenle
          </a>
          <?php endif; ?>
        </div>

        <?php if ($isSuper): ?>
        <button onclick="openTopupModal(<?= (int)$g['id'] ?>, '<?= htmlspecialchars($g['name']) ?>', <?= (float)$g['balance'] ?>)"
                class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition-colors duration-200">
          <i class="fa-solid fa-plus mr-1"></i>Yükle
        </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hover Effect Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Group Details Modal -->
<div id="groupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-layer-group mr-2 text-indigo-500"></i>Grup Detayları
        </h3>
        <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <div id="groupModalContent" class="p-6">
        <!-- Modal content will be populated by JavaScript -->
      </div>
    </div>
  </div>
</div>

<!-- Topup Modal -->
<?php if ($isSuper): ?>
<div id="topupModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
<?php endif; ?>
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <?php if ($isSuper): ?>
          <i class="fa-solid fa-plus mr-2 text-emerald-500"></i>Bakiye Yükle
          <?php endif; ?>
        </h3>
        <button onclick="closeTopupModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <form id="topupForm" method="POST" action="" class="p-6">
        <div class="space-y-4">
          <div class="text-center mb-6">
            <h4 id="topupGroupName" class="text-lg font-semibold text-slate-800 dark:text-white"></h4>
            <p class="text-sm text-slate-600 dark:text-slate-400">
              Mevcut Bakiye: $<span id="currentBalance"></span>
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <i class="fa-solid fa-dollar-sign mr-2 text-emerald-500"></i>Yüklenecek Tutar
            </label>
            <input type="number" name="amount" step="0.01" min="0.01" required
                   class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200"
                   placeholder="0.00">
          </div>

          <?php if ($isSuper): ?>
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              <i class="fa-solid fa-credit-card mr-2 text-blue-500"></i>Yükleme Yöntemi
            </label>
            <select name="method" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
              <option value="manual">Manuel Yükleme</option>
              <option value="bank">Banka Transferi</option>
              <option value="credit">Kredi Kartı</option>
              <option value="crypto">Kripto Para</option>
            </select>
          </div>
          <?php endif; ?>
          <input type="hidden" name="method" value="manual">
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
          <button type="button" onclick="closeTopupModal()" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 transition-colors duration-200">
            İptal
          </button>
          <button type="submit" class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors duration-200">
            <?php if ($isSuper): ?>
            <i class="fa-solid fa-plus mr-1"></i>Yükle
            <?php endif; ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Store groups data for modal
const groupsData = <?php echo json_encode($groups); ?>;

// Modal functions
function showGroupDetails(index) {
  const group = groupsData[index];
  if (!group) return;

  const modalContent = document.getElementById('groupModalContent');
  modalContent.innerHTML = `
    <div class="space-y-6">
      <!-- Group Header -->
      <div class="flex items-center space-x-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
          <span class="text-white font-bold text-xl">
            ${group.name.charAt(0).toUpperCase()}
          </span>
        </div>
        <div>
          <h4 class="text-xl font-bold text-slate-900 dark:text-white">${group.name}</h4>
          <p class="text-slate-600 dark:text-slate-400">Grup ID: #${group.id}</p>
        </div>
      </div>

      <!-- Group Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Grup Bilgileri</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Grup Adı:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${group.name}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Grup ID:</span>
              <span class="font-mono font-semibold text-indigo-600 dark:text-indigo-400">#${group.id}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Bakiye:</span>
              <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">$${parseFloat(group.balance).toFixed(2)}</span>
            </div>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">API Bağlantısı</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">API Grup:</span>
              <span class="font-semibold ${group.api_group_name ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400'}">
                ${group.api_group_name || 'Eşleşmedi'}
              </span>
            </div>
            ${group.api_group_id ? `
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">API ID:</span>
              <span class="font-mono font-semibold text-blue-600 dark:text-blue-400">#${group.api_group_id}</span>
            </div>
            ` : ''}
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Durum:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${group.api_group_name ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300'}">
                <i class="fa-solid fa-circle mr-1 text-xs"></i>${group.api_group_name ? 'Bağlı' : 'Bağlı Değil'}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
        <a href="<?= \App\Helpers\Url::to('/groups/show') ?>?id=${group.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-eye mr-2"></i>Detaylı Görüntüle
        </a>
        <?php if ($isSuper): ?>
        <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=${group.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-edit mr-2"></i>Grubu Düzenle
        </a>
        <?php endif; ?>
      </div>
    </div>
  `;

  document.getElementById('groupModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('groupModal').classList.add('hidden');
}

function openTopupModal(groupId, groupName, currentBalance) {
  <?php if ($isSuper): ?>
  document.getElementById('topupGroupName').textContent = groupName;
  document.getElementById('currentBalance').textContent = currentBalance.toFixed(2);
  document.getElementById('topupForm').action = `<?= \App\Helpers\Url::to('/groups/topup') ?>?id=${groupId}`;
  document.getElementById('topupModal').classList.remove('hidden');
  <?php endif; ?>
}

function closeTopupModal() {
  <?php if ($isSuper): ?>
  document.getElementById('topupModal').classList.add('hidden');
  <?php endif; ?>
}

// Close modals when clicking outside or pressing Escape
document.addEventListener('click', function(event) {
  if (event.target.id === 'groupModal') {
    closeModal();
  }
  <?php if ($isSuper): ?>
  if (event.target.id === 'topupModal') {
    closeTopupModal();
  }
  <?php endif; ?>
});

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeModal();
    <?php if ($isSuper): ?>
    closeTopupModal();
    <?php endif; ?>
  }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>
