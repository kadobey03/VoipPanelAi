<?php $title='Ana Bakiye - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-wallet text-fuchsia-600"></i> Ana Bakiye</h1>
  </div>
  <div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Mevcut Ana Bakiye</h2>
      <pre class="text-sm bg-slate-50 dark:bg-slate-900 p-2 rounded overflow-auto"><?php echo htmlspecialchars(json_encode($balance, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
      <div class="mt-2 text-xs text-slate-500">Not: Ana bakiye API üzerinden görüntülenir.</div>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Grup Bakiye Yönetimi</h2>
      <p class="text-sm">Gruplara bakiye eklemek için <a class="text-blue-600 hover:underline" href="<?= \App\Helpers\Url::to('/groups') ?>">Gruplar</a> sayfasından ilgili grupta “Bakiye Yükle” bölümünü kullanın.</p>
    </div>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

