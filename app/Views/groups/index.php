<?php
$title = __('groups') . ' - ' . __('papam_voip_panel');
require dirname(__DIR__).'/partials/header.php';
$isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'superadmin';

$totalGroups  = count($groups);
$totalBalance = array_sum(array_column($groups, 'balance'));
$apiConnected = count(array_filter($groups, fn($g) => !empty($g['api_group_name'])));
?>

<!-- Hero Header -->
<section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 mb-6 text-white">
  <div class="absolute inset-0 bg-black/10"></div>
  <div class="absolute top-0 right-0 w-72 h-72 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 blur-3xl pointer-events-none"></div>
  <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4 blur-2xl pointer-events-none"></div>

  <div class="relative px-6 py-8 lg:px-10 lg:py-10">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-4">
        <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
          <i class="fa-solid fa-layer-group text-3xl"></i>
        </div>
        <div>
          <h1 class="text-3xl font-bold"><?= __('group_management') ?></h1>
          <p class="text-white/70 mt-0.5"><?= __('view_and_manage_groups') ?></p>
        </div>
      </div>
      <?php if ($isSuper): ?>
      <a href="<?= \App\Helpers\Url::to('/groups/create') ?>"
         class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl font-semibold transition-all duration-200 hover:scale-105 text-sm whitespace-nowrap">
        <i class="fa-solid fa-plus"></i><?= __('new_group') ?>
      </a>
      <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
      <div class="bg-white/15 backdrop-blur-sm rounded-xl p-4 flex items-center gap-3">
        <div class="p-2.5 bg-white/20 rounded-lg"><i class="fa-solid fa-layer-group text-xl"></i></div>
        <div>
          <div class="text-2xl font-bold"><?= $totalGroups ?></div>
          <div class="text-xs text-white/70"><?= __('total_groups') ?></div>
        </div>
      </div>
      <div class="bg-white/15 backdrop-blur-sm rounded-xl p-4 flex items-center gap-3">
        <div class="p-2.5 bg-white/20 rounded-lg"><i class="fa-solid fa-wallet text-xl"></i></div>
        <div>
          <div class="text-2xl font-bold">$<?= number_format($totalBalance, 2) ?></div>
          <div class="text-xs text-white/70"><?= __('total_balance') ?></div>
        </div>
      </div>
      <div class="bg-white/15 backdrop-blur-sm rounded-xl p-4 flex items-center gap-3">
        <div class="p-2.5 bg-white/20 rounded-lg"><i class="fa-solid fa-link text-xl"></i></div>
        <div>
          <div class="text-2xl font-bold"><?= $apiConnected ?></div>
          <div class="text-xs text-white/70"><?= __('api_connected') ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Toolbar: view toggle + search -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
  <!-- Search -->
  <div class="relative w-full sm:w-72">
    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
    <input id="groupSearch" type="text" placeholder="<?= __('search') ?>..."
           class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
  </div>

  <!-- View Toggle -->
  <div class="flex items-center gap-1 p-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
    <button id="btnViewCards" onclick="setView('cards')" title="Kart Görünümü"
            class="view-btn active w-9 h-9 flex items-center justify-center rounded-lg transition-all duration-200">
      <i class="fa-solid fa-grip text-sm"></i>
    </button>
    <button id="btnViewList" onclick="setView('list')" title="Liste Görünümü"
            class="view-btn w-9 h-9 flex items-center justify-center rounded-lg transition-all duration-200">
      <i class="fa-solid fa-list text-sm"></i>
    </button>
    <button id="btnViewTable" onclick="setView('table')" title="Tablo Görünümü"
            class="view-btn w-9 h-9 flex items-center justify-center rounded-lg transition-all duration-200">
      <i class="fa-solid fa-table text-sm"></i>
    </button>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════ CARDS VIEW -->
