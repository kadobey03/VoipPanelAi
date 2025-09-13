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
  <!-- Modern Header -->
  <header class="sticky top-0 z-50">
    <!-- Main Header Bar -->
    <div class="relative">
      <!-- Background with Glassmorphism -->
      <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600"></div>
      <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>

      <!-- Content -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
          <!-- Logo & Brand -->
          <div class="flex items-center space-x-3">
            <a href="<?= Url::to('/') ?>" class="group relative flex items-center space-x-3 p-2 rounded-xl hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <!-- Animated Logo Background -->
              <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

              <!-- Logo Icon -->
              <div class="relative p-2 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300">
                <i class="fa-solid fa-wave-square text-2xl text-white animate-pulse"></i>
              </div>

              <!-- Brand Text -->
              <div class="relative">
                <span class="text-white font-bold text-lg lg:text-xl tracking-wide">PapaM VoIP Panel</span>
                <div class="text-white/70 text-xs font-medium">Moden İletişim Çözümleri</div>
              </div>
            </a>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden lg:flex items-center space-x-1">
            <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
              <!-- Users -->
              <a href="<?= Url::to('/users') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-users text-lg"></i>
                <span class="font-medium">Kullanıcılar</span>
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>

              <!-- Groups -->
              <a href="<?= Url::to('/groups') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-layer-group text-lg"></i>
                <span class="font-medium">Gruplar</span>
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>
            <?php endif; ?>

            <!-- Calls -->
            <a href="<?= Url::to('/calls/history') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-phone text-lg"></i>
              <span class="font-medium">Çağrılar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <!-- Reports -->
            <a href="<?= Url::to('/reports') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-chart-line text-lg"></i>
              <span class="font-medium">Raporlar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-red-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
            <!-- Agents -->
            <a href="<?= Url::to('/agents') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-user-nurse text-lg"></i>
              <span class="font-medium">Agent</span>
              <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='groupmember'): ?>
            <!-- Balance Dropdown -->
            <div class="relative" id="balance-menu-container">
              <button id="balance-menu-btn" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-wallet text-lg"></i>
                <span class="font-medium">Bakiye</span>
                <i class="fa-solid fa-chevron-down text-sm transition-transform duration-300 group-hover:rotate-180"></i>
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </button>

              <!-- Dropdown Menu -->
              <div id="balance-menu" class="absolute hidden right-0 mt-2 w-72 bg-white/95 dark:bg-slate-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/20 py-3 z-50">
                <div class="px-4 py-2 border-b border-slate-200/50 dark:border-slate-700/50">
                  <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Bakiye Yönetimi</h3>
                </div>
                <div class="py-2">
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/topups') ?>">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                      <i class="fa-solid fa-inbox text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Yükleme Talepleri</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Bekleyen talepleri yönet</div>
                    </div>
                  </a>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/balance/topup') ?>">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                      <i class="fa-solid fa-circle-plus text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Bakiye Yükle</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Hesabınıza bakiye ekleyin</div>
                    </div>
                  </a>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/transactions') ?>">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                      <i class="fa-solid fa-clock-rotate-left text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Bakiye Geçmişi</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">İşlem geçmişinizi görüntüleyin</div>
                    </div>
                  </a>
                  <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
                  <div class="border-t border-slate-200/50 dark:border-slate-700/50 my-2"></div>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/payment-methods') ?>">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                      <i class="fa-solid fa-money-bill-transfer text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Ödeme Yöntemleri</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Ödeme yöntemlerini yönet</div>
                    </div>
                  </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Profile -->
            <a href="<?= Url::to('/profile') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-user-gear text-lg"></i>
              <span class="font-medium">Profil</span>
              <div class="absolute inset-0 bg-gradient-to-r from-slate-500/20 to-gray-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
            <!-- Settings -->
            <a href="<?= Url::to('/settings') ?>" class="relative group flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-cogs text-lg"></i>
              <span class="font-medium">Ayarlar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>
            <?php endif; ?>
          </nav>

          <!-- Right Side Actions -->
          <div class="flex items-center space-x-3">
            <!-- Language Selector -->
            <div class="relative">
              <form method="post" action="/change-lang" class="inline-block">
                <select name="lang" id="lang-select" class="appearance-none bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-lg border border-white/20 hover:border-white/30 transition-all duration-300 cursor-pointer pr-8">
                  <option value="tr" <?= Lang::current() === 'tr' ? 'selected' : '' ?>>🇹🇷 TR</option>
                  <option value="en" <?= Lang::current() === 'en' ? 'selected' : '' ?>>🇺🇸 EN</option>
                </select>
                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                  <i class="fa-solid fa-chevron-down text-white/70 text-xs"></i>
                </div>
              </form>
            </div>

            <!-- Theme Toggle -->
            <button id="toggle-theme" class="relative group p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-300 transform hover:scale-110">
              <i class="fa-solid fa-moon text-white text-lg"></i>
              <div class="absolute inset-0 bg-white/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>

            <!-- User Menu (if logged in) -->
            <?php if(isset($_SESSION['user'])): ?>
            <div class="hidden lg:flex items-center space-x-2 pl-4 border-l border-white/20">
              <div class="flex items-center space-x-2 text-white">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                  <span class="text-sm font-semibold"><?php echo substr($_SESSION['user']['login'] ?? 'U', 0, 1); ?></span>
                </div>
                <div class="hidden xl:block">
                  <div class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user']['login'] ?? ''); ?></div>
                  <div class="text-xs text-white/70"><?php echo ucfirst($_SESSION['user']['role'] ?? ''); ?></div>
                </div>
              </div>

              <a href="<?= Url::to('/logout') ?>" class="group relative p-2 bg-red-500/20 hover:bg-red-500/30 rounded-lg transition-all duration-300 transform hover:scale-110">
                <i class="fa-solid fa-right-from-bracket text-white text-lg"></i>
                <div class="absolute inset-0 bg-red-500/30 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>
            </div>
            <?php endif; ?>

            <!-- Mobile Menu Button -->
            <button id="menu-btn" class="lg:hidden relative group p-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-300 transform hover:scale-110">
              <i class="fa-solid fa-bars text-white text-lg"></i>
              <div class="absolute inset-0 bg-white/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div id="mobile-menu" class="lg:hidden hidden bg-black/20 backdrop-blur-lg border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-4">
          <div class="space-y-2">
            <?php if(isset($_SESSION['user'])): ?>
            <!-- User Info Mobile -->
            <div class="flex items-center space-x-3 p-3 bg-white/10 rounded-lg">
              <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold"><?php echo substr($_SESSION['user']['login'] ?? 'U', 0, 1); ?></span>
              </div>
              <div>
                <div class="text-white font-medium"><?php echo htmlspecialchars($_SESSION['user']['login'] ?? ''); ?></div>
                <div class="text-white/70 text-sm"><?php echo ucfirst($_SESSION['user']['role'] ?? ''); ?></div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Mobile Navigation Links -->
            <div class="grid grid-cols-2 gap-2">
              <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/users') ?>">
                <i class="fa-solid fa-users text-white"></i>
                <span class="text-white font-medium">Kullanıcılar</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/groups') ?>">
                <i class="fa-solid fa-layer-group text-white"></i>
                <span class="text-white font-medium">Gruplar</span>
              </a>
              <?php endif; ?>

              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/calls/history') ?>">
                <i class="fa-solid fa-phone text-white"></i>
                <span class="text-white font-medium">Çağrılar</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/reports') ?>">
                <i class="fa-solid fa-chart-line text-white"></i>
                <span class="text-white font-medium">Raporlar</span>
              </a>

              <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/agents') ?>">
                <i class="fa-solid fa-user-nurse text-white"></i>
                <span class="text-white font-medium">Agent</span>
              </a>
              <?php endif; ?>

              <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='groupmember'): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/topups') ?>">
                <i class="fa-solid fa-inbox text-white"></i>
                <span class="text-white font-medium">Yükleme Talepleri</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/balance/topup') ?>">
                <i class="fa-solid fa-circle-plus text-white"></i>
                <span class="text-white font-medium">Bakiye Yükle</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/transactions') ?>">
                <i class="fa-solid fa-clock-rotate-left text-white"></i>
                <span class="text-white font-medium">Bakiye Geçmişi</span>
              </a>
              <?php endif; ?>

              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/profile') ?>">
                <i class="fa-solid fa-user-gear text-white"></i>
                <span class="text-white font-medium">Profil</span>
              </a>

              <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/settings') ?>">
                <i class="fa-solid fa-cogs text-white"></i>
                <span class="text-white font-medium">Ayarlar</span>
              </a>
              <?php endif; ?>
            </div>

            <!-- Mobile Logout -->
            <div class="pt-4 border-t border-white/10">
              <a class="flex items-center space-x-3 p-3 bg-red-500/20 hover:bg-red-500/30 rounded-lg transition-colors duration-200 w-full" href="<?= Url::to('/logout') ?>">
                <i class="fa-solid fa-right-from-bracket text-red-400"></i>
                <span class="text-white font-medium">Çıkış Yap</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Mobile menu toggle
      const menuBtn = document.getElementById('menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');

      if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
          mobileMenu.classList.toggle('hidden');

          // Animate hamburger icon
          const icon = menuBtn.querySelector('i');
          if (icon) {
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
          }
        });
      }

      // Theme toggle function
      function toggleTheme() {
        document.documentElement.classList.toggle('dark');

        // Update localStorage
        try {
          const isDark = document.documentElement.classList.contains('dark');
          localStorage.setItem('theme', isDark ? 'dark' : 'light');

          // Update theme icon
          const themeIcon = document.querySelector('#toggle-theme i');
          if (themeIcon) {
            themeIcon.className = isDark ? 'fa-solid fa-sun text-yellow-400' : 'fa-solid fa-moon text-white';
          }
        } catch (e) {
          console.warn('Unable to save theme preference');
        }
      }

      // Theme toggle buttons
      const themeBtn = document.getElementById('toggle-theme');
      if (themeBtn) {
        themeBtn.addEventListener('click', toggleTheme);
      }

      // Balance menu toggle
      const balanceBtn = document.getElementById('balance-menu-btn');
      const balanceMenu = document.getElementById('balance-menu');
      const balanceContainer = document.getElementById('balance-menu-container');

      if (balanceBtn && balanceMenu) {
        balanceBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          balanceMenu.classList.toggle('hidden');

          // Rotate chevron icon
          const chevron = balanceBtn.querySelector('.fa-chevron-down');
          if (chevron) {
            chevron.classList.toggle('rotate-180');
          }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
          if (balanceContainer && !balanceContainer.contains(e.target)) {
            balanceMenu.classList.add('hidden');
            const chevron = balanceBtn.querySelector('.fa-chevron-down');
            if (chevron) {
              chevron.classList.remove('rotate-180');
            }
          }
        });
      }

      // Language selector auto-submit
      const langSelect = document.getElementById('lang-select');
      if (langSelect) {
        langSelect.addEventListener('change', function() {
          this.form.submit();
        });
      }

      // Initialize theme icon on load
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        const themeIcon = document.querySelector('#toggle-theme i');
        if (themeIcon) {
          themeIcon.className = 'fa-solid fa-sun text-yellow-400';
        }
      }

      // Smooth scroll for anchor links
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

      // Add loading states for navigation links
      document.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', function() {
          // Add loading class for visual feedback
          this.classList.add('loading');
        });
      });

      // Improve mobile menu UX
      if (mobileMenu) {
        // Close mobile menu when clicking on a link
        mobileMenu.querySelectorAll('a').forEach(link => {
          link.addEventListener('click', function() {
            mobileMenu.classList.add('hidden');
            const icon = menuBtn.querySelector('i');
            if (icon) {
              icon.className = 'fa-solid fa-bars text-white text-lg';
            }
          });
        });
      }

      // Add keyboard navigation
      document.addEventListener('keydown', function(e) {
        // Close mobile menu with Escape
        if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('hidden')) {
          mobileMenu.classList.add('hidden');
          const icon = menuBtn.querySelector('i');
          if (icon) {
            icon.className = 'fa-solid fa-bars text-white text-lg';
          }
        }

        // Close balance menu with Escape
        if (e.key === 'Escape' && balanceMenu && !balanceMenu.classList.contains('hidden')) {
          balanceMenu.classList.add('hidden');
        }
      });

      // Add touch support for mobile
      if ('ontouchstart' in window) {
        document.querySelectorAll('.group').forEach(el => {
          el.addEventListener('touchstart', function() {
            // Add touch feedback
          });
        });
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

  <!-- Admin Impersonation Sticky Button -->
  <?php if (isset($_SESSION['impersonator'])): ?>
  <div id="admin-return-btn" class="fixed bottom-6 right-6 z-50">
    <a href="<?= Url::to('/admin/impersonate/stop') ?>"
       class="inline-flex items-center gap-3 px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-2xl shadow-2xl hover:shadow-red-500/25 transform hover:scale-105 transition-all duration-300 animate-pulse">
      <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-lg">
        <i class="fa-solid fa-arrow-left text-lg"></i>
      </div>
      <div class="text-left">
        <div class="text-sm opacity-90">Şu anda giriş yaptığınız:</div>
        <div class="text-base font-bold"><?= htmlspecialchars($_SESSION['user']['login'] ?? '') ?></div>
      </div>
      <div class="text-sm font-medium">Admin'e Geri Dön</div>
    </a>
  </div>
  <?php endif; ?>

  <main class="max-w-7xl mx-auto px-4 py-6">

