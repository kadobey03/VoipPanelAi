<?php $title='Kullanıcılar - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-users text-indigo-600"></i> Kullanıcılar</h1>
    <div class="space-x-2">
      <a href="<?= \App\Helpers\Url::to('/users/create') ?>" class="px-3 py-2 rounded bg-indigo-600 text-white"><i class="fa-solid fa-user-plus"></i> Yeni Kullanıcı</a>
    </div>
  </div>
  <div class="overflow-x-auto bg-white/80 dark:bg-slate-800 rounded-xl shadow">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40">
        <tr class="border-b border-slate-200 dark:border-slate-700 text-left">
          <th class="p-2">ID</th>
          <th class="p-2">Kullanıcı</th>
          <th class="p-2">Rol</th>
          <th class="p-2">Exten</th>
          <th class="p-2">Grup</th>
          <th class="p-2">İşlem</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2">#<?= (int)$u['id'] ?></td>
          <td class="p-2"><?= htmlspecialchars($u['login']) ?></td>
          <td class="p-2"><span class="px-2 py-0.5 rounded text-xs <?= ($u['role']==='superadmin')?'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-200':'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200' ?>"><?= htmlspecialchars($u['role']) ?></span></td>
          <td class="p-2"><?= htmlspecialchars((string)($u['exten'] ?? '')) ?></td>
          <td class="p-2"><?= htmlspecialchars((string)$u['group_id']) ?></td>
          <td class="p-2 space-x-2">
            <a class="inline-flex items-center gap-1 text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=<?= (int)$u['id'] ?>"><i class="fa-regular fa-pen-to-square"></i> Düzenle</a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
            <a class="inline-flex items-center gap-1 text-amber-600 hover:underline" href="<?= \App\Helpers\Url::to('/admin/impersonate') ?>?id=<?= (int)$u['id'] ?>" title="Login as"><i class="fa-solid fa-right-to-bracket"></i> Login as</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin' && (int)$u['id']>1): ?>
            <form method="post" action="<?= \App\Helpers\Url::to('/users/delete') ?>" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
              <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
              <button class="inline-flex items-center gap-1 text-red-600"><i class="fa-regular fa-trash-can"></i> Sil</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
