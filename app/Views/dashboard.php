<?php
$title='Dashboard - PapaM VoIP Panel';
require __DIR__.'/partials/header.php';

$isSuper = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '')==='superadmin');
$isGroupMember = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '')==='groupmember');
$user = $_SESSION['user'] ?? [];
$currentHour = (int)date('H');
$greeting = $currentHour < 12 ? 'Günaydın' : ($currentHour < 18 ? 'İyi günler' : 'İyi akşamlar');
?>

<!-- Hero Section -->
<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600 mb-8 text-white">
  <!-- Background Pattern -->
  <div class="absolute inset-0 bg-black/10"></div>
  <div class="absolute inset-0 opacity-10">
    <div class="absolute top-10 left-10 w-32 h-32 bg-white/20 rounded-full blur-xl"></div>
    <div class="absolute bottom-10 right-10 w-48 h-48 bg-white/20 rounded-full blur-2xl"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
  </div>

  <!-- Content -->
  <div class="relative px-8 py-12 lg:px-12 lg:py-16">
    <div class="max-w-4xl mx-auto text-center">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
        <i class="fa-solid fa-wave-square text-3xl animate-pulse"></i>
      </div>

      <h1 class="text-4xl lg:text-5xl font-bold mb-4">
        <?= $greeting ?>, <span class="text-yellow-300"><?= htmlspecialchars($user['login'] ?? '') ?></span>!
      </h1>

      <p class="text-xl lg:text-2xl text-white/80 mb-8 font-light">
        PapaM VoIP Panel kontrol paneline hoş geldiniz
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm">
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
          <i class="fa-solid fa-calendar-days text-yellow-300"></i>
          <span><?= date('d.m.Y') ?></span>
        </div>
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
          <i class="fa-solid fa-clock text-green-300"></i>
          <span id="currentTime"><?= date('H:i:s') ?></span>
        </div>
        <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
          <i class="fa-solid fa-shield text-blue-300"></i>
          <span><?= ucfirst($user['role'] ?? '') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Decorative Elements -->
  <div class="absolute bottom-0 left-0 right-0 h-2 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
</section>

<!-- Stats Cards -->
<section class="mb-8">
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
    <?php if ($isSuper): ?>
    <!-- Ana Bakiye -->
    <div class="group relative bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-wallet text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Ana Bakiye (API)</div>
            <div class="text-2xl font-bold" id="balance">
              <?= isset($balanceValue) && $balanceValue!==null ? '$' . number_format((float)$balanceValue, 2) : '...' ?>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Güncel Durum</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-circle text-green-400 mr-1 text-xs"></i>Aktif
          </span>
        </div>
      </div>
    </div>

    <!-- Gruplar Toplam -->
    <div class="group relative bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-layer-group text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Gruplar Toplam</div>
            <div class="text-2xl font-bold">$<?= number_format((float)($groupsTotal ?? 0), 2) ?></div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Tüm Gruplar</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-users text-blue-300 mr-1 text-xs"></i>Toplam
          </span>
        </div>
      </div>
    </div>

    <!-- Fark -->
    <div class="group relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-scale-balanced text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Fark (Ana - Gruplar)</div>
            <div class="text-2xl font-bold">
              <?= isset($diff) && $diff!==null ? '$' . number_format((float)$diff, 2) : '...' ?>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Bakiye Farkı</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-chart-line text-orange-300 mr-1 text-xs"></i>Analiz
          </span>
        </div>
      </div>
    </div>

    <!-- Haftalık Kâr -->
    <div class="group relative bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-chart-trend-up text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Haftalık Kâr</div>
            <div class="text-2xl font-bold">$<?= number_format((float)($weeklyProfit ?? 0), 2) ?></div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Son 7 Gün</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-arrow-trend-up text-green-300 mr-1 text-xs"></i>Yükseliş
          </span>
        </div>
      </div>
    </div>

    <?php elseif (!$isGroupMember): ?>
    <!-- Grup Bakiyesi -->
    <div class="group relative bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-piggy-bank text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Grup Bakiyesi</div>
            <div class="text-2xl font-bold">$<?= number_format((float)($ownGroupBalance ?? 0), 2) ?></div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Mevcut Bakiye</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-wallet text-teal-300 mr-1 text-xs"></i>Aktif
          </span>
        </div>
      </div>
    </div>

    <!-- Grubum Link -->
    <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="group relative bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden block">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-layer-group text-2xl"></i>
          </div>
          <div class="flex items-center text-right">
            <i class="fa-solid fa-arrow-right text-white/60 ml-2 group-hover:text-white transition-colors duration-300"></i>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm opacity-80">Grubum</div>
            <div class="text-lg font-bold">Görüntüle</div>
          </div>
          <div class="p-2 bg-white/20 rounded-lg group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-external-link-alt text-sm"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Haftalık Harcama -->
    <div class="group relative bg-gradient-to-br from-rose-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-rose-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-sack-dollar text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Bu Hafta Harcama</div>
            <div class="text-2xl font-bold">$<?= number_format((float)($weeklyRevenue ?? 0), 2) ?></div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm opacity-80">Son 7 Gün</span>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-white/20">
            <i class="fa-solid fa-chart-pie text-pink-300 mr-1 text-xs"></i>Harcama
          </span>
        </div>
      </div>
    </div>

    <!-- Boş Kart -->
    <div class="group relative bg-gradient-to-br from-slate-500 to-gray-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-slate-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10 flex items-center justify-center h-full">
        <div class="text-center">
          <div class="p-4 bg-white/20 rounded-full mb-4 inline-block">
            <i class="fa-solid fa-plus text-2xl"></i>
          </div>
          <div class="text-sm opacity-80">Daha Fazla Metrik</div>
          <div class="text-lg font-bold">Yakında</div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Quick Access Cards -->
