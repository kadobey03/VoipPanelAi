<?php
$title='Kullanıcılar - PapaM VoIP Panel';
require dirname(__DIR__).'/partials/header.php';
$isSuperAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'superadmin';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
  <div class="flex items-center gap-3">
    <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl shadow-lg">
      <i class="fa-solid fa-users text-white text-xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Kullanıcı Yönetimi</h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">Sistem kullanıcılarını görüntüleyin ve yönetin</p>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="flex gap-3">
    <button onclick="exportUsers()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-200">
      <i class="fa-solid fa-download"></i>
      <span class="hidden sm:inline">Excel İndir</span>
    </button>

    <a href="<?= \App\Helpers\Url::to('/users/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transform hover:scale-105 transition-all duration-200">
      <i class="fa-solid fa-user-plus"></i>
      <span class="hidden sm:inline">Yeni Kullanıcı</span>
    </a>
  </div>
</div>

<!-- Search & Filter Section -->
<div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6 mb-6">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-magnifying-glass mr-2 text-indigo-500"></i>Ara
      </label>
      <input type="text" id="searchInput" placeholder="Kullanıcı adı, exten..."
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-shield mr-2 text-emerald-500"></i>Rol Filtresi
      </label>
      <select id="roleFilter" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
        <option value="">Tümü</option>
        <option value="superadmin">Super Admin</option>
        <option value="groupadmin">Grup Admin</option>
        <option value="user">Kullanıcı</option>
        <option value="groupmember">Grup Üyesi</option>
      </select>
    </div>

    <?php if ($isSuperAdmin): ?>
    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-users mr-2 text-blue-500"></i>Grup Filtresi
      </label>
      <select id="groupFilter" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
        <option value="">Tümü</option>
        <?php
        $groups = [];
        $res = DB::conn()->query('SELECT id, name FROM groups ORDER BY name');
        while ($row = $res->fetch_assoc()) {
          $groups[] = $row;
          echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
        }
        ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="flex items-end sm:col-span-2 lg:col-span-1">
      <button onclick="clearFilters()" class="w-full px-4 py-3 bg-gradient-to-r from-slate-500 to-slate-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-slate-500/25 transform hover:scale-105 transition-all duration-200">
        <i class="fa-solid fa-filter-circle-x mr-2"></i>Temizle
      </button>
    </div>
  </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
  <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-indigo-100 text-sm font-medium">Toplam Kullanıcı</p>
        <p class="text-2xl font-bold" id="totalUsers"><?php echo count($users); ?></p>
      </div>
      <div class="p-3 bg-indigo-400/30 rounded-xl">
        <i class="fa-solid fa-users text-2xl"></i>
      </div>
    </div>
  </div>

  <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-emerald-100 text-sm font-medium">Aktif Kullanıcı</p>
        <p class="text-2xl font-bold" id="activeUsers"><?php echo count(array_filter($users, fn($u) => $u['role'] !== 'inactive')); ?></p>
      </div>
      <div class="p-3 bg-emerald-400/30 rounded-xl">
        <i class="fa-solid fa-user-check text-2xl"></i>
      </div>
    </div>
  </div>

  <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-purple-100 text-sm font-medium">Grup Üyeleri</p>
        <p class="text-2xl font-bold" id="groupMembers"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'groupmember')); ?></p>
      </div>
      <div class="p-3 bg-purple-400/30 rounded-xl">
        <i class="fa-solid fa-user-group text-2xl"></i>
      </div>
    </div>
  </div>

  <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-orange-100 text-sm font-medium">Adminler</p>
        <p class="text-2xl font-bold" id="admins"><?php echo count(array_filter($users, fn($u) => in_array($u['role'], ['superadmin', 'groupadmin']))); ?></p>
      </div>
      <div class="p-3 bg-orange-400/30 rounded-xl">
        <i class="fa-solid fa-user-shield text-2xl"></i>
      </div>
    </div>
  </div>
</div>

