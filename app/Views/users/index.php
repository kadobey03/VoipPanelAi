<?php
$title = __('users') . ' - ' . __('papam_voip_panel');
require dirname(__DIR__).'/partials/header.php';
$isSuperAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'superadmin';

function getRoleColor($role) {
    return match($role) {
        'superadmin'  => 'fuchsia',
        'groupadmin'  => 'blue',
        'groupmember' => 'purple',
        'user'        => 'emerald',
        default       => 'slate',
    };
}
function getRoleBadge($role) {
    return match($role) {
        'superadmin'  => 'bg-fuchsia-100 text-fuchsia-800 dark:bg-fuchsia-900/40 dark:text-fuchsia-300',
        'groupadmin'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'groupmember' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        'user'        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        default       => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    };
}
function getRoleIcon($role) {
    return match($role) {
        'superadmin'  => 'fa-crown',
        'groupadmin'  => 'fa-user-tie',
        'groupmember' => 'fa-user',
        'user'        => 'fa-headset',
        default       => 'fa-circle-user',
    };
}

// Stats
$totalUsers    = count($users);
$superAdmins   = count(array_filter($users, fn($u) => $u['role'] === 'superadmin'));
$groupAdmins   = count(array_filter($users, fn($u) => $u['role'] === 'groupadmin'));
$groupMembers  = count(array_filter($users, fn($u) => $u['role'] === 'groupmember'));
$withExten     = count(array_filter($users, fn($u) => !empty($u['exten'])));
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
  <div class="flex items-center gap-3">
    <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl shadow-lg">
      <i class="fa-solid fa-users text-white text-xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><?= __('user_management') ?></h1>
      <p class="text-sm text-slate-500 dark:text-slate-400"><?= __('view_and_manage_users') ?></p>
    </div>
  </div>
  <div class="flex gap-2">
    <button onclick="exportUsers()"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-200 text-sm">
      <i class="fa-solid fa-download"></i>
      <span class="hidden sm:inline"><?= __('download_excel') ?></span>
    </button>
    <a href="<?= \App\Helpers\Url::to('/users/create') ?>"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 hover:scale-105 transition-all duration-200 text-sm">
      <i class="fa-solid fa-user-plus"></i>
      <span class="hidden sm:inline"><?= __('new_user') ?></span>
    </a>
  </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="p-2.5 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex-shrink-0">
      <i class="fa-solid fa-users text-indigo-600 dark:text-indigo-400"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= $totalUsers ?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Toplam</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="p-2.5 bg-fuchsia-100 dark:bg-fuchsia-900/40 rounded-lg flex-shrink-0">
      <i class="fa-solid fa-crown text-fuchsia-600 dark:text-fuchsia-400"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= $superAdmins ?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Süper Admin</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="p-2.5 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex-shrink-0">
      <i class="fa-solid fa-user-tie text-blue-600 dark:text-blue-400"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= $groupAdmins ?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Grup Admin</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="p-2.5 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex-shrink-0">
      <i class="fa-solid fa-user text-purple-600 dark:text-purple-400"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= $groupMembers ?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Üye</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex items-center gap-3 shadow-sm">
    <div class="p-2.5 bg-cyan-100 dark:bg-cyan-900/40 rounded-lg flex-shrink-0">
      <i class="fa-solid fa-phone text-cyan-600 dark:text-cyan-400"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?= $withExten ?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Exten'li</div>
    </div>
  </div>
