<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bakiye Yükle - PapaM VoIP Panel</title>
  <link href="/assets/css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4 max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Bakiye</h1>
      <div class="space-x-2">
        <a href="/" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="/users" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Kullanıcılar</a>
        <a href="/logout" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h2 class="text-lg font-semibold mb-2">Mevcut Bakiye</h2>
        <pre class="text-sm bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-auto"><?php echo htmlspecialchars(json_encode($balance, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h2 class="text-lg font-semibold mb-2">Bakiye Yükle</h2>
        <?php if (!empty($message)): ?>
          <div class="mb-2 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
          <div class="mb-2 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="space-y-3">
          <div>
            <label class="block text-sm mb-1">Tutar</label>
            <input type="number" name="amount" step="0.01" min="0" class="w-full border rounded p-2 bg-white dark:bg-gray-800" required>
          </div>
          <button class="w-full bg-blue-600 text-white rounded p-2">Yükle</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

