<?php $title='Bakiye Yükle - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent flex items-center justify-center gap-3 mb-2">
          <i class="fa-solid fa-circle-plus text-3xl"></i>
          Bakiye Yükle
        </h1>
        <p class="text-slate-600 dark:text-slate-400">Gruplarınıza bakiye eklemek için grup seçimi yapın</p>
      </div>

      <!-- Progress Indicator -->
      <div class="flex items-center justify-center mb-8">
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
              1
            </div>
            <span class="ml-2 text-sm font-medium text-indigo-600">Grup Seç</span>
          </div>
          <div class="w-12 h-0.5 bg-slate-300 dark:bg-slate-600"></div>
          <div class="flex items-center">
            <div class="w-8 h-8 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-slate-500 dark:text-slate-400 text-sm font-semibold">
              2
            </div>
            <span class="ml-2 text-sm font-medium text-slate-500 dark:text-slate-400">Miktar Belirle</span>
          </div>
        </div>
      </div>

      <!-- Main Form Card -->
      <div class="max-w-2xl mx-auto">
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
          <div class="p-8">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-users text-indigo-600 text-2xl"></i>
              </div>
              <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-2">Grup Seçimi</h2>
              <p class="text-slate-600 dark:text-slate-400">Bakiye yüklemek istediğiniz grubu seçin</p>
            </div>

            <form method="get" action="<?= \App\Helpers\Url::to('/groups/topup') ?>" class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                  <i class="fa-solid fa-users text-indigo-600 mr-2"></i>
                  Grup Seçin
                </label>
                <div class="relative">
                  <select name="id"
                          class="w-full px-4 py-4 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 appearance-none cursor-pointer"
                          required>
                    <option value="">Grup seçin...</option>
                    <?php foreach (($groups ?? []) as $g): ?>
                      <option value="<?= (int)$g['id'] ?>" class="py-2">
                        <?= htmlspecialchars($g['name']) ?> (ID: <?= (int)$g['id'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                    <i class="fa-solid fa-chevron-down text-slate-400"></i>
                  </div>
                </div>
                <?php if (empty($groups ?? [])): ?>
                  <div class="mt-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-exclamation-triangle text-amber-600"></i>
                      <span class="text-sm text-amber-800 dark:text-amber-400">
                        Kullanılabilir grup bulunmuyor. Önce gruplarınızı oluşturun.
                      </span>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="pt-4">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        <?php if (empty($groups ?? [])): ?>disabled<?php endif; ?>>
                  <i class="fa-solid fa-arrow-right text-lg"></i>
                  <span class="font-semibold">Devam Et</span>
                  <i class="fa-solid fa-arrow-right text-lg"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Information Cards -->
        <div class="grid md:grid-cols-2 gap-4 mt-6">
          <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                <i class="fa-solid fa-info-circle text-emerald-600"></i>
              </div>
              <h3 class="font-semibold text-slate-800 dark:text-slate-200">Güvenli İşlem</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">Tüm bakiye yükleme işlemleri güvenli bir şekilde gerçekleştirilir.</p>
          </div>

          <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                <i class="fa-solid fa-clock text-blue-600"></i>
              </div>
              <h3 class="font-semibold text-slate-800 dark:text-slate-200">Anında Etkili</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">Yüklenen bakiye hemen grup hesabına yansıtılır.</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="text-center mt-8">
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= \App\Helpers\Url::to('/groups') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-users"></i>
              <span>Grupları Yönet</span>
            </a>
            <span class="text-slate-400 dark:text-slate-600">•</span>
            <a href="<?= \App\Helpers\Url::to('/balance') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-wallet"></i>
              <span>Ana Bakiye</span>
            </a>
            <span class="text-slate-400 dark:text-slate-600">•</span>
            <a href="<?= \App\Helpers\Url::to('/transactions') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-clock-rotate-left"></i>
              <span>İşlem Geçmişi</span>
            </a>
          </div>
        </div>
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

    /* Custom select styling */
    select {
      background-image: none !important;
    }
    select:focus {
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
  </style>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

