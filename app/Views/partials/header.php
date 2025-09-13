<?php
use App\Helpers\Url;
use App\Helpers\Lang;

Lang::load(Lang::current());

function __($key) {
    return Lang::get($key);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="theme-color" content="#3b82f6">
   <link rel="manifest" href="/manifest.json">
   <link rel="icon" href="/favicon.ico" type="image/x-icon">
   <meta name="description" content="PapaM VoIP Panel - VoIP çağrı yönetimi ve raporlama sistemi">
   <meta name="keywords" content="voip, çağrı, panel, rapor, telefon, iletişim, voip panel, çağrı merkezi">
   <meta property="og:title" content="PapaM VoIP Panel">
   <meta property="og:description" content="VoIP çağrı yönetimi ve raporlama sistemi">
   <meta property="og:image" content="/assets/images/seo-image.png">
   <meta property="og:type" content="website">
   <title><?= isset($title) ? htmlspecialchars($title) : 'PapaM VoIP Panel' ?></title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script>
    try{
      const t=localStorage.getItem('theme');
      if(t==='dark'){document.documentElement.classList.add('dark')}
    }catch(e){}
  </script>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 text-slate-900 dark:from-slate-900 dark:to-slate-950 dark:text-slate-100">
<?php if (empty($hideNav)): ?>
  <header class="sticky top-0 z-40 shadow">
    <div class="bg-gradient-to-r from-indigo-600 to-blue-600">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?= Url::to('/') ?>" class="flex items-center gap-2 text-white">
          <i class="fa-solid fa-wave-square text-2xl animate-pulse"></i>
          <span class="font-semibold tracking-wide">PapaM VoIP Panel</span>
        </a>
        <nav class="hidden md:flex items-center gap-4 text-white/90">
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='user'): ?>
          <a class="hover:text-white transition" href="<?= Url::to('/users') ?>"><i class="fa-solid fa-users"></i> <?= __('users') ?></a>
          <a class="hover:text-white transition" href="<?= Url::to('/groups') ?>"><i class="fa-solid fa-layer-group"></i> Gruplar</a>
          <?php endif; ?>
          <a class="hover:text-white transition" href="<?= Url::to('/calls/history') ?>"><i class="fa-solid fa-phone"></i> Çağrılar</a>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='user'): ?>
          <a class="hover:text-white transition" href="<?= Url::to('/reports') ?>"><i class="fa-solid fa-chart-line"></i> Raporlar</a>
          <a class="hover:text-white transition" href="<?= Url::to('/agents') ?>"><i class="fa-solid fa-user-nurse"></i> Agent</a>
          <?php endif; ?>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='groupmember'): ?>
          <div class="relative">
            <button id="balance-menu-btn" class="hover:text-white transition inline-flex items-center gap-1"><i class="fa-solid fa-wallet"></i> Bakiye <i class="fa-solid fa-caret-down text-xs"></i></button>
            <div id="balance-menu" class="absolute hidden right-0 mt-2 w-60 bg-white text-slate-800 rounded shadow-lg py-2">
              <a class="block px-3 py-2 hover:bg-slate-50" href="<?= Url::to('/topups') ?>"><i class="fa-solid fa-inbox"></i> Bakiye Yükleme Talepleri</a>
              <a class="block px-3 py-2 hover:bg-slate-50" href="<?= Url::to('/balance/topup') ?>"><i class="fa-solid fa-circle-plus"></i> Bakiye Yükle</a>
              <a class="block px-3 py-2 hover:bg-slate-50" href="<?= Url::to('/transactions') ?>"><i class="fa-solid fa-clock-rotate-left"></i> Bakiye Geçmişi</a>
              <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
              <div class="border-t my-2"></div>
              <a class="block px-3 py-2 hover:bg-slate-50" href="<?= Url::to('/payment-methods') ?>"><i class="fa-solid fa-money-bill-transfer"></i> Ödeme Yöntemleri</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
          <a class="hover:text-white transition" href="<?= Url::to('/profile') ?>"><i class="fa-solid fa-user-gear"></i> Profil</a>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
          <a class="hover:text-white transition" href="<?= Url::to('/settings') ?>"><i class="fa-solid fa-cogs"></i> Ayarlar</a>
          <?php endif; ?>
          <a class="hover:text-white transition" href="<?= Url::to('/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Çıkış</a>
          <form method="post" action="/change-lang" style="display:inline;">
            <select name="lang" id="lang-select" class="ml-2 px-2 py-1 rounded bg-white/20 hover:bg-white/30 text-white border-none">
              <option value="tr" <?= Lang::current() === 'tr' ? 'selected' : '' ?>>TR</option>
              <option value="en" <?= Lang::current() === 'en' ? 'selected' : '' ?>>EN</option>
            </select>
          </form>
          <button id="toggle-theme" class="ml-2 px-3 py-1 rounded bg-white/20 hover:bg-white/30 text-white"><i class="fa-solid fa-moon"></i></button>
        </nav>
        <button id="menu-btn" class="md:hidden text-white text-2xl"><i class="fa-solid fa-bars"></i></button>
      </div>
      <div id="mobile-menu" class="md:hidden hidden px-4 pb-3 text-white/90">
        <div class="grid grid-cols-2 gap-2 text-sm">
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='user'): ?>
          <a class="hover:text-white" href="<?= Url::to('/users') ?>"><i class="fa-solid fa-users"></i> Kullanıcılar</a>
          <a class="hover:text-white" href="<?= Url::to('/groups') ?>"><i class="fa-solid fa-layer-group"></i> Gruplar</a>
          <?php endif; ?>
          <a class="hover:text-white" href="<?= Url::to('/calls/history') ?>"><i class="fa-solid fa-phone"></i> Çağrılar</a>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='user'): ?>
          <a class="hover:text-white" href="<?= Url::to('/reports') ?>"><i class="fa-solid fa-chart-line"></i> Raporlar</a>
          <a class="hover:text-white" href="<?= Url::to('/agents') ?>"><i class="fa-solid fa-user-nurse"></i> Agent</a>
          <?php endif; ?>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='groupmember'): ?>
          <a class="hover:text-white" href="<?= Url::to('/topups') ?>"><i class="fa-solid fa-inbox"></i> Yükleme Talepleri</a>
          <a class="hover:text-white" href="<?= Url::to('/balance/topup') ?>"><i class="fa-solid fa-circle-plus"></i> Bakiye Yükle</a>
          <a class="hover:text-white" href="<?= Url::to('/transactions') ?>"><i class="fa-solid fa-clock-rotate-left"></i> Bakiye Geçmişi</a>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
          <a class="hover:text-white" href="<?= Url::to('/payment-methods') ?>"><i class="fa-solid fa-money-bill-transfer"></i> Ödeme Yöntemleri</a>
          <?php endif; ?>
          <a class="hover:text-white" href="<?= Url::to('/balance') ?>"><i class="fa-solid fa-wallet"></i> Bakiye</a>
          <?php endif; ?>
          <a class="hover:text-white" href="<?= Url::to('/profile') ?>"><i class="fa-solid fa-user-gear"></i> Profil</a>
          <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
          <a class="hover:text-white" href="<?= Url::to('/settings') ?>"><i class="fa-solid fa-cogs"></i> Ayarlar</a>
          <?php endif; ?>
          <a class="hover:text-white" href="<?= Url::to('/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Çıkış</a>
          <button id="toggle-theme-m" class="mt-2 px-3 py-1 rounded bg-white/20 hover:bg-white/30 text-white col-span-2"><i class="fa-solid fa-moon"></i> Tema</button>
        </div>
      </div>
    </div>
  </header>
  <script>
    document.addEventListener('DOMContentLoaded',function(){
      var m=document.getElementById('menu-btn');var mm=document.getElementById('mobile-menu');
      if(m&&mm){ m.addEventListener('click',()=>mm.classList.toggle('hidden')); }
      function tog(){document.documentElement.classList.toggle('dark');try{localStorage.setItem('theme',document.documentElement.classList.contains('dark')?'dark':'light')}catch(e){} }
      var t=document.getElementById('toggle-theme'); if(t){t.addEventListener('click',tog)}
      var tm=document.getElementById('toggle-theme-m'); if(tm){tm.addEventListener('click',tog)}
      // Balance menu toggle on click
      var bbtn=document.getElementById('balance-menu-btn'); var bmenu=document.getElementById('balance-menu');
      if(bbtn && bmenu){
        bbtn.addEventListener('click', function(e){ e.preventDefault(); bmenu.classList.toggle('hidden'); });
        // Close if click is outside both the menu and the button (including its children)
        document.addEventListener('click', function(e){ if(!bmenu.contains(e.target) && !bbtn.contains(e.target)){ bmenu.classList.add('hidden'); } });
      }
    });

  </script>
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js')
        .then(registration => console.log('SW registered'))
        .catch(error => console.log('SW registration failed'));
    }
  </script>
<?php endif; ?>
  <main class="max-w-7xl mx-auto px-4 py-6">

