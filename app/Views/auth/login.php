<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - PapaM VoIP Panel</title>
    <link href="<?= \App\Helpers\Url::to('/public/assets/css/tailwind.min.css') ?>" rel="stylesheet">
    <meta name="color-scheme" content="light dark">
    <style>body{min-height:100vh}</style>
    <script>
      try{if(localStorage.getItem('theme')==='dark'){document.documentElement.classList.add('dark')}}catch(e){}
    </script>
    <script>
      document.addEventListener('DOMContentLoaded',function(){
        var t=document.getElementById('toggle-theme');
        if(t){t.addEventListener('click',function(){document.documentElement.classList.toggle('dark');localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light');});}
      });
    </script>
    <style>.dark .card{background:#1f2937;color:#e5e7eb}.card{background:#fff;color:#111827}</style>
  </head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 flex items-center justify-center">
  <div class="card w-full max-w-sm rounded shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">PapaM VoIP Panel</h1>
      <button id="toggle-theme" class="text-sm text-blue-600 dark:text-blue-400">Tema</button>
    </div>
    <?php if (!empty($error)): ?>
      <div class="mb-3 p-2 rounded bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-100 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="post" class="space-y-3">
      <div>
        <label class="block text-sm mb-1">Kullanıcı Adı</label>
        <input name="login" required class="w-full border rounded p-2 bg-white dark:bg-gray-800">
      </div>
      <div>
        <label class="block text-sm mb-1">Şifre</label>
        <input type="password" name="password" required class="w-full border rounded p-2 bg-white dark:bg-gray-800">
      </div>
      <button type="submit" class="w-full bg-blue-600 text-white rounded p-2">Giriş Yap</button>
    </form>
    <div class="mt-3 text-center text-xs text-gray-500">Kurulum: <?= \App\Helpers\Url::to('/install/') ?></div>
  </div>
</body>
</html>