<section class="mb-8">
  <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
    <i class="fa-solid fa-bolt text-yellow-500"></i>
    Hızlı Erişim
  </h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php if (!$isGroupMember): ?>
    <!-- Kullanıcılar -->
    <a href="<?= \App\Helpers\Url::to('/users') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-users text-white text-xl"></i>
          </div>
          <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-indigo-600 dark:text-indigo-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Kullanıcılar</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Kullanıcı yönetimi</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>
    <?php endif; ?>

    <!-- Çağrılar -->
    <a href="<?= \App\Helpers\Url::to('/calls/history') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-phone text-white text-xl"></i>
          </div>
          <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-blue-600 dark:text-blue-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Çağrılar</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Çağrı geçmişi</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>

    <?php if (!$isGroupMember): ?>
    <!-- Raporlar -->
    <a href="<?= \App\Helpers\Url::to('/reports') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-chart-line text-white text-xl"></i>
          </div>
          <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-emerald-600 dark:text-emerald-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Raporlar</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Detaylı analizler</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Agent Durum -->
    <a href="<?= \App\Helpers\Url::to('/agents') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-rose-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-headset text-white text-xl"></i>
          </div>
          <div class="p-2 bg-rose-100 dark:bg-rose-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-rose-600 dark:text-rose-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Agent Durum</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Agent yönetimi</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-rose-600 dark:text-rose-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Dış Numaralar -->
    <a href="<?= \App\Helpers\Url::to('/numbers') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-amber-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-address-book text-white text-xl"></i>
          </div>
          <div class="p-2 bg-amber-100 dark:bg-amber-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-amber-600 dark:text-amber-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Dış Numaralar</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Numara yönetimi</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>
    <?php else: ?>
    <!-- Groupmember için Raporlar -->
    <a href="<?= \App\Helpers\Url::to('/reports') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-chart-line text-white text-xl"></i>
          </div>
          <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-emerald-600 dark:text-emerald-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Raporlar</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Çağrı raporlarım</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>
    <?php endif; ?>

    <?php if ($isSuper): ?>
    <!-- Ana Bakiye -->
    <a href="<?= \App\Helpers\Url::to('/balance') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-fuchsia-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-fuchsia-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-fuchsia-500 to-purple-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-wallet text-white text-xl"></i>
          </div>
          <div class="p-2 bg-fuchsia-100 dark:bg-fuchsia-900/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-fuchsia-600 dark:text-fuchsia-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Ana Bakiye</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">API bakiye yönetimi</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-fuchsia-600 dark:text-fuchsia-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>
    <?php endif; ?>

    <!-- Profil -->
    <a href="<?= \App\Helpers\Url::to('/profile') ?>" class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl hover:shadow-slate-500/25 transition-all duration-300 transform hover:-translate-y-1 border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-slate-500/5 to-gray-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-gradient-to-br from-slate-500 to-gray-600 rounded-xl shadow-lg group-hover:shadow-xl transition-all duration-300">
            <i class="fa-solid fa-user-gear text-white text-xl"></i>
          </div>
          <div class="p-2 bg-slate-100 dark:bg-slate-700/50 rounded-lg opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
            <i class="fa-solid fa-arrow-right text-slate-600 dark:text-slate-400"></i>
          </div>
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Profil</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Hesap ayarları</p>
        </div>
        <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
          <div class="flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400 font-medium">
            <span>Görüntüle</span>
            <i class="fa-solid fa-external-link-alt"></i>
          </div>
        </div>
      </div>
    </a>

    <!-- Çıkış -->
    <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="group relative bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl hover:shadow-red-500/25 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-white/20 rounded-xl group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-right-from-bracket text-2xl"></i>
          </div>
          <div class="text-right">
            <div class="text-sm opacity-80">Güvenli çıkış</div>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <div class="text-lg font-bold">Çıkış Yap</div>
            <div class="text-sm opacity-80">Oturumu kapat</div>
          </div>
          <div class="p-2 bg-white/20 rounded-lg group-hover:bg-white/30 transition-colors duration-300">
            <i class="fa-solid fa-sign-out-alt text-sm"></i>
          </div>
        </div>
      </div>
    </a>
  </div>