<!-- Users Table -->
<div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
      <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/50 dark:to-slate-800/50">
        <tr>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-hashtag mr-2 text-indigo-500"></i>ID
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-user mr-2 text-emerald-500"></i>Kullanıcı
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-shield mr-2 text-purple-500"></i>Rol
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-phone mr-2 text-blue-500"></i>Exten
          </th>
          <?php if ($isSuperAdmin): ?>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-users mr-2 text-orange-500"></i>Grup
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-robot mr-2 text-pink-500"></i>Agent ID
          </th>
          <?php endif; ?>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-cogs mr-2 text-gray-500"></i>İşlemler
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700" id="usersTableBody">
        <?php foreach ($users as $index => $u): ?>
        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-all duration-200 user-row" data-role="<?= htmlspecialchars($u['role']) ?>" data-group="<?= htmlspecialchars($u['group_id'] ?? '') ?>" data-login="<?= htmlspecialchars($u['login']) ?>" data-exten="<?= htmlspecialchars($u['exten'] ?? '') ?>">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-indigo-600 dark:text-indigo-400 font-bold">
            #<?= (int)$u['id'] ?>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-<?= getRoleColor($u['role']) ?>-400 to-<?= getRoleColor($u['role']) ?>-600 flex items-center justify-center">
                  <span class="text-white font-semibold text-sm">
                    <?= strtoupper(substr($u['login'], 0, 2)) ?>
                  </span>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-slate-900 dark:text-white">
                  <?= htmlspecialchars($u['login']) ?>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                  ID: <?= (int)$u['id'] ?>
                </div>
              </div>
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
              <?php
              switch($u['role']) {
                case 'superadmin':
                  echo 'bg-gradient-to-r from-fuchsia-500 to-fuchsia-600 text-white';
                  break;
                case 'groupadmin':
                  echo 'bg-gradient-to-r from-blue-500 to-blue-600 text-white';
                  break;
                case 'user':
                  echo 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white';
                  break;
                case 'groupmember':
                  echo 'bg-gradient-to-r from-purple-500 to-purple-600 text-white';
                  break;
                default:
                  echo 'bg-gradient-to-r from-slate-500 to-slate-600 text-white';
              }
              ?>">
              <i class="fa-solid fa-shield mr-1 text-xs"></i>
              <?= htmlspecialchars(ucfirst($u['role'])) ?>
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-900 dark:text-white">
            <?php if (!empty($u['exten'])): ?>
              <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                <i class="fa-solid fa-phone mr-1 text-xs"></i>
                <?= htmlspecialchars($u['exten']) ?>
              </span>
            <?php else: ?>
              <span class="text-slate-400 dark:text-slate-600">-</span>
            <?php endif; ?>
          </td>
          <?php if ($isSuperAdmin): ?>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
            <?php
            $groupName = 'N/A';
            if (!empty($u['group_id'])) {
              foreach ($groups as $g) {
                if ($g['id'] == $u['group_id']) {
                  $groupName = $g['name'];
                  break;
                }
              }
            }
            ?>
            <span class="inline-flex items-center px-2 py-1 rounded-md bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
              <i class="fa-solid fa-users mr-1 text-xs"></i>
              <?= htmlspecialchars($groupName) ?>
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-900 dark:text-white">
            <?php if (!empty($u['agent_id'])): ?>
              <span class="inline-flex items-center px-2 py-1 rounded-md bg-pink-100 text-pink-800 dark:bg-pink-900/50 dark:text-pink-300">
                <i class="fa-solid fa-robot mr-1 text-xs"></i>
                <?= (int)$u['agent_id'] ?>
              </span>
            <?php else: ?>
              <span class="text-slate-400 dark:text-slate-600">-</span>
            <?php endif; ?>
          </td>
          <?php endif; ?>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="flex items-center space-x-2">
              <button onclick="showUserDetails(<?= $index ?>)"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900/70 transition-colors duration-200">
                <i class="fa-solid fa-eye mr-1"></i>Detay
              </button>

              <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=<?= (int)$u['id'] ?>"
                 class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900/70 transition-colors duration-200">
                <i class="fa-solid fa-edit mr-1"></i>Düzenle
              </a>

              <?php if ($isSuperAdmin): ?>
              <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=<?= (int)$u['id'] ?>" title="Bu kullanıcı olarak giriş yap"
                 class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/50 dark:text-amber-300 dark:hover:bg-amber-900/70 transition-colors duration-200">
                <i class="fa-solid fa-right-to-bracket mr-1"></i>Giriş
              </a>

              <?php if ((int)$u['id'] > 1): ?>
              <button onclick="deleteUser(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['login']) ?>')"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/50 dark:text-red-300 dark:hover:bg-red-900/70 transition-colors duration-200">
                <i class="fa-solid fa-trash mr-1"></i>Sil
              </button>
              <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Empty State -->
  <div id="emptyState" class="hidden px-6 py-16 text-center">
    <i class="fa-solid fa-users text-4xl text-slate-400 dark:text-slate-600 mb-4"></i>
    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-1">Kullanıcı Bulunamadı</h3>
    <p class="text-slate-500 dark:text-slate-400">Arama kriterlerinize uygun kullanıcı bulunamadı.</p>
  </div>
