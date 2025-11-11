<?php
$title=__('users') . ' - ' . __('papam_voip_panel');
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
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><?= __('user_management') ?></h1>
      <p class="text-sm text-slate-600 dark:text-slate-400"><?= __('view_and_manage_users') ?></p>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="flex gap-3">
    <button onclick="exportUsers()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-200">
      <i class="fa-solid fa-download"></i>
      <span class="hidden sm:inline"><?= __('download_excel') ?></span>
    </button>

    <a href="<?= \App\Helpers\Url::to('/users/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transform hover:scale-105 transition-all duration-200">
      <i class="fa-solid fa-user-plus"></i>
      <span class="hidden sm:inline"><?= __('new_user') ?></span>
    </a>
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
            <i class="fa-solid fa-user mr-2 text-emerald-500"></i><?= __('user') ?>
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-shield mr-2 text-purple-500"></i><?= __('role') ?>
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-phone mr-2 text-blue-500"></i><?= __('exten') ?>
          </th>
          <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
            <i class="fa-solid fa-cogs mr-2 text-gray-500"></i><?= __('actions') ?>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
        <?php foreach ($users as $index => $u): ?>
        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-all duration-200">
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
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="flex items-center space-x-2">
              <button onclick="showUserDetails(<?= $index ?>)"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-100 text-indigo-800 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-300 dark:hover:bg-indigo-900/70 transition-colors duration-200">
                <i class="fa-solid fa-eye mr-1"></i><?= __('detail') ?>
              </button>

              <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=<?= (int)$u['id'] ?>"
                 class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-300 dark:hover:bg-blue-900/70 transition-colors duration-200">
                <i class="fa-solid fa-edit mr-1"></i><?= __('edit') ?>
              </a>

              <?php if ($isSuperAdmin): ?>
              <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=<?= (int)$u['id'] ?>" title="<?= __('login_as_this_user') ?>"
                 class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/50 dark:text-amber-300 dark:hover:bg-amber-900/70 transition-colors duration-200">
                <i class="fa-solid fa-right-to-bracket mr-1"></i><?= __('login') ?>
              </a>

              <?php if ((int)$u['id'] > 1): ?>
              <button onclick="deleteUser(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['login']) ?>')"
                      class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900/50 dark:text-red-300 dark:hover:bg-red-900/70 transition-colors duration-200">
                <i class="fa-solid fa-trash mr-1"></i><?= __('delete') ?>
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
</div>

<!-- User Details Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">
          <i class="fa-solid fa-user mr-2 text-indigo-500"></i><?= __('user_details') ?>
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
          <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= __('delete_user') ?>
        </h3>
        <button onclick="closeDeleteModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-times text-xl"></i>
        </button>
      </div>

      <div class="p-6">
        <p class="text-slate-700 dark:text-slate-300 mb-6">
          <strong id="deleteUserName"></strong> <?= __('are_you_sure_delete_user') ?>
          <?= __('this_action_cannot_be_undone') ?>
        </p>

        <div class="flex justify-end gap-3">
          <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 transition-colors duration-200">
            <?= __('cancel') ?>
          </button>
          <button id="confirmDeleteBtn" onclick="confirmDelete()" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors duration-200">
            <i class="fa-solid fa-trash mr-1"></i><?= __('delete') ?>
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
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3"><?= __('account_info') ?></div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400"><?= __('username') ?>:</span>
              <span class="font-semibold text-slate-900 dark:text-white">${user.login}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400"><?= __('role') ?>:</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${roleColor}-100 text-${roleColor}-800 dark:bg-${roleColor}-900/50 dark:text-${roleColor}-300">
                <i class="fa-solid fa-shield mr-1 text-xs"></i>${user.role}
              </span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400"><?= __('exten') ?>:</span>
              <span class="font-mono font-semibold ${user.exten ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-600'}">
                ${user.exten || '<?= __('not_specified') ?>'}
              </span>
            </div>
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3"><?= __('additional_info') ?></div>
          <div class="space-y-3">
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400"><?= __('registration_date') ?>:</span>
              <span class="text-slate-900 dark:text-white"><?= __('unknown') ?></span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
              <span class="text-slate-600 dark:text-slate-400"><?= __('last_login') ?>:</span>
              <span class="text-slate-900 dark:text-white"><?= __('unknown') ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-center gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
        <a href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=${user.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-edit mr-2"></i><?= __('edit_user') ?>
        </a>
        <?php if ($isSuperAdmin): ?>
        <a href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=${user.id}"
           class="inline-flex items-center px-6 py-3 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium transition-colors duration-200">
          <i class="fa-solid fa-right-to-bracket mr-2"></i><?= __('login_as_user') ?>
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
    alert('<?= __('no_users_to_export') ?>');
    return;
  }

  // Create CSV content
  let csvContent = 'ID,<?= __('username') ?>,<?= __('role') ?>,<?= __('exten') ?>\n';
  users.forEach(user => {
    csvContent += `"${user.id}","${user.login}","${user.role}","${user.exten || ''}"\n`;
  });

  // Create and download file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`);
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