</div>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
  <!-- Search -->
  <div class="relative w-full sm:w-80">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input id="userSearch" type="text" placeholder="Kullanıcı adı, grup veya exten ara..."
           class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
  </div>

  <!-- Role Filter -->
  <div class="flex items-center gap-2 flex-wrap">
    <button onclick="filterRole('')"     class="role-filter-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Tümü</button>
    <button onclick="filterRole('superadmin')"  class="role-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
      <i class="fa-solid fa-crown mr-1"></i>Süper Admin
    </button>
    <button onclick="filterRole('groupadmin')"  class="role-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
      <i class="fa-solid fa-user-tie mr-1"></i>Grup Admin
    </button>
    <button onclick="filterRole('groupmember')" class="role-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
      <i class="fa-solid fa-user mr-1"></i>Üye
    </button>
  </div>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">#</th>
          <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <i class="fa-solid fa-user mr-1 text-indigo-400"></i><?= __('user') ?>
          </th>
          <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <i class="fa-solid fa-shield mr-1 text-purple-400"></i><?= __('role') ?>
          </th>
          <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <i class="fa-solid fa-users mr-1 text-blue-400"></i><?= __('group') ?>
          </th>
          <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <i class="fa-solid fa-phone mr-1 text-emerald-400"></i><?= __('exten') ?>
          </th>
          <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
            <i class="fa-solid fa-cogs mr-1 text-slate-400"></i><?= __('actions') ?>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
        <?php foreach ($users as $index => $u): ?>
        <?php $roleColor = getRoleColor($u['role']); $roleBadge = getRoleBadge($u['role']); $roleIcon = getRoleIcon($u['role']); ?>
        <tr class="user-row hover:bg-indigo-50/40 dark:hover:bg-slate-700/30 transition-colors group"
            data-login="<?= strtolower(htmlspecialchars($u['login'])) ?>"
            data-group="<?= strtolower(htmlspecialchars($u['group_name'] ?? '')) ?>"
            data-exten="<?= htmlspecialchars($u['exten'] ?? '') ?>"
            data-role="<?= htmlspecialchars($u['role']) ?>">

          <!-- ID -->
          <td class="px-4 py-3 font-mono text-xs font-bold text-indigo-500 dark:text-indigo-400">
            #<?= (int)$u['id'] ?>
          </td>

          <!-- User -->
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-<?= $roleColor ?>-400 to-<?= $roleColor ?>-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm">
                <?= strtoupper(mb_substr($u['login'], 0, 1)) ?>
              </div>
              <div class="min-w-0">
                <div class="font-semibold text-slate-800 dark:text-white truncate"><?= htmlspecialchars($u['login']) ?></div>
                <div class="text-xs text-slate-400 dark:text-slate-500">ID #<?= (int)$u['id'] ?></div>
              </div>
            </div>
          </td>

          <!-- Role -->
          <td class="px-4 py-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $roleBadge ?>">
              <i class="fa-solid <?= $roleIcon ?> text-xs"></i>
              <?= htmlspecialchars(ucfirst($u['role'])) ?>
            </span>
          </td>

          <!-- Group -->
          <td class="px-4 py-3">
            <?php if (!empty($u['group_name'])): ?>
            <div class="flex flex-col gap-0.5">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs font-medium max-w-[140px] truncate">
                <i class="fa-solid fa-users text-xs flex-shrink-0"></i>
                <span class="truncate"><?= htmlspecialchars($u['group_name']) ?></span>
              </span>
              <?php if ($u['role'] === 'groupadmin'): ?>
              <span class="text-xs text-amber-600 dark:text-amber-400 font-medium ml-1">
                <i class="fa-solid fa-star text-xs"></i> Admin
              </span>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
            <?php endif; ?>
          </td>

          <!-- Exten -->
          <td class="px-4 py-3">
            <?php if (!empty($u['exten'])): ?>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 text-xs font-mono font-semibold">
              <i class="fa-solid fa-phone text-xs"></i>
              <?= htmlspecialchars($u['exten']) ?>
            </span>
            <?php else: ?>
            <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
            <?php endif; ?>
          </td>

          <!-- Actions -->
          <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1.5">
              <!-- Detail -->
              <button onclick="showUserDetails(<?= $index ?>)"
                      title="<?= __('detail') ?>"
                      class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition-colors">
                <i class="fa-solid fa-eye text-xs"></i>
              </button>

              <!-- Edit -->
              <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=<?= (int)$u['id'] ?>"
                 title="<?= __('edit') ?>"
                 class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
                <i class="fa-solid fa-pen text-xs"></i>
              </a>

              <?php if ($isSuperAdmin): ?>
              <!-- Impersonate -->
              <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=<?= (int)$u['id'] ?>"
                 title="<?= __('login_as_this_user') ?>"
                 class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50 transition-colors">
                <i class="fa-solid fa-right-to-bracket text-xs"></i>
              </a>

              <?php if ((int)$u['id'] > 1): ?>
              <!-- Delete -->
              <button onclick="deleteUser(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['login'], ENT_QUOTES) ?>')"
                      title="<?= __('delete') ?>"
                      class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors">
                <i class="fa-solid fa-trash text-xs"></i>
              </button>
              <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($users)): ?>
        <tr>
          <td colspan="6" class="px-4 py-16 text-center">
            <div class="flex flex-col items-center gap-3">
              <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-users text-3xl text-slate-300 dark:text-slate-500"></i>
              </div>
              <p class="text-slate-500 dark:text-slate-400 font-medium">Kullanıcı bulunamadı</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- No results row (search) -->
