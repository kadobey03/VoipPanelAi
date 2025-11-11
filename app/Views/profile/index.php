<?php $title=__('profile').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4"><i class="fa-solid fa-user-gear text-indigo-600"></i> <?= __('profile') ?></h1>
    <?php if (!empty($error)): ?><div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (!empty($ok)): ?><div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
    <form method="post" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
      <div>
        <label class="block text-sm mb-1"><?= __('username') ?></label>
        <input name="login" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars($login) ?>" required>
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('new_password_optional') ?></label>
        <input type="password" name="password" class="w-full border rounded p-2 bg-white dark:bg-slate-900" placeholder="<?= __('fill_to_change') ?>">
      </div>
      <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2"><?= __('save') ?></button>
    </form>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