<div id="viewCards" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
  <?php foreach ($groups as $index => $g): ?>
  <?php $isConnected = !empty($g['api_group_name']); $balance = (float)$g['balance']; ?>
  <div class="group-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-300 overflow-hidden"
       data-name="<?= strtolower(htmlspecialchars($g['name'])) ?>">

    <!-- Card top accent -->
    <div class="h-1 bg-gradient-to-r <?= $isConnected ? 'from-emerald-400 to-teal-500' : 'from-slate-300 to-slate-400' ?>"></div>

    <div class="p-5">
      <!-- Header row -->
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-md flex-shrink-0">
            <?= strtoupper(mb_substr($g['name'], 0, 1)) ?>
          </div>
          <div class="min-w-0">
            <h3 class="font-bold text-slate-800 dark:text-white truncate text-base leading-tight"><?= htmlspecialchars($g['name']) ?></h3>
            <span class="text-xs font-mono text-slate-400 dark:text-slate-500">#<?= (int)$g['id'] ?></span>
          </div>
        </div>
        <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold
          <?= $isConnected ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' ?>">
          <i class="fa-solid fa-circle text-xs <?= $isConnected ? 'text-emerald-500' : 'text-slate-400' ?>"></i>
          <?= $isConnected ? __('api_connected_status') : __('not_connected') ?>
        </span>
      </div>

      <!-- Balance -->
      <div class="rounded-xl p-4 mb-4 <?= $balance <= 0 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/40' : ($balance < 10 ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40') ?>">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-0.5"><?= __('balance') ?></p>
            <p class="text-2xl font-bold <?= $balance <= 0 ? 'text-red-600 dark:text-red-400' : ($balance < 10 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') ?>">
              $<?= number_format($balance, 2) ?>
            </p>
          </div>
          <?php if ($balance < 10): ?>
          <div class="p-2 <?= $balance <= 0 ? 'bg-red-100 dark:bg-red-900/40 text-red-500' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-500' ?> rounded-lg">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
          </div>
          <?php else: ?>
          <div class="p-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-500 rounded-lg">
            <i class="fa-solid fa-circle-check text-lg"></i>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- API Group info -->
      <div class="flex items-center gap-2 mb-4 text-sm">
        <i class="fa-solid fa-plug text-slate-400 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-400 text-xs"><?= __('api_group') ?>:</span>
        <span class="font-medium text-xs truncate <?= $isConnected ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' ?>">
          <?= $isConnected ? htmlspecialchars($g['api_group_name']) : __('not_matched') ?>
        </span>
      </div>

      <!-- Action buttons -->
      <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/50">
        <button onclick="showGroupDetails(<?= $index ?>)"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition-colors">
          <i class="fa-solid fa-eye text-xs"></i><?= __('detail') ?>
        </button>

        <?php if ($isSuper): ?>
        <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors">
          <i class="fa-solid fa-pen text-xs"></i><?= __('edit') ?>
        </a>

        <button onclick="sendBalanceReport(<?= (int)$g['id'] ?>, this)"
                title="Telegram'a bakiye raporu gönder"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-900/30 dark:text-sky-300 dark:hover:bg-sky-900/50 transition-colors">
          <i class="fa-brands fa-telegram text-xs"></i><span class="hidden sm:inline">Rapor</span>
        </button>

        <a href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$g['id'] ?>"
           class="ml-auto inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm">
          <i class="fa-solid fa-plus text-xs"></i><?= __('load_balance') ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ═══════════════════════════════════════════════════════ LIST VIEW -->
