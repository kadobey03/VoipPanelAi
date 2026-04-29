<?php $title=__('main_balance').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-fuchsia-600 to-purple-600 bg-clip-text text-transparent flex items-center justify-center gap-3 mb-2">
          <i class="fa-solid fa-wallet text-3xl"></i>
          <?= __('main_balance') ?>
        </h1>
        <p class="text-slate-600 dark:text-slate-400"><?= __('balance_management_description') ?></p>
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
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200"><?= __('current_main_balance') ?></h3>
                <p class="text-sm text-slate-600 dark:text-slate-400"><?= __('current_balance_via_api') ?></p>
              </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 mb-4">
              <?php
                $bal = $balance ?? null;
                if (is_numeric($bal)):
              ?>
                <div class="flex flex-col items-center justify-center py-2">
                  <span class="text-5xl font-extrabold bg-gradient-to-r from-fuchsia-600 to-purple-600 bg-clip-text text-transparent tracking-tight">
                    $<?= number_format((float)$bal, 2) ?>
                  </span>
                  <span class="mt-1 text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest"><?= __('current_balance') ?></span>
                </div>
              <?php elseif (is_array($bal) || is_object($bal)):
                $items = (array)$bal;
                $iconMap = [
                  'balance'       => ['icon' => 'fa-coins',           'color' => 'text-fuchsia-500'],
                  'amount'        => ['icon' => 'fa-coins',           'color' => 'text-fuchsia-500'],
                  'credit'        => ['icon' => 'fa-circle-plus',     'color' => 'text-emerald-500'],
                  'debit'         => ['icon' => 'fa-circle-minus',    'color' => 'text-red-500'],
                  'currency'      => ['icon' => 'fa-money-bill',      'color' => 'text-yellow-500'],
                  'status'        => ['icon' => 'fa-circle-check',    'color' => 'text-blue-500'],
                  'account'       => ['icon' => 'fa-user',            'color' => 'text-indigo-500'],
                  'last_updated'  => ['icon' => 'fa-clock',           'color' => 'text-slate-400'],
                  'updated_at'    => ['icon' => 'fa-clock',           'color' => 'text-slate-400'],
                  'created_at'    => ['icon' => 'fa-calendar',        'color' => 'text-slate-400'],
                ];
              ?>
                <div class="divide-y divide-slate-200 dark:divide-slate-700">
                  <?php foreach ($items as $key => $value):
                    $meta   = $iconMap[$key] ?? ['icon' => 'fa-tag', 'color' => 'text-slate-400'];
                    $label  = ucwords(str_replace(['_', '-'], ' ', $key));
                    $isNumericVal = is_numeric($value);
                    $displayVal   = is_array($value) || is_object($value)
                      ? htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE))
                      : htmlspecialchars((string)$value);
                  ?>
                    <div class="flex items-center justify-between py-2 first:pt-0 last:pb-0">
                      <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                        <i class="fa-solid <?= $meta['icon'] ?> <?= $meta['color'] ?> w-4 text-center"></i>
                        <span class="text-sm font-medium"><?= $label ?></span>
                      </div>
                      <span class="text-sm font-semibold text-slate-800 dark:text-slate-200 <?= $isNumericVal ? 'text-fuchsia-600 dark:text-fuchsia-400 text-base' : '' ?>">
                        <?= $isNumericVal ? '$'.number_format((float)$value, 2) : $displayVal ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php elseif ($bal !== null): ?>
                <div class="flex flex-col items-center justify-center py-2">
                  <span class="text-5xl font-extrabold bg-gradient-to-r from-fuchsia-600 to-purple-600 bg-clip-text text-transparent tracking-tight">
                    <?= htmlspecialchars((string)$bal) ?>
                  </span>
                  <span class="mt-1 text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest"><?= __('current_balance') ?></span>
                </div>
              <?php else: ?>
                <div class="flex items-center justify-center gap-2 py-4 text-slate-400 dark:text-slate-500">
                  <i class="fa-solid fa-circle-exclamation"></i>
                  <span class="text-sm"><?= __('balance_unavailable') ?></span>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <i class="fa-solid fa-info-circle"></i>
              <span><?= __('main_balance_auto_update') ?></span>
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
                <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200"><?= __('group_balance_management') ?></h3>
                <p class="text-sm text-slate-600 dark:text-slate-400"><?= __('group_balance_operations') ?></p>
              </div>
            </div>

            <div class="space-y-4">
              <p class="text-slate-700 dark:text-slate-300 leading-relaxed">
                <?= __('group_balance_explanation') ?>
              </p>

              <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= \App\Helpers\Url::to('/groups') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                  <i class="fa-solid fa-users"></i>
                  <span><?= __('view_groups') ?></span>
                </a>

                <a href="<?= \App\Helpers\Url::to('/balance/topup') ?>"
                   class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                  <i class="fa-solid fa-circle-plus"></i>
                  <span><?= __('load_balance') ?></span>
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
              <div class="font-semibold text-slate-800 dark:text-slate-200"><?= __('balance_history') ?></div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('view_transaction_records') ?></div>
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
              <div class="font-semibold text-slate-800 dark:text-slate-200"><?= __('payment_methods') ?></div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('manage_payment_options') ?></div>
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
              <div class="font-semibold text-slate-800 dark:text-slate-200"><?= __('reports') ?></div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('view_detailed_analysis') ?></div>
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

