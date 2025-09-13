<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bakiye Yükle - PapaM VoIP Panel</title>
  <link href="<?= \App\Helpers\Url::to('/public/assets/css/tailwind.min.css') ?>" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4 max-w-2xl">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Bakiye</h1>
      <div class="space-x-2">
        <a href="<?= \App\Helpers\Url::to('/') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Dashboard</a>
        <a href="<?= \App\Helpers\Url::to('/users') ?>" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700">Kullanıcılar</a>
        <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-3 py-2 rounded bg-red-600 text-white">Çıkış</a>
      </div>
    </div>
    <div class="grid md:grid-cols-2 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h2 class="text-lg font-semibold mb-2">Mevcut Ana Bakiye</h2>
        <pre class="text-sm bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-auto"><?php echo htmlspecialchars(json_encode($balance, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
        <div class="mt-2 text-xs text-gray-500">Not: Ana bakiye API üzerinden sadece görüntülenir. Yükleme işlemi dış sistemden yapılır.</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded shadow p-4">
        <h2 class="text-lg font-semibold mb-2">Grup Bakiye Yönetimi</h2>
        <p class="text-sm">Gruplara bakiye eklemek için <a class="text-blue-600" href="/groups">Gruplar</a> sayfasından ilgili grupta “Bakiye Yükle” bölümünü kullanın.</p>
      </div>
    </div>
  </div>
</body>
</html>
