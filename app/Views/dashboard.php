<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PapaM VoIP Panel</title>
    <link href="/assets/css/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">PapaM VoIP Panel Dashboard</h1>
        <div id="stats" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Bakiye: <span id="balance">...</span></div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Aktif Aramalar: <span id="active-calls">...</span></div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded shadow">Son İşlemler</div>
        </div>
        <canvas id="chart" height="100"></canvas>
        <button id="theme-toggle" class="mt-6 px-4 py-2 bg-gray-800 text-white rounded">Tema Değiştir</button>
    </div>
    <script src="/assets/js/chart.min.js"></script>
    <script>if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js';document.head.appendChild(s);}</script>
    <script src="/assets/js/dashboard.js"></script>
</body>
</html>