<div id="viewList" class="hidden flex flex-col gap-3 mb-8">
  <?php foreach ($groups as $index => $g): ?>
  <?php $isConnected = !empty($g['api_group_name']); $balance = (float)$g['balance']; ?>
  <div class="group-card bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-700/50 transition-all duration-200 overflow-hidden"
       data-name="<?= strtolower(htmlspecialchars($g['name'])) ?>">
    <div class="flex items-center gap-4 p-4">
      <!-- Avatar -->
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
        <?= strtoupper(mb_substr($g['name'], 0, 1)) ?>
      </div>

      <!-- Name + ID -->
      <div class="min-w-0 flex-1">
        <h3 class="font-semibold text-slate-800 dark:text-white truncate"><?= htmlspecialchars($g['name']) ?></h3>
        <span class="text-xs font-mono text-slate-400">#<?= (int)$g['id'] ?> <?= $isConnected ? '· '.htmlspecialchars($g['api_group_name']) : '' ?></span>
      </div>

      <!-- Status badge -->
      <span class="hidden sm:inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold flex-shrink-0
        <?= $isConnected ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' ?>">
        <i class="fa-solid fa-circle text-xs"></i><?= $isConnected ? __('api_connected_status') : __('not_connected') ?>
      </span>

      <!-- Balance -->
      <div class="text-right flex-shrink-0 hidden sm:block">
        <div class="font-bold text-lg <?= $balance <= 0 ? 'text-red-500' : ($balance < 10 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400') ?>">
          $<?= number_format($balance, 2) ?>
        </div>
        <div class="text-xs text-slate-400"><?= __('balance') ?></div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1.5 flex-shrink-0">
        <button onclick="showGroupDetails(<?= $index ?>)"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50 transition-colors" title="<?= __('detail') ?>">
          <i class="fa-solid fa-eye text-xs"></i>
        </button>
        <?php if ($isSuper): ?>
        <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors" title="<?= __('edit') ?>">
          <i class="fa-solid fa-pen text-xs"></i>
        </a>
        <button onclick="sendBalanceReport(<?= (int)$g['id'] ?>, this)"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-100 dark:bg-sky-900/30 dark:text-sky-300 dark:hover:bg-sky-900/50 transition-colors" title="Rapor Gönder">
          <i class="fa-brands fa-telegram text-xs"></i>
        </button>
        <a href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$g['id'] ?>"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-sm" title="<?= __('load_balance') ?>">
          <i class="fa-solid fa-plus text-xs"></i><?= __('load_balance') ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ═══════════════════════════════════════════════════════ TABLE VIEW -->
<div id="viewTable" class="hidden mb-8">
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('group_name') ?></th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('api_group') ?></th>
            <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('balance') ?></th>
            <?php if ($isSuper): ?>
            <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('margin_percent') ?></th>
            <?php endif; ?>
            <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('status') ?></th>
            <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= __('actions') ?></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
          <?php foreach ($groups as $index => $g): ?>
          <?php $isConnected = !empty($g['api_group_name']); $balance = (float)$g['balance']; ?>
          <tr class="group-card hover:bg-indigo-50/40 dark:hover:bg-slate-700/30 transition-colors"
              data-name="<?= strtolower(htmlspecialchars($g['name'])) ?>">
            <td class="px-4 py-3 font-mono text-xs text-slate-400"><?= (int)$g['id'] ?></td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                  <?= strtoupper(mb_substr($g['name'], 0, 1)) ?>
                </div>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($g['name']) ?></span>
              </div>
            </td>
            <td class="px-4 py-3 text-sm <?= $isConnected ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' ?>">
              <?= $isConnected ? htmlspecialchars($g['api_group_name']) : '—' ?>
            </td>
            <td class="px-4 py-3 text-right font-mono font-bold <?= $balance <= 0 ? 'text-red-500' : ($balance < 10 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400') ?>">
              $<?= number_format($balance, 2) ?>
            </td>
            <?php if ($isSuper): ?>
            <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">
              <?= isset($g['margin']) ? number_format((float)$g['margin'], 1).'%' : '—' ?>
            </td>
            <?php endif; ?>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                <?= $isConnected ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' ?>">
                <i class="fa-solid fa-circle text-xs"></i>
                <?= $isConnected ? __('api_connected_status') : __('not_connected') ?>
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center justify-end gap-1.5">
                <button onclick="showGroupDetails(<?= $index ?>)"
                        class="w-7 h-7 flex items-center justify-center rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 transition-colors" title="<?= __('detail') ?>">
                  <i class="fa-solid fa-eye text-xs"></i>
                </button>
                <?php if ($isSuper): ?>
                <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>"
                   class="w-7 h-7 flex items-center justify-center rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 transition-colors" title="<?= __('edit') ?>">
                  <i class="fa-solid fa-pen text-xs"></i>
                </a>
                <button onclick="sendBalanceReport(<?= (int)$g['id'] ?>, this)"
                        class="w-7 h-7 flex items-center justify-center rounded-md bg-sky-50 text-sky-600 hover:bg-sky-100 dark:bg-sky-900/30 dark:text-sky-300 transition-colors" title="Rapor">
                  <i class="fa-brands fa-telegram text-xs"></i>
                </button>
                <a href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$g['id'] ?>"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                  <i class="fa-solid fa-plus text-xs"></i><?= __('load_balance') ?>
                </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Empty State -->