<div id="noResults" class="hidden text-center py-10 text-slate-400 dark:text-slate-500 text-sm">
  <i class="fa-solid fa-magnifying-glass text-2xl mb-2 block"></i>Arama sonucu bulunamadı
</div>

<!-- ═══════════════════════════════ USER DETAIL MODAL -->
<div id="userModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 items-center justify-center p-4"
     onclick="if(event.target===this)closeModal()">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg"><i class="fa-solid fa-user text-indigo-600 dark:text-indigo-400"></i></span>
        <?= __('user_details') ?>
      </h3>
      <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 transition-colors">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <div id="userModalContent" class="p-5"></div>
  </div>
</div>

<!-- ═══════════════════════════════ DELETE CONFIRM MODAL -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 items-center justify-center p-4"
     onclick="if(event.target===this)closeDeleteModal()">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700">
    <div class="p-6">
      <!-- Warning icon -->
      <div class="flex items-center justify-center w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/40 mx-auto mb-4">
        <i class="fa-solid fa-trash text-2xl text-red-600 dark:text-red-400"></i>
      </div>
      <h3 class="text-lg font-bold text-center text-slate-900 dark:text-white mb-2"><?= __('delete_user') ?></h3>
      <p class="text-center text-slate-600 dark:text-slate-400 text-sm mb-1">
        <strong id="deleteUserName" class="text-red-600 dark:text-red-400"></strong> <?= __('are_you_sure_delete_user') ?>
      </p>
      <p class="text-center text-xs text-slate-400 dark:text-slate-500 mb-6"><?= __('this_action_cannot_be_undone') ?></p>

      <div class="flex gap-3">
        <button onclick="closeDeleteModal()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-semibold transition-colors text-sm">
          <?= __('cancel') ?>
        </button>
        <button id="confirmDeleteBtn" onclick="confirmDelete()"
                class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition-colors text-sm shadow-lg shadow-red-500/25">
          <i class="fa-solid fa-trash mr-1.5"></i><?= __('delete') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const usersData = <?php echo json_encode($users); ?>;
let deleteUserId = null;
let currentRoleFilter = '';

// ── Search ────────────────────────────────────────────────────────────────
document.getElementById('userSearch').addEventListener('input', function() {
  applyFilters();
});

// ── Role Filter ───────────────────────────────────────────────────────────
function filterRole(role) {
  currentRoleFilter = role;
  document.querySelectorAll('.role-filter-btn').forEach(btn => btn.classList.remove('active'));
  event.currentTarget.classList.add('active');
  applyFilters();
}

