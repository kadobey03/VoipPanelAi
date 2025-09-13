<?php $title='CDR Geçmişi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
<?php $isGroupMember = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='groupmember'; ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
  <div class="flex items-center gap-3">
    <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl shadow-lg">
      <i class="fa-solid fa-table-list text-white text-xl"></i>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">CDR Geçmişi</h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">Çağrı detay kayıtlarını görüntüleyin</p>
    </div>
  </div>

  <!-- Export Button -->
  <div class="flex gap-3">
    <button onclick="exportToExcel()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-200">
      <i class="fa-solid fa-download"></i>
      <span class="hidden sm:inline">Excel İndir</span>
    </button>
  </div>
</div>

<!-- Filter Form -->
<div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6 mb-6">
  <form method="get" action="<?= \App\Helpers\Url::to('/calls/history') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-calendar-days mr-2 text-indigo-500"></i>Başlangıç
      </label>
      <input type="datetime-local" name="from" value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-d\TH:i', strtotime('-1 day'))) ?>"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-calendar-days mr-2 text-indigo-500"></i>Bitiş
      </label>
      <input type="datetime-local" name="to" value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d\TH:i')) ?>"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-phone mr-2 text-emerald-500"></i>Src
      </label>
      <input name="src" value="<?= htmlspecialchars($_GET['src'] ?? '') ?>" placeholder="aramayı başlatan"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-phone mr-2 text-blue-500"></i>Dst
      </label>
      <input name="dst" value="<?= htmlspecialchars($_GET['dst'] ?? '') ?>" placeholder="aranan"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-list-ol mr-2 text-purple-500"></i>Sayfa
      </label>
      <input type="number" min="1" name="page" value="<?= (int)($_GET['page'] ?? 1) ?>"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
    </div>

    <div class="space-y-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-hashtag mr-2 text-orange-500"></i>Adet
      </label>
      <input type="number" min="10" max="200" name="per" value="<?= (int)($_GET['per'] ?? 100) ?>"
             class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200">
    </div>

    <?php if ($isSuper): ?>
    <div class="space-y-2 xl:col-span-2">
      <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
        <i class="fa-solid fa-users mr-2 text-rose-500"></i>Grup
      </label>
      <select name="group_id" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200">
        <option value="">Tümü</option>
        <?php foreach (($groups ?? []) as $g): $gid=(int)$g['id']; ?>
          <option value="<?= $gid ?>" <?= (isset($_GET['group_id']) && (int)$_GET['group_id']===$gid)?'selected':'' ?>><?= htmlspecialchars($g['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="flex items-end xl:col-span-2">
      <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-indigo-500/25 transform hover:scale-105 transition-all duration-200">
        <i class="fa-solid fa-magnifying-glass mr-2"></i>Ara
      </button>
    </div>
  </form>
</div>

  <!-- Data Table -->
  <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/50 dark:to-slate-800/50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-calendar-days mr-2 text-indigo-500"></i>Tarih
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-phone mr-2 text-emerald-500"></i>Src
            </th>
            <?php if ($isSuper): ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-users mr-2 text-blue-500"></i>Grup
            </th>
            <?php endif; ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-phone mr-2 text-purple-500"></i>Dst
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-info-circle mr-2 text-orange-500"></i>Durum
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-clock mr-2 text-gray-500"></i>Süre
            </th>
            <?php if (!$isGroupMember): ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-stopwatch mr-2 text-red-500"></i>Billsec
            </th>
            <?php endif; ?>
            <?php if ($isSuper): ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-dollar-sign mr-2 text-green-500"></i>Cost(API)
            </th>
            <?php endif; ?>
            <?php if ($isSuper): ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-percentage mr-2 text-yellow-500"></i>Margin%
            </th>
            <?php endif; ?>
            <?php if (!$isGroupMember): ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-coins mr-2 text-cyan-500"></i>Tahsil
            </th>
            <?php endif; ?>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-headphones mr-2 text-pink-500"></i>Kayıt
            </th>
            <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
              <i class="fa-solid fa-eye mr-2 text-violet-500"></i>Detay
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
          <?php if (!empty($calls ?? [])): ?>
            <?php foreach ($calls as $index => $c): ?>
            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-all duration-200 group">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                <div class="flex items-center">
                  <div class="text-sm font-medium">
                    <?= date('d.m.Y', strtotime($c['start'])) ?>
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 ml-1">
                    <?= date('H:i:s', strtotime($c['start'])) ?>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-emerald-600 dark:text-emerald-400 font-semibold">
                <?= htmlspecialchars($c['src']) ?>
              </td>
              <?php if ($isSuper): ?>
                <?php $gid=(int)$c['group_id']; $gn = isset($groupNamesById[$gid]) ? $groupNamesById[$gid] : (isset($groupNamesByApi[$gid]) ? $groupNamesByApi[$gid] : ('#'.$gid)); ?>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                    <?= htmlspecialchars($gn) ?>
                  </span>
                </td>
              <?php endif; ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-purple-600 dark:text-purple-400 font-semibold">
                <?= htmlspecialchars($c['dst']) ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  <?php
                  $disp = strtoupper($c['disposition']);
                  if ($disp === 'ANSWERED') {
                    echo 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                  } elseif ($disp === 'NO ANSWER' || $disp === 'BUSY') {
                    echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                  } elseif ($disp === 'FAILED') {
                    echo 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300';
                  } else {
                    echo 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
                  }
                  ?>">
                  <i class="fa-solid fa-circle mr-1 text-xs"></i>
                  <?= htmlspecialchars($c['disposition']) ?>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                <div class="flex items-center">
                  <i class="fa-solid fa-clock text-gray-400 mr-2"></i>
                  <?= gmdate('i:s', (int)$c['duration']) ?>
                </div>
              </td>
              <?php if (!$isGroupMember): ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-white">
                <div class="flex items-center">
                  <i class="fa-solid fa-stopwatch text-red-400 mr-2"></i>
                  <?= gmdate('i:s', (int)$c['billsec']) ?>
                </div>
              </td>
              <?php endif; ?>
              <?php if ($isSuper): ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-green-600 dark:text-green-400">
                $<?= number_format((float)$c['cost_api'], 6) ?>
              </td>
              <?php endif; ?>
              <?php if ($isSuper): ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium
                  <?php
                  $margin = (float)$c['margin_percent'];
                  if ($margin > 50) {
                    echo 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300';
                  } elseif ($margin > 20) {
                    echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
                  } else {
                    echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                  }
                  ?>">
                  <?= number_format($margin, 2) ?>%
                </span>
              </td>
              <?php endif; ?>
              <?php if (!$isGroupMember): ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-cyan-600 dark:text-cyan-400">
                $<?= number_format((float)$c['amount_charged'], 6) ?>
              </td>
              <?php endif; ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <?php if (!empty($c['call_id']) && strtoupper($c['disposition'])==='ANSWERED'): ?>
                  <button onclick="playAudio('<?= htmlspecialchars($c['call_id']) ?>')"
                          class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-pink-100 text-pink-800 hover:bg-pink-200 dark:bg-pink-900/50 dark:text-pink-300 dark:hover:bg-pink-900/70 transition-colors duration-200">
                    <i class="fa-solid fa-play mr-1"></i>Dinle
                  </button>
                <?php else: ?>
                  <span class="text-slate-400 dark:text-slate-600 text-xs">-</span>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button onclick="showCallDetails(<?= $index ?>)"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-violet-100 text-violet-800 hover:bg-violet-200 dark:bg-violet-900/50 dark:text-violet-300 dark:hover:bg-violet-900/70 transition-colors duration-200">
                  <i class="fa-solid fa-eye mr-1"></i>Detay
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= $isSuper ? 12 : 9 ?>" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center">
                  <i class="fa-solid fa-inbox text-4xl text-slate-400 dark:text-slate-600 mb-4"></i>
                  <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-1">Kayıt Bulunamadı</h3>
                  <p class="text-slate-500 dark:text-slate-400">Belirtilen kriterlere uygun çağrı bulunamadı.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Pagination -->
  <?php $page = (int)($_GET['page'] ?? 1); $per=(int)($_GET['per'] ?? 100); $totalPages = $totalPages ?? 1; ?>
  <?php if ($totalPages > 1): ?>
  <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
      <span>Toplam <?= number_format($totalCalls ?? 0) ?> kayıt</span>
      <span>•</span>
      <span>Sayfa <?= number_format($page) ?> / <?= number_format($totalPages) ?></span>
    </div>

    <div class="flex items-center gap-2">
      <?php if ($page > 1): $q=$_GET; $q['page']=1; ?>
        <a class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors duration-200" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>" title="İlk Sayfa">
          <i class="fa-solid fa-angles-left"></i>
        </a>
      <?php endif; ?>

      <?php if ($page > 1): $q=$_GET; $q['page']=$page-1; ?>
        <a class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors duration-200" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>" title="Önceki Sayfa">
          <i class="fa-solid fa-chevron-left"></i>
        </a>
      <?php endif; ?>

      <!-- Page Numbers -->
      <?php
      $startPage = max(1, $page - 2);
      $endPage = min($totalPages, $page + 2);
      for ($i = $startPage; $i <= $endPage; $i++):
        $q = $_GET; $q['page'] = $i;
        $isActive = $i === $page;
      ?>
        <a class="px-4 py-2 rounded-lg font-medium transition-colors duration-200 <?= $isActive ? 'bg-indigo-600 text-white' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300' ?>"
           href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): $q=$_GET; $q['page']=$page+1; ?>
        <a class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors duration-200" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>" title="Sonraki Sayfa">
          <i class="fa-solid fa-chevron-right"></i>
        </a>
      <?php endif; ?>

      <?php if ($page < $totalPages): $q=$_GET; $q['page']=$totalPages; ?>
        <a class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 transition-colors duration-200" href="<?= \App\Helpers\Url::to('/calls/history').'?'.http_build_query($q) ?>" title="Son Sayfa">
          <i class="fa-solid fa-angles-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Call Details Modal -->
  <div id="callModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">
            <i class="fa-solid fa-phone mr-2 text-indigo-500"></i>Çağrı Detayları
          </h3>
          <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <i class="fa-solid fa-times text-xl"></i>
          </button>
        </div>

        <div id="modalContent" class="p-6">
          <!-- Modal content will be populated by JavaScript -->
        </div>
      </div>
    </div>
  </div>

  <!-- Audio Player Modal -->
  <div id="audioModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full">
        <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
          <h3 class="text-xl font-bold text-slate-900 dark:text-white">
            <i class="fa-solid fa-headphones mr-2 text-pink-500"></i>Çağrı Kaydı
          </h3>
          <button onclick="closeAudioModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <i class="fa-solid fa-times text-xl"></i>
          </button>
        </div>

        <div class="p-6">
          <audio id="audioPlayer" controls class="w-full">
            <p>Tarayıcınız audio elementini desteklemiyor.</p>
          </audio>
        </div>
      </div>
    </div>
  </div>

  <script>
  // Store calls data for modal
  const callsData = <?php echo json_encode($calls ?? []); ?>;

  // Modal functions
  function showCallDetails(index) {
    const call = callsData[index];
    if (!call) return;

    const modalContent = document.getElementById('modalContent');
    const dispositionColor = getDispositionColor(call.disposition);

    modalContent.innerHTML = `
      <div class="space-y-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Başlangıç</div>
            <div class="font-semibold text-slate-900 dark:text-white">${formatDateTime(call.start)}</div>
          </div>
          <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4">
            <div class="text-sm text-slate-500 dark:text-slate-400 mb-1">Süre</div>
            <div class="font-semibold text-slate-900 dark:text-white">${formatDuration(call.duration)}</div>
          </div>
        </div>

        <!-- Call Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Çağrı Bilgileri</div>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Kaynak (Src):</span>
                <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">${call.src}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Hedef (Dst):</span>
                <span class="font-mono font-semibold text-purple-600 dark:text-purple-400">${call.dst}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Durum:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${dispositionColor.class}">
                  <i class="fa-solid fa-circle mr-1 text-xs"></i>${call.disposition}
                </span>
              </div>
              ${call.call_id ? `
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Çağrı ID:</span>
                <span class="font-mono text-xs text-slate-500 dark:text-slate-400">${call.call_id}</span>
              </div>
              ` : ''}
            </div>
          </div>

          <div>
            <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Teknik Detaylar</div>
            <div class="space-y-3">
              ${<?php echo $isGroupMember ? 'false' : 'true'; ?> ? `
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Billsec:</span>
                <span class="font-semibold text-red-600 dark:text-red-400">${formatDuration(call.billsec)}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Tahsil:</span>
                <span class="font-mono font-semibold text-cyan-600 dark:text-cyan-400">$${parseFloat(call.amount_charged || 0).toFixed(6)}</span>
              </div>
              ` : ''}
              ${<?php echo $isSuper ? 'true' : 'false'; ?> ? `
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Cost (API):</span>
                <span class="font-mono font-semibold text-green-600 dark:text-green-400">$${parseFloat(call.cost_api || 0).toFixed(6)}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-600 dark:text-slate-400">Margin %:</span>
                <span class="font-semibold text-yellow-600 dark:text-yellow-400">${parseFloat(call.margin_percent || 0).toFixed(2)}%</span>
              </div>
              ` : ''}
            </div>
          </div>
        </div>

        <!-- Actions -->
        ${call.call_id && call.disposition.toUpperCase() === 'ANSWERED' ? `
        <div class="flex justify-center pt-4 border-t border-slate-200 dark:border-slate-700">
          <button onclick="playAudio('${call.call_id}'); closeModal();"
                  class="inline-flex items-center px-6 py-3 rounded-lg bg-pink-600 hover:bg-pink-700 text-white font-medium transition-colors duration-200">
            <i class="fa-solid fa-play mr-2"></i>Çağrıyı Dinle
          </button>
        </div>
        ` : ''}
      </div>
    `;

    document.getElementById('callModal').classList.remove('hidden');
  }

  function closeModal() {
    document.getElementById('callModal').classList.add('hidden');
  }

  function playAudio(callId) {
    const audioUrl = `<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=${callId}`;
    const audioPlayer = document.getElementById('audioPlayer');
    audioPlayer.src = audioUrl;
    audioPlayer.load();

    document.getElementById('audioModal').classList.remove('hidden');
  }

  function closeAudioModal() {
    document.getElementById('audioModal').classList.add('hidden');
    const audioPlayer = document.getElementById('audioPlayer');
    audioPlayer.pause();
    audioPlayer.src = '';
  }

  function getDispositionColor(disposition) {
    const disp = disposition.toUpperCase();
    if (disp === 'ANSWERED') {
      return { class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' };
    } else if (disp === 'NO ANSWER' || disp === 'BUSY') {
      return { class: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' };
    } else if (disp === 'FAILED') {
      return { class: 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300' };
    } else {
      return { class: 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300' };
    }
  }

  function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleDateString('tr-TR') + ' ' + date.toLocaleTimeString('tr-TR');
  }

  function formatDuration(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }

  // Export to Excel function
  function exportToExcel() {
    const calls = <?php echo json_encode($calls ?? []); ?>;

    if (calls.length === 0) {
      alert('Dışa aktarılacak veri bulunamadı.');
      return;
    }

    // Create CSV content
    let csvContent = 'Tarih,Src,';
    <?php if ($isSuper): ?>csvContent += 'Grup,';<?php endif; ?>
    csvContent += 'Dst,Durum,Süre';
    <?php if (!$isGroupMember): ?>csvContent += ',Billsec,Tahsil';<?php endif; ?>
    <?php if ($isSuper): ?>csvContent += ',Cost_API,Margin_Percent';<?php endif; ?>
    csvContent += '\n';

    calls.forEach(call => {
      csvContent += `"${call.start}","${call.src}",`;
      <?php if ($isSuper): ?>
        const groupId = parseInt(call.group_id);
        const groupName = <?= json_encode($groupNamesById ?? []) ?>[groupId] ||
                         <?= json_encode($groupNamesByApi ?? []) ?>[groupId] ||
                         `#${groupId}`;
        csvContent += `"${groupName}",`;
      <?php endif; ?>
      csvContent += `"${call.dst}","${call.disposition}","${formatDuration(call.duration)}"`;
      <?php if (!$isGroupMember): ?>csvContent += `,"${formatDuration(call.billsec)}","${call.amount_charged || 0}"`;<?php endif; ?>
      <?php if ($isSuper): ?>csvContent += `,"${call.cost_api || 0}","${call.margin_percent || 0}"`;<?php endif; ?>
      csvContent += '\n';
    });

    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `cdr_export_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Close modals when clicking outside
  document.addEventListener('click', function(event) {
    if (event.target.id === 'callModal') {
      closeModal();
    }
    if (event.target.id === 'audioModal') {
      closeAudioModal();
    }
  });

  // Close modals with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      closeModal();
      closeAudioModal();
    }
  });
  </script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>