<?php if (empty($groups)): ?>
<div class="flex flex-col items-center justify-center py-20 text-center">
  <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
    <i class="fa-solid fa-layer-group text-4xl text-slate-300 dark:text-slate-500"></i>
  </div>
  <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mb-1">Henüz grup yok</h3>
  <p class="text-slate-400 text-sm mb-4">İlk grubu oluşturmak için butona tıklayın</p>
  <?php if ($isSuper): ?>
  <a href="<?= \App\Helpers\Url::to('/groups/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
    <i class="fa-solid fa-plus"></i><?= __('new_group') ?>
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Group Details Modal -->
<div id="groupModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this)closeModal()">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
      <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
        <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg"><i class="fa-solid fa-layer-group text-indigo-600 dark:text-indigo-400"></i></span>
        <?= __('group_details') ?>
      </h3>
      <button onclick="closeModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    <div id="groupModalContent" class="p-5"></div>
  </div>
</div>

<script>
const groupsData = <?php echo json_encode($groups); ?>;

// ── View Toggle ────────────────────────────────────────────────────────────
const VIEWS = ['cards', 'list', 'table'];
const VIEW_IDS = { cards: 'viewCards', list: 'viewList', table: 'viewTable' };
const BTN_IDS  = { cards: 'btnViewCards', list: 'btnViewList', table: 'btnViewTable' };

function setView(view) {
  // Save preference
  try { localStorage.setItem('groupsView', view); } catch(e){}

  VIEWS.forEach(v => {
    const el = document.getElementById(VIEW_IDS[v]);
    const btn = document.getElementById(BTN_IDS[v]);
    if (v === view) {
      el.classList.remove('hidden');
      btn.classList.add('active');
    } else {
      el.classList.add('hidden');
      btn.classList.remove('active');
    }
  });
}

// Restore saved view
(function() {
  let saved = 'cards';
  try { saved = localStorage.getItem('groupsView') || 'cards'; } catch(e){}
  if (!VIEWS.includes(saved)) saved = 'cards';
  setView(saved);
})();

// ── Search / Filter ────────────────────────────────────────────────────────
document.getElementById('groupSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase().trim();
  document.querySelectorAll('.group-card').forEach(el => {
    const name = el.dataset.name || '';
    el.closest('tr') ? el.closest('tr').style.display = (!q || name.includes(q)) ? '' : 'none'
                     : el.style.display = (!q || name.includes(q)) ? '' : 'none';
  });
});

// ── Toast ──────────────────────────────────────────────────────────────────
function showToast(message, type) {
  const colors = { success: 'bg-emerald-600', error: 'bg-rose-600', loading: 'bg-sky-600' };
  const icons  = { success: 'fa-check-circle', error: 'fa-times-circle', loading: 'fa-spinner fa-spin' };
  let toast = document.getElementById('groupToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'groupToast';
    toast.className = 'fixed bottom-6 right-6 z-[60] flex items-center gap-3 px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl transition-all duration-300 translate-y-20 opacity-0';
    document.body.appendChild(toast);
  }
  toast.className = toast.className.replace(/bg-\w+-\d+/g, '').trim();
  toast.classList.add(colors[type] || 'bg-slate-700');
  toast.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info-circle'}"></i><span>${message}</span>`;
  requestAnimationFrame(() => { toast.classList.remove('translate-y-20','opacity-0'); toast.classList.add('translate-y-0','opacity-100'); });
  if (type !== 'loading') {
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.classList.add('translate-y-20','opacity-0'); toast.classList.remove('translate-y-0','opacity-100'); }, 4000);
  }
}

