<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Durumları - PapaM VoIP Panel</title>
  <link href="<?= \App\Helpers\Url::to('/public/assets/css/tailwind.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Agent Durumları</h1>
      <div class="space-x-2">
        <a href="<?= \App\Helpers\Url::to('/') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
            <th class="p-2">Exten</th>
            <th class="p-2">Login</th>
            <th class="p-2">Durum</th>
            <th class="p-2">Son Çağrı</th>
            <th class="p-2">Lead</th>
            <th class="p-2">Grup</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (($agents ?? []) as $a): ?>
          <tr class="border-b border-gray-100 dark:border-gray-700">
            <td class="p-2"><?= htmlspecialchars($a['exten'] ?? '') ?></td>
            <td class="p-2"><?= htmlspecialchars($a['user_login'] ?? '') ?></td>
            <td class="p-2"><?= htmlspecialchars($a['status'] ?? '') ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($a['las_call_time'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars($a['lead'] ?? '') ?></td>
            <td class="p-2"><?= htmlspecialchars($a['group'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
