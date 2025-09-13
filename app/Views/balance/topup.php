<?php $title='Ana Bakiye - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-fuchsia-600 to-purple-600 bg-clip-text text-transparent flex items-center justify-center gap-3 mb-2">
          <i class="fa-solid fa-wallet text-3xl"></i>
          Ana Bakiye
        </h1>
        <p class="text-slate-600 dark:text-slate-400">Sistem bakiye bilgilerinizi ve yönetim araçlarını görüntüleyin</p>
      </div>

      <!-- Balance Cards Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Ana Bakiye Card -->
        <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-fuchsia-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-3 bg-fuchsia-100 dark:bg-fuchsia-900/50 rounded-xl">
                <i class="fa-solid fa-coins text-fuchsia-600 text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Mevcut Ana Bakiye</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">API üzerinden güncel bakiye</p>
              </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 mb-4">
              <pre class="text-sm text-slate-700 dark:text-slate-300 overflow-auto max-h-48"><?php echo htmlspecialchars(json_encode($balance, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <i class="fa-solid fa-info-circle"></i>
              <span>Ana bakiye API üzerinden görüntülenir ve otomatik güncellenir</span>
            </div>
          </div>
        </div>

        <!-- Grup Bakiye Yönetimi Card -->
        <div class="group relative bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          <div class="relative p-6">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl">
                <i class="fa-solid fa-users-gear text-emerald-600 text-xl"></i>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200">Grup Bakiye Yönetimi</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Gruplar için bakiye işlemleri</p>
              </div>
            </div>

            <div class="space-y-4">
              <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
                Gruplara bakiye eklemek ve yönetmek için gruplar sayfasını kullanabilirsiniz.
              </p>

              <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= \App\Helpers\Url::to('/groups') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                  <i class="fa-solid fa-users"></i>
                  <span>Grupları Görüntüle</span>
                </a>

                <a href="<?= \App\Helpers\Url::to('/balance/topup') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                  <i class="fa-solid fa-circle-plus"></i>
                  <span>Bakiye Yükle</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="<?= \App\Helpers\Url::to('/transactions') ?>"
           class="group relative bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-clock-rotate-left text-slate-600 dark:text-slate-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-slate-200">Bakiye Geçmişi</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">İşlem kayıtlarını görüntüle</div>
            </div>
          </div>
        </a>

        <a href="<?= \App\Helpers\Url::to('/payments') ?>"
           class="group relative bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-money-bill-transfer text-slate-600 dark:text-slate-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-slate-200">Ödeme Yöntemleri</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">Ödeme seçeneklerini yönet</div>
            </div>
          </div>
        </a>

        <a href="<?= \App\Helpers\Url::to('/reports') ?>"
           class="group relative bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg group-hover:bg-slate-200 dark:group-hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-chart-line text-slate-600 dark:text-slate-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-slate-200">Raporlar</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">Detaylı analiz görüntüle</div>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>

  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
      animation: fade-in 0.6s ease-out;
    }
  </style>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