</section>

<!-- Analytics Section -->
<?php if (!$isGroupMember): ?>
<section class="mb-8">
  <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
    <i class="fa-solid fa-chart-bar text-purple-500"></i>
    Analizler
  </h2>

  <div class="grid lg:grid-cols-2 gap-8">
    <!-- Trend Chart -->
    <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white">
            <?php echo $isSuper ? 'Gelir/Maliyet Trendi' : 'Harcama Trendi'; ?>
          </h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Son 7 günün analizi</p>
        </div>
        <div class="p-3 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg">
          <i class="fa-solid fa-chart-line text-white text-xl"></i>
        </div>
      </div>
      <div class="relative">
        <canvas id="trendLine" height="200"></canvas>
      </div>
      <div class="flex items-center justify-center mt-4 space-x-6">
        <?php if ($isSuper): ?>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
          <span class="text-sm text-slate-600 dark:text-slate-400">Gelir</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-red-500 rounded-full"></div>
          <span class="text-sm text-slate-600 dark:text-slate-400">Maliyet</span>
        </div>
        <?php else: ?>
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
          <span class="text-sm text-slate-600 dark:text-slate-400">Harcama</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Calls Chart -->
    <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-slate-800 dark:text-white">Günlük Çağrı Adedi</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">Son 7 günün çağrı sayısı</p>
        </div>
        <div class="p-3 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg">
          <i class="fa-solid fa-phone text-white text-xl"></i>
        </div>
      </div>
      <div class="relative">
        <canvas id="callsBar" height="200"></canvas>
      </div>
      <div class="flex items-center justify-center mt-4">
        <div class="flex items-center space-x-2">
          <div class="w-3 h-3 bg-indigo-500 rounded-full"></div>
          <span class="text-sm text-slate-600 dark:text-slate-400">Çağrı Sayısı</span>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- System Status & Recent Activity -->
