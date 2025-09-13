<?php $title='Dashboard - PapaM VoIP Panel'; require __DIR__.'/partials/header.php'; ?>

  <section class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-indigo-600/10 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-wallet"></i></div>
          <div>
            <div class="text-sm text-slate-500">Ana Bakiye (API)</div>
            <div class="text-2xl font-semibold"><span id="balance"><?= isset($balanceValue) && $balanceValue!==null ? htmlspecialchars((string)$balanceValue) : '...' ?></span></div>
          </div>
        </div>
      </div>
      <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5 block">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-emerald-600/10 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-layer-group"></i></div>
          <div>
            <div class="text-sm text-slate-500">Gruplar</div>
            <div class="text-lg font-medium">Yönet ve Görüntüle</div>
          </div>
        </div>
      </a>
      <a href="<?= \App\Helpers\Url::to('/calls') ?>" class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5 block">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-rose-600/10 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-phone"></i></div>
          <div>
            <div class="text-sm text-slate-500">Çağrılar</div>
            <div class="text-lg font-medium">Liste ve Kayıt Dinleme</div>
          </div>
        </div>
      </a>
    </div>
  </section>

  <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <a href="<?= \App\Helpers\Url::to('/users') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-users text-indigo-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Kullanıcılar</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/reports') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-chart-line text-emerald-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Raporlar</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/agents') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-headset text-rose-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Agent Durum</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/numbers') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-address-book text-amber-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Dış Numaralar</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/balance') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-wallet text-fuchsia-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Ana Bakiye</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-right-from-bracket text-slate-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Çıkış</div>
    </a>
  </section>

  <script src="<?= \App\Helpers\Url::to('/public/assets/js/chart.min.js') ?>"></script>
  <script>if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js';document.head.appendChild(s);}</script>
  <script src="<?= \App\Helpers\Url::to('/public/assets/js/dashboard.js') ?>"></script>

<?php require __DIR__.'/partials/footer.php'; ?>

