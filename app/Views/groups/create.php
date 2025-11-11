<?php $title=__('create_group') . ' - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-plus text-indigo-600"></i> <?= __('create_group') ?></h1>
    <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700"><?= __('back') ?></a>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($ok)): ?>
    <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
    <div>
      <label class="block text-sm mb-1"><?= __('name') ?></label>
      <input name="name" required class="w-full border rounded p-2 bg-white dark:bg-slate-900">
    </div>
    <div>
      <label class="block text-sm mb-1"><?= __('margin') ?> %</label>
      <input type="number" step="0.01" name="margin" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0">
    </div>
    <div>
      <label class="block text-sm mb-1"><?= __('initial_balance') ?></label>
      <input type="number" step="0.01" name="balance" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="0">
    </div>
    <div>
      <label class="block text-sm mb-1"><?= __('api_group_mapping') ?></label>
      <?php $apiGroups = $apiGroups ?? []; ?>
      <select name="api_group_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
        <option value=""><?= __('not_selected') ?></option>
        <?php foreach ($apiGroups as $ag): $gid=(int)($ag['id']??0); $gname=(string)($ag['name']??''); ?>
          <option value="<?= $gid ?>">#<?= $gid ?> - <?= htmlspecialchars($gname) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="text-xs text-slate-500 mt-1"><?= __('auto_match_explanation') ?></div>
    </div>
    <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2"><?= __('create') ?></button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