<section class="grid lg:grid-cols-3 gap-8 mb-8">
  <!-- System Status -->
  <div class="lg:col-span-1">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
      <i class="fa-solid fa-server text-green-500"></i>
      Sistem Durumu
    </h2>

    <div class="space-y-4">
      <!-- API Status -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
              <i class="fa-solid fa-globe text-green-600 dark:text-green-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-white">API Bağlantısı</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">VoIP API durumu</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fa-solid fa-circle text-green-500 text-sm animate-pulse"></i>
            <span class="text-sm text-green-600 dark:text-green-400 font-medium">Aktif</span>
          </div>
        </div>
      </div>

      <!-- Database Status -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
              <i class="fa-solid fa-database text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-white">Veritabanı</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">MySQL bağlantısı</div>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <i class="fa-solid fa-circle text-green-500 text-sm animate-pulse"></i>
            <span class="text-sm text-green-600 dark:text-green-400 font-medium">Aktif</span>
          </div>
        </div>
      </div>

      <!-- Last Sync -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
              <i class="fa-solid fa-sync text-purple-600 dark:text-purple-400"></i>
            </div>
            <div>
              <div class="font-semibold text-slate-800 dark:text-white">Son Senkronizasyon</div>
              <div class="text-sm text-slate-600 dark:text-slate-400">Veri güncellemesi</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-sm font-medium text-slate-800 dark:text-white">2 dk önce</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Otomatik</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="lg:col-span-2">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
      <i class="fa-solid fa-clock-rotate-left text-orange-500"></i>
      Son Aktiviteler
    </h2>

    <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="p-6">
        <div class="space-y-4">
          <!-- Activity Item -->
          <div class="flex items-start space-x-4 p-4 bg-slate-50/50 dark:bg-slate-900/50 rounded-lg">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
              <i class="fa-solid fa-phone text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-slate-800 dark:text-white">Yeni çağrı alındı</span>
                <span class="text-sm text-slate-500 dark:text-slate-400">2 dk önce</span>
              </div>
              <p class="text-sm text-slate-600 dark:text-slate-400">+90 555 123 45 67 numaradan gelen çağrı yanıtlandı</p>
            </div>
          </div>

          <!-- Activity Item -->
          <div class="flex items-start space-x-4 p-4 bg-slate-50/50 dark:bg-slate-900/50 rounded-lg">
            <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
              <i class="fa-solid fa-user-plus text-green-600 dark:text-green-400"></i>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-slate-800 dark:text-white">Yeni kullanıcı eklendi</span>
                <span class="text-sm text-slate-500 dark:text-slate-400">15 dk önce</span>
              </div>
              <p class="text-sm text-slate-600 dark:text-slate-400">Ahmet Yılmaz kullanıcısı sisteme eklendi</p>
            </div>
          </div>

          <!-- Activity Item -->
          <div class="flex items-start space-x-4 p-4 bg-slate-50/50 dark:bg-slate-900/50 rounded-lg">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
              <i class="fa-solid fa-chart-line text-purple-600 dark:text-purple-400"></i>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-slate-800 dark:text-white">Rapor oluşturuldu</span>
                <span class="text-sm text-slate-500 dark:text-slate-400">1 saat önce</span>
              </div>
              <p class="text-sm text-slate-600 dark:text-slate-400">Haftalık performans raporu hazırlandı</p>
            </div>
          </div>

          <!-- Activity Item -->
          <div class="flex items-start space-x-4 p-4 bg-slate-50/50 dark:bg-slate-900/50 rounded-lg">
            <div class="p-2 bg-orange-100 dark:bg-orange-900/50 rounded-lg">
              <i class="fa-solid fa-wallet text-orange-600 dark:text-orange-400"></i>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-1">
                <span class="font-medium text-slate-800 dark:text-white">Bakiye güncellendi</span>
                <span class="text-sm text-slate-500 dark:text-slate-400">2 saat önce</span>
              </div>
              <p class="text-sm text-slate-600 dark:text-slate-400">Grup bakiyesi otomatik olarak güncellendi</p>
            </div>
          </div>
        </div>
      </div>

      <!-- View All Activities -->
      <div class="px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200/50 dark:border-slate-700/50">
        <a href="#" class="inline-flex items-center space-x-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-200">
          <span>Tüm aktiviteleri görüntüle</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Live clock update
  function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('tr-TR', {
      hour12: false,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
      timeElement.textContent = timeString;
    }
  }

  // Update clock every second
  updateClock();
  setInterval(updateClock, 1000);

  <?php if (!$isGroupMember): ?>
  // Initialize charts with modern styling
  function initCharts() {
    const labels = <?= json_encode($chartLabels ?? []) ?>;
    const revenue = <?= json_encode($chartRevenue ?? []) ?>;
    const cost = <?= json_encode($chartCost ?? []) ?>;
    const calls = <?= json_encode($chartCalls ?? []) ?>;

    // Trend Line Chart
    const trendCtx = document.getElementById('trendLine');
    if (trendCtx) {
      const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            <?php if ($isSuper): ?>
            {
              label: 'Gelir',
              data: revenue,
              borderColor: 'rgba(16, 185, 129, 1)',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: 'rgba(16, 185, 129, 1)',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8,
              pointHoverBackgroundColor: 'rgba(16, 185, 129, 1)',
              pointHoverBorderColor: '#ffffff',
              pointHoverBorderWidth: 3
            },
            {
              label: 'Maliyet',
              data: cost,
              borderColor: 'rgba(239, 68, 68, 1)',
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: 'rgba(239, 68, 68, 1)',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8,
              pointHoverBackgroundColor: 'rgba(239, 68, 68, 1)',
              pointHoverBorderColor: '#ffffff',
              pointHoverBorderWidth: 3
            }
            <?php else: ?>
            {
              label: 'Harcama',
              data: revenue,
              borderColor: 'rgba(59, 130, 246, 1)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              borderWidth: 3,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: 'rgba(59, 130, 246, 1)',
              pointBorderColor: '#ffffff',
              pointBorderWidth: 2,
              pointRadius: 6,
              pointHoverRadius: 8,
              pointHoverBackgroundColor: 'rgba(59, 130, 246, 1)',
              pointHoverBorderColor: '#ffffff',
              pointHoverBorderWidth: 3
            }
            <?php endif; ?>
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                  size: 12,
                  weight: '600'
                }
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              borderColor: 'rgba(255, 255, 255, 0.2)',
              borderWidth: 1,
              cornerRadius: 8,
              displayColors: true,
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.1)',
                borderDash: [5, 5]
              },
              ticks: {
                callback: function(value) {
                  return '$' + value.toFixed(2);
                },
                font: {
                  size: 11
                }
              }
            },
            x: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  size: 11
                }
              }
            }
          },
          elements: {
            point: {
              hoverBorderWidth: 3
            }
          },
          animation: {
            duration: 2000,
            easing: 'easeInOutQuart'
          }
        }
      });
    }

    // Calls Bar Chart
    const callsCtx = document.getElementById('callsBar');
    if (callsCtx) {
      const callsChart = new Chart(callsCtx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Çağrı Sayısı',
            data: calls,
            backgroundColor: 'rgba(99, 102, 241, 0.8)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            hoverBackgroundColor: 'rgba(99, 102, 241, 1)',
            hoverBorderColor: 'rgba(255, 255, 255, 1)',
            hoverBorderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            intersect: false,
            mode: 'index'
          },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              borderColor: 'rgba(255, 255, 255, 0.2)',
              borderWidth: 1,
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': ' + context.parsed.y;
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.1)',
                borderDash: [5, 5]
              },
              ticks: {
                font: {
                  size: 11
                }
              }
            },
            x: {
              grid: {
                display: false
              },
              ticks: {
                font: {
                  size: 11
                }
              }
            }
          },
          animation: {
            duration: 2000,
            easing: 'easeInOutQuart',
            delay: function(context) {
              return context.dataIndex * 200;
            }
          }
        }
      });
    }
  }

  // Initialize charts
  initCharts();
  <?php endif; ?>

  // Add smooth scrolling to sections
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  // Add loading animation for stat cards
  const statCards = document.querySelectorAll('[id$="Users"], [id$="Total"], [id$="Revenue"], [id$="Profit"]');
  statCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';

    setTimeout(() => {
      card.style.transition = 'all 0.6s ease-out';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, Math.random() * 500);
  });

  // Add hover effects for quick access cards
  document.querySelectorAll('.group').forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-8px) scale(1.02)';
    });

    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
  });

  // Add pulse animation to status indicators
  document.querySelectorAll('.animate-pulse').forEach(element => {
    setInterval(() => {
      element.style.animation = 'none';
      setTimeout(() => {
        element.style.animation = 'pulse 2s infinite';
      }, 10);
    }, 4000);
  });
});
</script>

<?php require __DIR__.'/partials/footer.php'; ?>

