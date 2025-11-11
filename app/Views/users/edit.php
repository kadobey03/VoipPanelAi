<?php $title=__('edit_user_title') . ' - ' . __('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="container mx-auto p-4 max-w-lg">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-user-pen text-indigo-600"></i> <?= __('edit_user_title') ?></h1>
      <a href="<?= \App\Helpers\Url::to('/users') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700"><?= __('back') ?></a>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
      <div>
        <label class="block text-sm mb-1"><?= __('username') ?></label>
        <input name="login" required class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars($user['login']) ?>">
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('password_fill_to_change') ?></label>
        <input type="password" name="password" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('extension_exten') ?></label>
        <input name="exten" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars((string)($user['exten'] ?? '')) ?>">
      </div>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
      <div>
        <label class="block text-sm mb-1"><?= __('role') ?></label>
        <select name="role" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value="groupmember" <?= ($user['role']==='groupmember' || empty($user['role']))?'selected':'' ?>>groupmember</option>
          <option value="user" <?= $user['role']==='user'?'selected':'' ?>>user</option>
          <option value="groupadmin" <?= $user['role']==='groupadmin'?'selected':'' ?>>groupadmin</option>
          <option value="superadmin" <?= $user['role']==='superadmin'?'selected':'' ?>>superadmin</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('group') ?></label>
        <select name="group_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value=""><?= __('select_option') ?></option>
          <?php foreach (($groups ?? []) as $g): $gid=(int)$g['id']; ?>
            <option value="<?= $gid ?>" <?= $gid===(int)$user['group_id']?'selected':'' ?>><?= htmlspecialchars($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('agent_for_user_role') ?></label>
        <select name="agent_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value=""><?= __('select_option') ?></option>
          <?php foreach (($agents ?? []) as $a): $aid=(int)$a['id']; ?>
            <option value="<?= $aid ?>" <?= $aid===(int)$user['agent_id']?'selected':'' ?>><?= htmlspecialchars($a['user_login'] . ' (' . $a['exten'] . ')') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2"><?= __('update') ?></button>
    </form>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

