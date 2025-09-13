<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PapaM VoIP Panel</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">PapaM VoIP Panel Dashboard</h1>

    <div id="stats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Ana Bakiye (API): <span id="balance"><?= isset($balanceValue) && $balanceValue!==null ? htmlspecialchars((string)$balanceValue) : '...' ?></span></div>
      <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Gruplar: <a class="text-blue-600" href="<?= \App\Helpers\Url::to('/groups') ?>">Görüntüle</a></div>
      <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Çağrılar: <a class="text-blue-600" href="<?= \App\Helpers\Url::to('/calls') ?>">Görüntüle</a></div>
    </div>

    <div class="mt-6 flex gap-2 flex-wrap">
      <a href="<?= \App\Helpers\Url::to('/users') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Kullanıcılar</a>
      <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Gruplar</a>
      <a href="<?= \App\Helpers\Url::to('/reports') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Raporlar</a>
      <a href="<?= \App\Helpers\Url::to('/agents') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Agent Durum</a>
      <a href="<?= \App\Helpers\Url::to('/numbers') ?>" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Dış Numaralar</a>
      <a href="<?= \App\Helpers\Url::to('/balance') ?>" class="px-4 py-2 bg-blue-600 text-white rounded">Ana Bakiye</a>
      <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="px-4 py-2 bg-red-600 text-white rounded">Çıkış</a>
      <button id="theme-toggle" class="px-4 py-2 bg-gray-800 text-white rounded">Tema</button>
    </div>
  </div>

  <script>
    // Tema toggle
    (function(){
      var btn = document.getElementById('theme-toggle');
      if(btn){ btn.addEventListener('click', function(){ document.documentElement.classList.toggle('dark'); }); }
    })();
  </script>
  <script src="<?= \App\Helpers\Url::to('/public/assets/js/chart.min.js') ?>"></script>
  <script>if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js';document.head.appendChild(s);}</script>
  <script src="<?= \App\Helpers\Url::to('/public/assets/js/dashboard.js') ?>"></script>
</body>
</html>
