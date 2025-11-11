<?php $title=__('balance_history').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-600 to-slate-800 bg-clip-text text-transparent flex items-center justify-center gap-3 mb-2">
          <i class="fa-solid fa-clock-rotate-left text-3xl"></i>
          <?= __('balance_history') ?>
        </h1>
        <p class="text-slate-600 dark:text-slate-400"><?= __('view_all_balance_transaction_details') ?></p>
      </div>

      <!-- Filter Section -->
      <?php if ($isSuper): ?>
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl p-6 mb-6 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center gap-3 mb-4">
          <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl">
            <i class="fa-solid fa-filter text-indigo-600 text-xl"></i>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200"><?= __('filter') ?></h3>
            <p class="text-sm text-slate-600 dark:text-slate-400"><?= __('filter_transactions_by_group') ?></p>
          </div>
        </div>

        <form method="get" class="flex flex-col sm:flex-row items-end gap-4">
          <div class="flex-1 max-w-xs">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
              <?= __('group_id_optional') ?>
            </label>
            <input type="number" name="group_id" value="<?= isset($_GET['group_id'])?(int)$_GET['group_id']:'' ?>"
                   class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                   placeholder="<?= __('leave_empty_for_all_groups') ?>">
          </div>
          <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
              <i class="fa-solid fa-magnifying-glass"></i>
              <span><?= __('apply') ?></span>
            </button>
            <?php if(isset($_GET['group_id'])): ?>
            <a href="<?= \App\Helpers\Url::to('/transactions') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
              <i class="fa-solid fa-xmark"></i>
              <span><?= __('clear') ?></span>
            </a>
            <?php endif; ?>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <!-- Transactions Table/Card -->
      <?php if (!empty($items ?? [])): ?>
      <!-- Desktop Table -->
      <div class="hidden lg:block bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl overflow-hidden border border-slate-200/50 dark:border-slate-700/50">
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('date') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('group') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('type') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('amount') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('reference') ?></th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 dark:text-slate-300"><?= __('description') ?></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600">
              <?php foreach (($items ?? []) as $t): ?>
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                  <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-slate-400"></i>
                    <?= htmlspecialchars($t['created_at']) ?>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-900 dark:text-slate-100">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-users text-indigo-600 text-xs"></i>
                    </div>
                    <?= htmlspecialchars($t['group_name'] ?? ('#'.$t['group_id'])) ?>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium
                    <?php if(strtolower($t['type']) === 'credit'): ?>bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                    <?php elseif(strtolower($t['type']) === 'debit'): ?>bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400
                    <?php else: ?>bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400<?php endif; ?>">
                    <i class="fa-solid <?php if(strtolower($t['type']) === 'credit'): ?>fa-plus<?php elseif(strtolower($t['type']) === 'debit'): ?>fa-minus<?php else: ?>fa-exchange-alt<?php endif; ?>"></i>
                    <?= htmlspecialchars($t['type']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-semibold
                  <?php if((float)$t['amount'] > 0): ?>text-emerald-600 dark:text-emerald-400
                  <?php elseif((float)$t['amount'] < 0): ?>text-rose-600 dark:text-rose-400
                  <?php else: ?>text-slate-900 dark:text-slate-100<?php endif; ?>">
                  <div class="flex items-center gap-1">
                    <i class="fa-solid fa-dollar-sign text-xs"></i>
                    <?= number_format((float)$t['amount'],2) ?>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                  <?= htmlspecialchars((string)$t['reference']) ?: '-' ?>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                  <?= htmlspecialchars((string)$t['description']) ?: '-' ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile Cards -->
      <div class="lg:hidden space-y-4">
        <?php foreach (($items ?? []) as $t): ?>
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50 hover:shadow-2xl transition-all duration-300">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-users text-indigo-600"></i>
              </div>
              <div>
                <div class="font-semibold text-slate-900 dark:text-slate-100">
                  <?= htmlspecialchars($t['group_name'] ?? ('#'.$t['group_id'])) ?>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1">
                  <i class="fa-solid fa-calendar-days"></i>
                  <?= htmlspecialchars($t['created_at']) ?>
                </div>
              </div>
            </div>
            <div class="text-right">
              <div class="text-xl font-bold
                <?php if((float)$t['amount'] > 0): ?>text-emerald-600 dark:text-emerald-400
                <?php elseif((float)$t['amount'] < 0): ?>text-rose-600 dark:text-rose-400
                <?php else: ?>text-slate-900 dark:text-slate-100<?php endif; ?>">
                <i class="fa-solid fa-dollar-sign text-sm mr-1"></i>
                <?= number_format((float)$t['amount'],2) ?>
              </div>
              <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium mt-1
                <?php if(strtolower($t['type']) === 'credit'): ?>bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400
                <?php elseif(strtolower($t['type']) === 'debit'): ?>bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-400
                <?php else: ?>bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400<?php endif; ?>">
                <i class="fa-solid <?php if(strtolower($t['type']) === 'credit'): ?>fa-plus<?php elseif(strtolower($t['type']) === 'debit'): ?>fa-minus<?php else: ?>fa-exchange-alt<?php endif; ?>"></i>
                <?= htmlspecialchars($t['type']) ?>
              </span>
            </div>
          </div>

          <div class="space-y-2">
            <?php if (!empty(trim((string)$t['reference']))): ?>
            <div class="flex items-center gap-2 text-sm">
              <span class="font-medium text-slate-600 dark:text-slate-400"><?= __('reference_label') ?></span>
              <span class="text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-lg">
                <?= htmlspecialchars((string)$t['reference']) ?>
              </span>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim((string)$t['description']))): ?>
            <div class="text-sm">
              <span class="font-medium text-slate-600 dark:text-slate-400"><?= __('description_label') ?></span>
              <p class="text-slate-800 dark:text-slate-200 mt-1 leading-relaxed">
                <?= htmlspecialchars((string)$t['description']) ?>
              </p>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php else: ?>
      <!-- Empty State -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl p-12 text-center border border-slate-200/50 dark:border-slate-700/50">
        <div class="w-24 h-24 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
          <i class="fa-solid fa-clock-rotate-left text-4xl text-slate-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-2"><?= __('no_transactions_found') ?></h3>
        <p class="text-slate-600 dark:text-slate-400">
          <?php if(isset($_GET['group_id'])): ?>
            <?= __('no_transactions_for_this_group') ?>
          <?php else: ?>
            <?= __('no_transactions_in_system') ?>
          <?php endif; ?>
        </p>
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

