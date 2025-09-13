<?php $title='Yeni Kullanıcı - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="container mx-auto p-4 max-w-lg">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-user-plus text-indigo-600"></i> Yeni Kullanıcı</h1>
      <a href="<?= \App\Helpers\Url::to('/users') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700">Geri</a>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
      <div>
        <label class="block text-sm mb-1">Kullanıcı Adı</label>
        <input name="login" required class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm mb-1">Şifre</label>
        <input type="password" name="password" required class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm mb-1">Dahili (exten)</label>
        <input name="exten" class="w-full border rounded p-2 bg-white dark:bg-slate-900" placeholder="1001">
      </div>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
      <div>
        <label class="block text-sm mb-1">Rol</label>
        <select name="role" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value="groupadmin">groupadmin</option>
          <option value="superadmin">superadmin</option>
        </select>
      </div>
      <div>
        <label class="block text-sm mb-1">Grup</label>
        <select name="group_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value="">(seçiniz)</option>
          <?php foreach (($groups ?? []) as $g): ?>
            <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2">Kaydet</button>
    </form>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