</div>

<!-- User Details Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-user mr-2 text-indigo-500"></i>Kullanıcı Detayları
        </h3>
        <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <div id="userModalContent" class="p-6">
        <!-- Modal content will be populated by JavaScript -->
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-red-600 dark:text-red-400">
          <i class="fa-solid fa-triangle-exclamation mr-2"></i>Kullanıcıyı Sil
        </h3>
        <button onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <div class="p-6">
        <p class="text-slate-700 dark:text-slate-300 mb-6">
          <strong id="deleteUserName"></strong> kullanıcısını silmek istediğinizden emin misiniz?
          Bu işlem geri alınamaz.
        </p>

        <div class="flex justify-end gap-3">
          <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 transition-colors duration-200">
            İptal
          </button>
          <button id="confirmDeleteBtn" onclick="confirmDelete()" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors duration-200">
            <i class="fa-solid fa-trash mr-1"></i>Sil
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Store users data for modal
const usersData = <?php echo json_encode($users); ?>;
let deleteUserId = null;

// Search and filter functionality
document.getElementById('searchInput').addEventListener('input', filterUsers);
document.getElementById('roleFilter').addEventListener('change', filterUsers);
<?php if ($isSuperAdmin): ?>document.getElementById('groupFilter').addEventListener('change', filterUsers);<?php endif; ?>

function filterUsers() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const roleFilter = document.getElementById('roleFilter').value;
  const groupFilter = <?php echo $isSuperAdmin ? 'document.getElementById("groupFilter").value' : '""'; ?>;

  const rows = document.querySelectorAll('.user-row');
  let visibleCount = 0;

  rows.forEach(row => {
    const login = row.dataset.login.toLowerCase();
    const exten = (row.dataset.exten || '').toLowerCase();
    const role = row.dataset.role;
    const group = row.dataset.group;

    const matchesSearch = login.includes(searchTerm) || exten.includes(searchTerm);
    const matchesRole = !roleFilter || role === roleFilter;
    const matchesGroup = !groupFilter || group === groupFilter;

    if (matchesSearch && matchesRole && matchesGroup) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  // Update empty state
  const emptyState = document.getElementById('emptyState');
  const tableBody = document.getElementById('usersTableBody');

  if (visibleCount === 0) {
    emptyState.classList.remove('hidden');
    tableBody.classList.add('hidden');
  } else {
    emptyState.classList.add('hidden');
    tableBody.classList.remove('hidden');
  }
}

function clearFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('roleFilter').value = '';
  <?php if ($isSuperAdmin): ?>document.getElementById('groupFilter').value = '';<?php endif; ?>
  filterUsers();
}

