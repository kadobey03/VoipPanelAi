<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kullanıcılar - PapaM VoIP Panel</title>
  <link href="/assets/css/tailwind.min.css" rel="stylesheet">
  <meta name="color-scheme" content="light dark">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Kullanıcılar</h1>
      <div class="space-x-2">
        <a href="<?= \App\Helpers\Url::to('/') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="<?= \App\Helpers\Url::to('/users/create') ?>" class="px-3 py-2 rounded bg-blue-600 text-white">Yeni Kullanıcı</a>
        <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
            <th class="p-2">ID</th>
            <th class="p-2">Kullanıcı</th>
            <th class="p-2">Rol</th>
            <th class="p-2">Exten</th>
            <th class="p-2">Grup</th>
            <th class="p-2">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr class="border-b border-gray-100 dark:border-gray-700">
            <td class="p-2"><?= (int)$u['id'] ?></td>
            <td class="p-2"><?= htmlspecialchars($u['login']) ?></td>
            <td class="p-2"><?= htmlspecialchars($u['role']) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)($u['exten'] ?? '')) ?></td>
            <td class="p-2"><?= htmlspecialchars((string)$u['group_id']) ?></td>
            <td class="p-2 space-x-2">
              <a class="text-blue-600" href="<?= \App\Helpers\Url::to('/users/edit') ?>?id=<?= (int)$u['id'] ?>">Düzenle</a>
              <?php if (isset($_SESSION['user']) && $_SESSION['user']['role']==='superadmin' && (int)$u['id']>1): ?>
              <form method="post" action="<?= \App\Helpers\Url::to('/users/delete') ?>" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="text-red-600">Sil</button>
              </form>
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
