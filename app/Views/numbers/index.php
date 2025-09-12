<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dış Numaralar - PapaM VoIP Panel</title>
  <link href="/assets/css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Dış Numaralar</h1>
      <div class="space-x-2">
        <a href="/" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="/logout" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
            <th class="p-2">Numara</th>
            <th class="p-2">Durum</th>
            <th class="p-2">İşlem</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (($numbers ?? []) as $n): $num=$n['number'] ?? ''; $st=strtolower($n['status'] ?? ''); ?>
          <tr class="border-b border-gray-100 dark:border-gray-700">
            <td class="p-2"><?= htmlspecialchars($num) ?></td>
            <td class="p-2 capitalize"><?= htmlspecialchars($st) ?></td>
            <td class="p-2 space-x-2">
              <form method="post" action="/numbers/active" style="display:inline">
                <input type="hidden" name="number" value="<?= htmlspecialchars($num) ?>">
                <button class="px-2 py-1 rounded bg-green-600 text-white">Active</button>
              </form>
              <form method="post" action="/numbers/spam" style="display:inline">
                <input type="hidden" name="number" value="<?= htmlspecialchars($num) ?>">
                <button class="px-2 py-1 rounded bg-yellow-600 text-white">Spam</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

