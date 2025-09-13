<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grup Oluştur - PapaM VoIP Panel</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4 max-w-lg">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Grup Oluştur</h1>
      <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Geri</a>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
    <?php endif; ?>
    <form method="post" class="space-y-3 bg-white dark:bg-gray-800 p-4 rounded shadow">
      <div>
        <label class="block text-sm mb-1">Ad</label>
        <input name="name" required class="w-full border rounded p-2 bg-white dark:bg-gray-800">
      </div>
      <div>
        <label class="block text-sm mb-1">Margin %</label>
        <input type="number" step="0.01" name="margin" class="w-full border rounded p-2 bg-white dark:bg-gray-800" value="0">
      </div>
      <div>
        <label class="block text-sm mb-1">Başlangıç Bakiye</label>
        <input type="number" step="0.01" name="balance" class="w-full border rounded p-2 bg-white dark:bg-gray-800" value="0">
      </div>
      <button class="w-full bg-blue-600 text-white rounded p-2">Oluştur</button>
    </form>
  </div>
</body>
</html>
