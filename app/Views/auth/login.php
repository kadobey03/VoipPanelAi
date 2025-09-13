<?php $hideNav=true; $title='Giriş - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-sm rounded-xl shadow-lg p-6 bg-white/80 dark:bg-slate-800 animate__animated animate__fadeIn">
      <div class="flex items-center gap-2 mb-4">
        <i class="fa-solid fa-wave-square text-indigo-600 text-2xl"></i>
        <h1 class="text-xl font-semibold">PapaM VoIP Panel</h1>
      </div>
      <?php if (!empty($error)): ?>
        <div class="mb-3 p-2 rounded bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-100 text-sm">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <form method="post" class="space-y-3">
        <div>
          <label class="block text-sm mb-1">Kullanıcı Adı</label>
          <div class="relative">
            <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input name="login" required class="w-full border rounded p-2 pl-9 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500">
          </div>
        </div>
        <div>
          <label class="block text-sm mb-1">Şifre</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="password" name="password" required class="w-full border rounded p-2 pl-9 bg-white dark:bg-slate-900 focus:ring-2 focus:ring-indigo-500">
          </div>
        </div>
        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2 hover:opacity-95 transition"><i class="fa-solid fa-right-to-bracket"></i> Giriş Yap</button>
      </form>
      <div class="mt-3 text-center text-xs text-gray-500">Kurulum: <?= \App\Helpers\Url::to('/install/') ?></div>
    </div>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
