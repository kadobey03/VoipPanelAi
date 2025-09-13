<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gruplar - PapaM VoIP Panel</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Gruplar</h1>
      <div class="space-x-2">
        <a href="<?= \App\Helpers\Url::to('/') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="<?= \App\Helpers\Url::to('/users') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Kullanıcılar</a>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin'): ?>
        <a href="<?= \App\Helpers\Url::to('/groups/create') ?>" class="px-3 py-2 rounded bg-indigo-600 text-white">Yeni Grup</a>
        <?php endif; ?>
        <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
            <th class="p-2">ID</th>
            <th class="p-2">Ad</th>
            <th class="p-2">Margin %</th>
            <th class="p-2">Bakiye</th>
            <th class="p-2">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($groups as $g): ?>
          <tr class="border-b border-gray-100 dark:border-gray-700">
            <td class="p-2"><?= (int)$g['id'] ?></td>
            <td class="p-2"><?= htmlspecialchars($g['name']) ?></td>
            <td class="p-2"><?= number_format((float)$g['margin'],2) ?></td>
            <td class="p-2"><?= number_format((float)$g['balance'],2) ?></td>
            <td class="p-2 space-x-2">
              <a class="text-blue-600" href="<?= \App\Helpers\Url::to('/groups/show') ?>?id=<?= (int)$g['id'] ?>">Detay</a>
              <a class="text-indigo-600" href="<?= \App\Helpers\Url::to('/groups/edit') ?>?id=<?= (int)$g['id'] ?>">Düzenle</a>
              <a class="text-green-600" href="<?= \App\Helpers\Url::to('/groups/topup') ?>?id=<?= (int)$g['id'] ?>">Bakiye Yükle</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