// ── Telegram Report ────────────────────────────────────────────────────────
function sendBalanceReport(groupId, btn) {
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
  showToast('Telegram bildirimi gönderiliyor...', 'loading');
  fetch('<?= \App\Helpers\Url::to('/groups/send-balance-report') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'group_id=' + groupId
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('✓ Telegram bildirimi gönderildi!', 'success');
      btn.innerHTML = '<i class="fa-solid fa-check"></i>';
      setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 3000);
    } else {
      showToast('✗ ' + (data.error || 'Gönderilemedi'), 'error');
      btn.innerHTML = orig; btn.disabled = false;
    }
  })
  .catch(err => {
    showToast('✗ Bağlantı hatası', 'error');
    btn.innerHTML = orig; btn.disabled = false;
  });
}

// ── Group Detail Modal ─────────────────────────────────────────────────────
function showGroupDetails(index) {
  const g = groupsData[index];
  if (!g) return;
  const isConnected = !!g.api_group_name;
  const balance = parseFloat(g.balance);
  const balanceClass = balance <= 0 ? 'text-red-500' : (balance < 10 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400');

  const rows = [
    ['<?= __('group_name') ?>', g.name, 'fa-layer-group text-indigo-500'],
    ['<?= __('group_id') ?>',   `<span class="font-mono text-indigo-600 dark:text-indigo-400">#${g.id}</span>`, 'fa-fingerprint text-slate-400'],
    ['<?= __('balance') ?>',    `<span class="font-mono font-bold ${balanceClass}">$${balance.toFixed(2)}</span>`, 'fa-wallet text-emerald-500'],
    ['<?= __('api_group') ?>', isConnected ? `<span class="text-blue-600 dark:text-blue-400">${g.api_group_name}</span>` : '<span class="text-slate-400"><?= __('not_matched') ?></span>', 'fa-plug text-blue-400'],
    ...(g.api_group_id ? [['<?= __('api_id') ?>', `<span class="font-mono text-blue-600 dark:text-blue-400">#${g.api_group_id}</span>`, 'fa-hashtag text-slate-400']] : []),
    ['<?= __('status') ?>', isConnected
      ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"><i class="fa-solid fa-circle text-xs"></i><?= __('connected') ?></span>`
      : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300"><i class="fa-solid fa-circle text-xs"></i><?= __('not_connected') ?></span>`,
      'fa-circle-info text-orange-400'],
  ];

  const tableRows = rows.map(([label, val, icon]) => `
    <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0">
      <td class="py-3 pr-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-2"><i class="fa-solid ${icon} w-4 text-center text-xs"></i>${label}</span>
      </td>
      <td class="py-3 text-sm font-medium text-slate-800 dark:text-slate-200">${val}</td>
    </tr>`).join('');

  document.getElementById('groupModalContent').innerHTML = `
    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-200 dark:border-slate-700">
      <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl shadow-md">
        ${g.name.charAt(0).toUpperCase()}
      </div>
      <div>
        <h4 class="text-xl font-bold text-slate-900 dark:text-white">${g.name}</h4>
        <p class="text-sm text-slate-500 dark:text-slate-400"><?= __('group_id') ?>: #${g.id}</p>
      </div>
    </div>
    <table class="w-full mb-5">${tableRows}</table>
    <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
      <a href="<?= \App\Helpers\Url::to('/groups/show') ?>?id=${g.id}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors text-sm">
        <i class="fa-solid fa-eye"></i><?= __('detailed_view') ?>
      </a>
      <?php if ($isSuper): ?>
      <a href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=${g.id}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold transition-colors text-sm">
        <i class="fa-solid fa-pen"></i><?= __('edit_group') ?>
      </a>
      <a href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=${g.id}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-colors text-sm">
        <i class="fa-solid fa-plus"></i><?= __('load_balance') ?>
      </a>
      <?php endif; ?>
    </div>
  `;

  const modal = document.getElementById('groupModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeModal() {
  const modal = document.getElementById('groupModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

<style>
.view-btn { color: #94a3b8; }
.dark .view-btn { color: #64748b; }
.view-btn:hover { background: #f1f5f9; color: #6366f1; }
.dark .view-btn:hover { background: #334155; color: #818cf8; }
.view-btn.active { background: #6366f1; color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.35); }
.dark .view-btn.active { background: #6366f1; color: #fff; }
</style>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>