function applyFilters() {
  const q = document.getElementById('userSearch').value.toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll('.user-row').forEach(row => {
    const login  = row.dataset.login  || '';
    const group  = row.dataset.group  || '';
    const exten  = row.dataset.exten  || '';
    const role   = row.dataset.role   || '';
    const matchQ    = !q || login.includes(q) || group.includes(q) || exten.includes(q);
    const matchRole = !currentRoleFilter || role === currentRoleFilter;
    const show = matchQ && matchRole;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('noResults').classList.toggle('hidden', visible > 0);
}

// ── User Detail Modal ─────────────────────────────────────────────────────
function showUserDetails(index) {
  const u = usersData[index];
  if (!u) return;

  const roleLabels = { superadmin:'Süper Admin', groupadmin:'Grup Admin', groupmember:'Üye', user:'Kullanıcı' };
  const roleIcons  = { superadmin:'fa-crown', groupadmin:'fa-user-tie', groupmember:'fa-user', user:'fa-headset' };
  const roleColors = { superadmin:'fuchsia', groupadmin:'blue', groupmember:'purple', user:'emerald' };
  const rc = roleColors[u.role] || 'slate';
  const ri = roleIcons[u.role]  || 'fa-circle-user';
  const rl = roleLabels[u.role] || u.role;

  const rows = [
    ['<?= __('username') ?>', `<span class="font-semibold text-slate-800 dark:text-white">${u.login}</span>`, 'fa-user text-indigo-400'],
    ['ID', `<span class="font-mono text-indigo-600 dark:text-indigo-400">#${u.id}</span>`, 'fa-fingerprint text-slate-400'],
    ['<?= __('role') ?>', `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-${rc}-100 text-${rc}-800 dark:bg-${rc}-900/40 dark:text-${rc}-300"><i class="fa-solid ${ri} text-xs"></i>${rl}</span>`, 'fa-shield text-purple-400'],
    ['<?= __('group') ?>', u.group_name ? `<span class="px-2 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-medium">${u.group_name}</span>` : '<span class="text-slate-400 text-xs">—</span>', 'fa-users text-blue-400'],
    ['<?= __('exten') ?>', u.exten ? `<span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">${u.exten}</span>` : '<span class="text-slate-400 text-xs">—</span>', 'fa-phone text-emerald-400'],
    ...(u.agent_id ? [['Agent ID', `<span class="font-mono text-slate-600 dark:text-slate-300">${u.agent_id}</span>`, 'fa-headset text-slate-400']] : []),
  ];

  const tableRows = rows.map(([label, val, icon]) => `
    <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0">
      <td class="py-3 pr-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-2"><i class="fa-solid ${icon} w-4 text-center text-xs"></i>${label}</span>
      </td>
      <td class="py-3 text-sm font-medium">${val}</td>
    </tr>`).join('');

  document.getElementById('userModalContent').innerHTML = `
    <div class="flex items-center gap-4 mb-5 pb-4 border-b border-slate-200 dark:border-slate-700">
      <div class="w-14 h-14 rounded-full bg-gradient-to-br from-${rc}-400 to-${rc}-600 flex items-center justify-center text-white font-bold text-2xl shadow-md flex-shrink-0">
        ${u.login.charAt(0).toUpperCase()}
      </div>
      <div>
        <h4 class="text-xl font-bold text-slate-900 dark:text-white">${u.login}</h4>
        <p class="text-sm text-slate-500 dark:text-slate-400">${rl} · ID #${u.id}</p>
      </div>
    </div>
    <table class="w-full mb-5">${tableRows}</table>
    <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
      <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=${u.id}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors text-sm">
        <i class="fa-solid fa-pen"></i><?= __('edit_user') ?>
      </a>
      <?php if ($isSuperAdmin): ?>
      <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=${u.id}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold transition-colors text-sm">
        <i class="fa-solid fa-right-to-bracket"></i><?= __('login_as_user') ?>
      </a>
      <?php endif; ?>
    </div>
  `;

  const modal = document.getElementById('userModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeModal() {
  const modal = document.getElementById('userModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

// ── Delete Modal ──────────────────────────────────────────────────────────
function deleteUser(userId, userName) {
  deleteUserId = userId;
  document.getElementById('deleteUserName').textContent = userName;
  const modal = document.getElementById('deleteModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeDeleteModal() {
  const modal = document.getElementById('deleteModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
  deleteUserId = null;
}

function confirmDelete() {
  if (!deleteUserId) return;
  const btn = document.getElementById('confirmDeleteBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Siliniyor...';
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= \App\Helpers\Url::to('/users/delete') ?>';
  const input = document.createElement('input');
  input.type = 'hidden'; input.name = 'id'; input.value = deleteUserId;
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}

// ── CSV Export ────────────────────────────────────────────────────────────
function exportUsers() {
  if (!usersData.length) { alert('<?= __('no_users_to_export') ?>'); return; }
  const escape = v => `"${String(v||'').replace(/"/g,'""')}"`;
  const headers = ['ID','<?= __('username') ?>','<?= __('role') ?>','<?= __('group') ?>','<?= __('exten') ?>'];
  const rows = usersData.map(u => [u.id, u.login, u.role, u.group_name||'', u.exten||''].map(escape).join(','));
  const csv = '\uFEFF' + [headers.map(escape).join(','), ...rows].join('\r\n');
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv;charset=utf-8;'}));
  a.download = `users_${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
}

// ── Keyboard ──────────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeModal(); closeDeleteModal(); }
});
</script>

<style>
.role-filter-btn {
  background: #f1f5f9; color: #64748b;
}
.dark .role-filter-btn {
  background: #1e293b; color: #94a3b8;
}
.role-filter-btn:hover {
  background: #e0e7ff; color: #4f46e5;
}
.dark .role-filter-btn:hover {
  background: #312e81; color: #a5b4fc;
}
.role-filter-btn.active {
  background: #6366f1; color: #fff;
  box-shadow: 0 2px 8px rgba(99,102,241,.3);
}
</style>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>