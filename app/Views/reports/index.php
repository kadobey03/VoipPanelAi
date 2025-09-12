<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Raporlar - PapaM VoIP Panel</title>
  <link href="/assets/css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Raporlar</h1>
      <div class="space-x-2">
        <a href="/" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="/groups" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Gruplar</a>
        <a href="/calls" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Çağrılar</a>
        <a href="/logout" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>

    <form method="get" class="mb-4 bg-white dark:bg-gray-800 p-3 rounded shadow flex flex-wrap items-end gap-2">
      <div>
        <label class="block text-xs">Başlangıç</label>
        <input type="datetime-local" name="from" class="border rounded p-1 bg-white dark:bg-gray-900" value="<?= htmlspecialchars(str_replace(' ', 'T', substr($from,0,16))) ?>">
      </div>
      <div>
        <label class="block text-xs">Bitiş</label>
        <input type="datetime-local" name="to" class="border rounded p-1 bg-white dark:bg-gray-900" value="<?= htmlspecialchars(str_replace(' ', 'T', substr($to,0,16))) ?>">
      </div>
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
      <div>
        <label class="block text-xs">Grup</label>
        <input type="number" name="group_id" class="border rounded p-1 bg-white dark:bg-gray-900" value="<?= isset($_GET['group_id'])? (int)$_GET['group_id'] : '' ?>" placeholder="(hepsi)">
      </div>
      <?php endif; ?>
      <button class="px-3 py-2 bg-indigo-600 text-white rounded">Filtrele</button>
    </form>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
      <?php foreach ($summary as $row): $gid=(int)$row['group_id']; ?>
      <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <div class="text-sm text-gray-500">Grup</div>
        <div class="text-lg font-semibold mb-2"><?= htmlspecialchars($groups[$gid] ?? ('#'.$gid)) ?></div>
        <div class="text-xs text-gray-400">Çağrı: <?= (int)$row['calls'] ?> | Süre: <?= (int)$row['billsec'] ?>s</div>
        <div class="mt-2 text-sm">Maliyet: <strong><?= number_format((float)$row['cost_api'],2) ?></strong></div>
        <div class="text-sm">Gelir: <strong><?= number_format((float)$row['revenue'],2) ?></strong></div>
        <div class="text-sm">Kâr: <strong><?= number_format((float)$row['profit'],2) ?></strong></div>
      </div>
      <?php endforeach; ?>
    </div>

  <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
      <div class="text-lg font-semibold mb-2">Günlük Trend</div>
      <canvas id="trend" height="120"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded shadow p-4 mt-6">
      <div class="text-lg font-semibold mb-2">API Call Plane (Kullanıcı Bazlı)</div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
              <th class="p-2">Login</th>
              <th class="p-2">Grup</th>
              <th class="p-2">Çağrı</th>
              <th class="p-2">Cevap</th>
              <th class="p-2">Billsec</th>
              <th class="p-2">Cost</th>
              <th class="p-2">Exten</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($callStat ?? []) as $r): ?>
            <tr class="border-b border-gray-100 dark:border-gray-700">
              <td class="p-2"><?= htmlspecialchars($r['user_login'] ?? '') ?></td>
              <td class="p-2"><?= htmlspecialchars($r['group_name'] ?? '') ?></td>
              <td class="p-2"><?= (int)($r['calls'] ?? 0) ?></td>
              <td class="p-2"><?= (int)($r['answer'] ?? 0) ?></td>
              <td class="p-2"><?= (int)($r['billsec'] ?? 0) ?></td>
              <td class="p-2"><?= number_format((float)($r['cost'] ?? 0),2) ?></td>
              <td class="p-2"><?= htmlspecialchars($r['voip_exten'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="/assets/js/chart.min.js"></script>
  <script>
    const labels = <?= json_encode(array_map(function($t){return $t['d'];}, $trend ?? []), JSON_UNESCAPED_UNICODE) ?>;
    const cost = <?= json_encode(array_map(function($t){return (float)$t['cost'];}, $trend ?? [])) ?>;
    const revenue = <?= json_encode(array_map(function($t){return (float)$t['revenue'];}, $trend ?? [])) ?>;
    const ctx = document.getElementById('trend').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {label:'Maliyet', data: cost, borderColor: 'rgba(239,68,68,1)', backgroundColor:'rgba(239,68,68,0.2)', tension:.2},
          {label:'Gelir', data: revenue, borderColor: 'rgba(16,185,129,1)', backgroundColor:'rgba(16,185,129,0.2)', tension:.2}
        ]
      },
      options: {responsive:true, scales:{y:{beginAtZero:true}}}
    });
  </script>
</body>
</html>
