<?php $title=__('payment_methods_title') . ' - ' . __('site_title'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 animate-fade-in">
        <div>
          <h1 class="text-4xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent flex items-center gap-3 mb-2">
            <i class="fa-solid fa-money-bill-transfer text-3xl"></i>
            <?= __('payment_methods_title') ?>
          </h1>
          <p class="text-slate-600 dark:text-slate-400"><?= __('manage_payment_methods_desc') ?></p>
        </div>
        <a href="<?= \App\Helpers\Url::to('/payment-methods/create') ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl mt-4 sm:mt-0">
          <i class="fa-solid fa-plus"></i>
          <span><?= __('new_method') ?></span>
        </a>
      </div>

      <!-- Payment Methods Table/Card -->
      <?php if (!empty($items ?? [])): ?>
      <!-- Desktop Table -->
      <div class="hidden lg:block bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl overflow-hidden border border-slate-200/50 dark:border-slate-700/50">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-emerald-100 to-teal-100 dark:from-emerald-900/50 dark:to-teal-900/50">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('payment_method') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('type') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('commission') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('status') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('actions') ?></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
              <?php foreach (($items ?? []) as $pm): ?>
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-credit-card text-indigo-600"></i>
                    </div>
                    <div>
                      <div class="font-semibold text-slate-900 dark:text-slate-100">
                        <?= htmlspecialchars($pm['name']) ?>
                      </div>
                      <div class="text-sm text-slate-500 dark:text-slate-400">
                        ID: <?= (int)$pm['id'] ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-tag"></i>
                    <?= htmlspecialchars($pm['method_type']) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-slate-900 dark:text-slate-100">
                    <div class="flex items-center gap-1">
                      <i class="fa-solid fa-percent text-slate-400"></i>
                      <span class="font-medium">%<?= number_format((float)$pm['fee_percent'],2) ?></span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                      <i class="fa-solid fa-dollar-sign text-slate-400 text-xs"></i>
                      <span class="text-slate-600 dark:text-slate-400">+<?= number_format((float)$pm['fee_fixed'],2) ?></span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                    <?php if((int)$pm['active'] === 1): ?>bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                    <?php else: ?>bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400<?php endif; ?>">
                    <i class="fa-solid <?php if((int)$pm['active'] === 1): ?>fa-check<?php else: ?>fa-xmark<?php endif; ?>"></i>
                    <?php if((int)$pm['active'] === 1): ?><?= __('active') ?><?php else: ?><?= __('inactive') ?><?php endif; ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <a href="<?= \App\Helpers\Url::to('/payment-methods/edit') ?>?id=<?= (int)$pm['id'] ?>"
                       class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors text-sm font-medium">
                      <i class="fa-regular fa-pen-to-square"></i>
                      <span><?= __('edit') ?></span>
                    </a>
                    <form method="post" action="<?= \App\Helpers\Url::to('/payment-methods/delete') ?>" style="display:inline"
                          onsubmit="return confirm('<?= __('confirm_delete_payment_method') ?>')">
                      <input type="hidden" name="id" value="<?= (int)$pm['id'] ?>">
                      <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400 rounded-lg hover:bg-rose-200 dark:hover:bg-rose-800 transition-colors text-sm font-medium">
                        <i class="fa-regular fa-trash-can"></i>
                        <span><?= __('delete') ?></span>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Cards -->
      <div class="lg:hidden space-y-4">
        <?php foreach (($items ?? []) as $pm): ?>
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-credit-card text-indigo-600 text-lg"></i>
              </div>
              <div>
                <div class="font-semibold text-slate-900 dark:text-slate-100 text-lg">
                  <?= htmlspecialchars($pm['name']) ?>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                  <?= htmlspecialchars($pm['method_type']) ?> • ID: <?= (int)$pm['id'] ?>
                </div>
              </div>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
              <?php if((int)$pm['active'] === 1): ?>bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
              <?php else: ?>bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400<?php endif; ?>">
              <i class="fa-solid <?php if((int)$pm['active'] === 1): ?>fa-check<?php else: ?>fa-xmark<?php endif; ?>"></i>
              <?php if((int)$pm['active'] === 1): ?><?= __('active') ?><?php else: ?><?= __('inactive') ?><?php endif; ?>
            </span>
          </div>

          <div class="mb-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
            <div class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= __('commission_rates') ?></div>
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1">
                <i class="fa-solid fa-percent text-slate-400"></i>
                <span>%<?= number_format((float)$pm['fee_percent'],2) ?></span>
              </div>
              <div class="text-slate-500 dark:text-slate-400">+</div>
              <div class="flex items-center gap-1">
                <i class="fa-solid fa-dollar-sign text-slate-400 text-xs"></i>
                <span>$<?= number_format((float)$pm['fee_fixed'],2) ?></span>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <a href="<?= \App\Helpers\Url::to('/payment-methods/edit') ?>?id=<?= (int)$pm['id'] ?>"
               class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400 rounded-xl hover:bg-indigo-200 dark:hover:bg-indigo-800 transition-colors text-sm font-medium">
              <i class="fa-regular fa-pen-to-square"></i>
              <span><?= __('edit') ?></span>
            </a>
            <form method="post" action="<?= \App\Helpers\Url::to('/payment-methods/delete') ?>" style="flex:1"
                  onsubmit="return confirm('<?= __('confirm_delete_payment_method') ?>')">
              <input type="hidden" name="id" value="<?= (int)$pm['id'] ?>">
              <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-400 rounded-xl hover:bg-rose-200 dark:hover:bg-rose-800 transition-colors text-sm font-medium">
                <i class="fa-regular fa-trash-can"></i>
                <span><?= __('delete') ?></span>
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php else: ?>
      <!-- Empty State -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl p-12 text-center border border-slate-200/50 dark:border-slate-700/50">
        <div class="w-24 h-24 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fa-solid fa-money-bill-transfer text-4xl text-slate-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2"><?= __('no_payment_methods_yet') ?></h3>
        <p class="text-slate-600 dark:text-slate-400 mb-6">
          <?= __('no_payment_methods_desc') ?>
        </p>
        <a href="<?= \App\Helpers\Url::to('/payment-methods/create') ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
          <i class="fa-solid fa-plus"></i>
          <span><?= __('add_first_method') ?></span>
        </a>
      </div>
      <?php endif; ?>

      <!-- Statistics Card -->
      <?php if (!empty($items ?? [])): ?>
      <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
              <i class="fa-solid fa-list text-indigo-600"></i>
            </div>
            <div>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                <?= count($items ?? []) ?>
              </div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('total_methods') ?></div>
            </div>
          </div>
        </div>

        <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
              <i class="fa-solid fa-check text-emerald-600"></i>
            </div>
            <div>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                <?= count(array_filter($items ?? [], fn($pm) => (int)$pm['active'] === 1)) ?>
              </div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('active_methods') ?></div>
            </div>
          </div>
        </div>

        <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-rose-100 dark:bg-rose-900/50 rounded-lg">
              <i class="fa-solid fa-xmark text-rose-600"></i>
            </div>
            <div>
              <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                <?= count(array_filter($items ?? [], fn($pm) => (int)$pm['active'] !== 1)) ?>
              </div>
              <div class="text-sm text-slate-600 dark:text-slate-400"><?= __('inactive_methods') ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
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

