<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Çağrılar - PapaM VoIP Panel</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Çağrılar</h1>
      <div class="space-x-2">
        <a href="<?= \App\Helpers\Url::to('/') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Gruplar</a>
        <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
    <form method="post" action="<?= \App\Helpers\Url::to('/calls/sync') ?>" class="mb-4 bg-white dark:bg-gray-800 p-3 rounded shadow flex flex-wrap items-end gap-2">
      <div>
        <label class="block text-xs">Başlangıç</label>
        <input type="datetime-local" name="from" class="border rounded p-1 bg-white dark:bg-gray-900">
      </div>
      <div>
        <label class="block text-xs">Bitiş</label>
        <input type="datetime-local" name="to" class="border rounded p-1 bg-white dark:bg-gray-900">
      </div>
      <button class="px-3 py-2 bg-indigo-600 text-white rounded">CDR Senkronize</button>
    </form>
    <?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
      <table class="min-w-full text-xs md:text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
            <th class="p-2">Tarih</th>
            <th class="p-2">Src</th>
            <th class="p-2">Dst</th>
            <th class="p-2">Süre</th>
            <th class="p-2">Billsec</th>
            <th class="p-2">Durum</th>
            <th class="p-2">Grup</th>
            <th class="p-2">Cost(API)</th>
            <th class="p-2">Margin%</th>
            <th class="p-2">Tahsil</th>
            <th class="p-2">Kayıt</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($calls as $c): ?>
          <tr class="border-b border-gray-100 dark:border-gray-700">
            <td class="p-2 whitespace-nowrap"><?= htmlspecialchars($c['start']) ?></td>
            <td class="p-2"><?= htmlspecialchars($c['src']) ?></td>
            <td class="p-2"><?= htmlspecialchars($c['dst']) ?></td>
            <td class="p-2"><?= (int)$c['duration'] ?></td>
            <td class="p-2"><?= (int)$c['billsec'] ?></td>
            <td class="p-2"><?= htmlspecialchars($c['disposition']) ?></td>
            <td class="p-2"><?= (int)$c['group_id'] ?></td>
            <td class="p-2"><?= number_format((float)$c['cost_api'],6) ?></td>
            <td class="p-2"><?= number_format((float)$c['margin_percent'],2) ?></td>
            <td class="p-2"><?= number_format((float)$c['amount_charged'],6) ?></td>
            <td class="p-2">
              <?php if (!empty($c['call_id'])): ?>
                <a class="text-blue-600" href="<?= \App\Helpers\Url::to('/calls/record') ?>?call_id=<?= urlencode($c['call_id']) ?>" target="_blank">Dinle</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