// Modal functions
function showUserDetails(index) {
  const user = usersData[index];
  if (!user) return;

  const roleColor = getRoleColor(user.role);

  const modalContent = document.getElementById('userModalContent');
  modalContent.innerHTML = `
    <div class="space-y-6">
      <!-- User Header -->
      <div class="flex items-center space-x-4 pb-4 border-b border-slate-200 dark:border-slate-700">
        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-${roleColor}-400 to-${roleColor}-600 flex items-center justify-center">
          <span class="text-white font-bold text-xl">
            ${user.login.charAt(0).toUpperCase()}
          </span>
        </div>
        <div>
          <h4 class="text-xl font-bold text-slate-900 dark:text-white">${user.login}</h4>
          <p class="text-slate-600 dark:text-slate-400">ID: #${user.id}</p>
        </div>
      </div>

      <!-- User Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Hesap Bilgileri</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Kullanıcı Adı:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${user.login}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Rol:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${roleColor}-100 text-${roleColor}-800 dark:bg-${roleColor}-900/50 dark:text-${roleColor}-300">
                <i class="fa-solid fa-shield mr-1 text-xs"></i>${user.role}
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Exten:</span>
              <span class="font-mono font-semibold ${user.exten ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-600'}">
                ${user.exten || 'Belirtilmemiş'}
              </span>
            </div>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Sistem Bilgileri</div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Grup ID:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${user.group_id || 'N/A'}</span>
            </div>
            ${user.agent_id ? `
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Agent ID:</span>
              <span class="font-mono font-semibold text-pink-600 dark:text-pink-400">${user.agent_id}</span>
            </div>
            ` : ''}
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400">Kayıt Tarihi:</span>
              <span class="text-slate-900 dark:text-white">Bilinmiyor</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
        <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=${user.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-edit mr-2"></i>Kullanıcıyı Düzenle
        </a>
        <?php if ($isSuperAdmin): ?>
        <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=${user.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-right-to-bracket mr-2"></i>Bu Kullanıcı Olarak Giriş Yap
        </a>
        <?php endif; ?>
      </div>
    </div>
  `;

  document.getElementById('userModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('userModal').classList.add('hidden');
}

function deleteUser(userId, userName) {
  deleteUserId = userId;
  document.getElementById('deleteUserName').textContent = userName;
  document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.add('hidden');
  deleteUserId = null;
}

function confirmDelete() {
  if (deleteUserId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= \App\Helpers\Url::to('/users/delete') ?>';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = deleteUserId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}

function getRoleColor(role) {
  switch(role) {
    case 'superadmin': return 'fuchsia';
    case 'groupadmin': return 'blue';
    case 'user': return 'emerald';
    case 'groupmember': return 'purple';
    default: return 'slate';
  }
}

// Export to Excel function
function exportUsers() {
  const users = <?php echo json_encode($users); ?>;

  if (users.length === 0) {
    alert('Dışa aktarılacak kullanıcı bulunamadı.');
    return;
  }

  // Create CSV content
  let csvContent = 'ID,Kullanici_Adi,Rol,Exten,Grup_ID,Agent_ID\n';
  users.forEach(user => {
    csvContent += `"${user.id}","${user.login}","${user.role}","${user.exten || ''}","${user.group_id || ''}","${user.agent_id || ''}"\n`;
  });

  // Create and download file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `kullanicilar_export_${new Date().toISOString().split('T')[0]}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Close modals when clicking outside or pressing Escape
document.addEventListener('click', function(event) {
  if (event.target.id === 'userModal') {
    closeModal();
  }
  if (event.target.id === 'deleteModal') {
    closeDeleteModal();
  }
});

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeModal();
    closeDeleteModal();
  }
});
</script>

<?php
function getRoleColor($role) {
  switch($role) {
    case 'superadmin': return 'fuchsia';
    case 'groupadmin': return 'blue';
    case 'user': return 'emerald';
    case 'groupmember': return 'purple';
    default: return 'slate';
  }
}
?>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>
