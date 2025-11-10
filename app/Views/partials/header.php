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
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.css" rel="stylesheet">
   <?php if (isset($title) && strpos($title, 'Giriş') !== false): ?>
   <link href="/assets/css/login-animations.css" rel="stylesheet">
   <script src="/assets/js/login.js" defer></script>
   <?php endif; ?>
  <script>
    try{
      const t=localStorage.getItem('theme');
      if(t==='dark'){document.documentElement.classList.add('dark')}
    }catch(e){}
  </script>
</head>
<body class="min-h-screen <?php echo (isset($title) && strpos($title, 'Giriş') !== false) ? 'login-background' : 'bg-gradient-to-b from-slate-50 to-slate-100'; ?> text-slate-900 dark:from-slate-900 dark:to-slate-950 dark:text-slate-100">
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
        <div class="flex items-center justify-between h-14 lg:h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center space-x-3">
            <a href="<?= Url::to('/') ?>" class="group relative flex items-center space-x-3 p-2 rounded-xl hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <!-- Animated Logo Background -->
              <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

              <!-- Logo Icon -->
              <div class="relative p-1.5 bg-white/20 rounded-lg group-hover:bg-white/30 transition-all duration-300">
                <i class="fa-solid fa-wave-square text-xl text-white animate-pulse"></i>
              </div>

              <!-- Brand Text -->
              <div class="relative">
                <span class="text-white font-bold text-base lg:text-lg tracking-wide">PapaM VoIP Panel</span>
                <div class="text-white/70 text-xs font-medium hidden sm:block">Modern İletişim Çözümleri</div>
              </div>
            </a>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden lg:flex items-center space-x-1">
            <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
              <!-- Dashboard/Home -->
              <a href="<?= Url::to('/') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-house text-base"></i>
                <span class="font-medium text-sm">Anasayfa</span>
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>
  
              <!-- Users -->
              <a href="<?= Url::to('/users') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-users text-base"></i>
                <span class="font-medium text-sm">Kullanıcılar</span>
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>
  
              <!-- Groups -->
              <a href="<?= Url::to('/groups') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-layer-group text-base"></i>
                <span class="font-medium text-sm">Gruplar</span>
                <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </a>
            <?php endif; ?>

            <!-- Calls -->
            <a href="<?= Url::to('/calls/history') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-phone text-base"></i>
              <span class="font-medium text-sm">Çağrılar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <!-- Reports -->
            <a href="<?= Url::to('/reports') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-chart-line text-base"></i>
              <span class="font-medium text-sm">Raporlar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-red-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
            <!-- Agent Dropdown -->
            <div class="relative" id="agent-menu-container">
              <button id="agent-menu-btn" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-user-nurse text-base"></i>
                <span class="font-medium text-sm">Agent</span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </button>

              <!-- Agent Dropdown Menu -->
              <div id="agent-menu" class="absolute hidden right-0 mt-2 w-64 bg-white/95 dark:bg-slate-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/20 py-3 z-50">
                <div class="px-4 py-2 border-b border-slate-200/50 dark:border-slate-700/50">
                  <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Agent Yönetimi</h3>
                </div>
                <div class="py-2">
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/agents') ?>">
                    <div class="p-2 bg-cyan-100 dark:bg-cyan-900/50 rounded-lg">
                      <i class="fa-solid fa-headset text-cyan-600 dark:text-cyan-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Agentler</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Agent durumlarını görüntüle</div>
                    </div>
                  </a>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/agents/purchase') ?>">
                    <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-lg">
                      <i class="fa-solid fa-shopping-cart text-green-600 dark:text-green-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Agent Satın Al</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Yeni agent satın alın</div>
                    </div>
                  </a>
                  <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/agents/manage-products') ?>">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/50 rounded-lg">
                      <i class="fa-solid fa-cogs text-purple-600 dark:text-purple-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Ürün Yönetimi</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Agent ürünlerini yönet</div>
                    </div>
                  </a>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/agents/subscriptions') ?>">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                      <i class="fa-solid fa-calendar-alt text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Abonelik Yönetimi</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400">Abonelikleri yönet</div>
                    </div>
                  </a>
                  <?php endif; ?>
                  
                  <?php if(isset($_SESSION['user']) && in_array($_SESSION['user']['role']??'', ['superadmin', 'groupadmin'])): ?>
                  <a class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200" href="<?= Url::to('/agents/subscriptions') ?>">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                      <i class="fa-solid fa-calendar-check text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800 dark:text-white">Aboneliklerim</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo ($_SESSION['user']['role']??'') === 'superadmin' ? 'Tüm abonelikler' : 'Grup abonelikleri'; ?></div>
                    </div>
                  </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')!=='groupmember'): ?>
            <!-- Balance Dropdown -->
            <div class="relative" id="balance-menu-container">
              <button id="balance-menu-btn" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
                <i class="fa-solid fa-wallet text-base"></i>
                <span class="font-medium text-sm">Bakiye</span>
                <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180"></i>
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

            <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
            <!-- Settings -->
            <a href="<?= Url::to('/settings') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-cogs text-base"></i>
              <span class="font-medium text-sm">Ayarlar</span>
              <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>
            
            <!-- Payment Settings -->
            <a href="<?= Url::to('/payment-settings') ?>" class="relative group flex items-center space-x-1.5 px-3 py-2 text-white/80 hover:text-white rounded-lg hover:bg-white/10 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-credit-card text-base"></i>
              <span class="font-medium text-sm">Ödeme Ayarları</span>
              <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>
            <?php endif; ?>
          </nav>

          <!-- Right Side Actions -->
          <div class="flex items-center space-x-2">
            <!-- Language Selector with Flags -->
            <div class="relative" id="lang-menu-container">
              <button id="lang-menu-btn" class="relative group flex items-center space-x-1 px-2 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-300 transform hover:scale-105">
                <?php if(Lang::current() === 'tr'): ?>
                  <span class="text-base">🇹🇷</span>
                <?php else: ?>
                  <span class="text-base">🇺🇸</span>
                <?php endif; ?>
                <i class="fa-solid fa-chevron-down text-white/70 text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                <div class="absolute inset-0 bg-white/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </button>

              <!-- Language Dropdown -->
              <div id="lang-menu" class="absolute hidden right-0 mt-2 w-32 bg-white/95 dark:bg-slate-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/20 py-2 z-50">
                <form method="post" action="/change-lang">
                  <button type="submit" name="lang" value="tr" class="flex items-center space-x-3 w-full px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                    <span class="text-lg">🇹🇷</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white"><?= __('turkish') ?: 'Türkçe' ?></span>
                  </button>
                  <button type="submit" name="lang" value="en" class="flex items-center space-x-3 w-full px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                    <span class="text-lg">🇺🇸</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white"><?= __('english') ?: 'English' ?></span>
                  </button>
                </form>
              </div>
            </div>

            <!-- Theme Toggle -->
            <button id="toggle-theme" class="relative group p-1.5 bg-white/10 hover:bg-white/20 rounded-lg transition-all duration-300 transform hover:scale-110">
              <i class="fa-solid fa-moon text-white text-base"></i>
              <div class="absolute inset-0 bg-white/20 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>

            <!-- User Menu (if logged in) -->
            <?php if(isset($_SESSION['user'])): ?>
            <div class="hidden lg:flex items-center pl-3 border-l border-white/20">
              <!-- Profile Dropdown -->
              <div class="relative" id="profile-menu-container">
                <button id="profile-menu-btn" class="relative group flex items-center space-x-2 text-white hover:text-white/90 rounded-lg transition-all duration-300">
                  <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center">
                    <span class="text-xs font-semibold"><?php echo substr($_SESSION['user']['login'] ?? 'U', 0, 1); ?></span>
                  </div>
                  <div class="hidden xl:block text-left">
                    <div class="text-xs font-medium"><?php echo htmlspecialchars($_SESSION['user']['login'] ?? ''); ?></div>
                    <div class="text-xs text-white/70"><?php echo ucfirst($_SESSION['user']['role'] ?? ''); ?></div>
                  </div>
                  <i class="fa-solid fa-chevron-down text-white/70 text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                  <div class="absolute inset-0 bg-white/10 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </button>

                <!-- Profile Dropdown Menu -->
                <div id="profile-menu" class="absolute hidden right-0 mt-2 w-56 bg-white/95 dark:bg-slate-800/95 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 dark:border-slate-700/20 py-3 z-50">
                  <div class="px-4 py-3 border-b border-slate-200/50 dark:border-slate-700/50">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold"><?php echo substr($_SESSION['user']['login'] ?? 'U', 0, 1); ?></span>
                      </div>
                      <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo htmlspecialchars($_SESSION['user']['login'] ?? ''); ?></div>
                        <div class="text-xs text-slate-500 dark:text-slate-400"><?php echo ucfirst($_SESSION['user']['role'] ?? ''); ?></div>
                      </div>
                    </div>
                  </div>

                  <div class="py-2">
                    <a href="<?= Url::to('/profile') ?>" class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                      <div class="p-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                        <i class="fa-solid fa-user text-indigo-600 dark:text-indigo-400 text-sm"></i>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-slate-800 dark:text-white">Profil</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Hesap ayarları</div>
                      </div>
                    </a>

                    <a href="<?= Url::to('/profile') ?>" class="flex items-center space-x-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                      <div class="p-1.5 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                        <i class="fa-solid fa-gear text-blue-600 dark:text-blue-400 text-sm"></i>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-slate-800 dark:text-white">Ayarlar</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Tercihler</div>
                      </div>
                    </a>
                  </div>

                  <div class="border-t border-slate-200/50 dark:border-slate-700/50 pt-2">
                    <a href="<?= Url::to('/logout') ?>" class="flex items-center space-x-3 px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200">
                      <div class="p-1.5 bg-red-100 dark:bg-red-900/50 rounded-lg">
                        <i class="fa-solid fa-right-from-bracket text-red-600 dark:text-red-400 text-sm"></i>
                      </div>
                      <div>
                        <div class="text-sm font-medium text-slate-800 dark:text-white">Çıkış Yap</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Oturumu kapat</div>
                      </div>
                    </a>
                  </div>
                </div>
              </div>
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
              <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold text-sm"><?php echo substr($_SESSION['user']['login'] ?? 'U', 0, 1); ?></span>
              </div>
              <div>
                <div class="text-white font-medium text-sm"><?php echo htmlspecialchars($_SESSION['user']['login'] ?? ''); ?></div>
                <div class="text-white/70 text-xs"><?php echo ucfirst($_SESSION['user']['role'] ?? ''); ?></div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Mobile Navigation Links -->
            <div class="grid grid-cols-2 gap-2">
              <?php if(isset($_SESSION['user']) && !in_array($_SESSION['user']['role']??'', ['user','groupmember'])): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/') ?>">
                <i class="fa-solid fa-house text-white"></i>
                <span class="text-white font-medium">Anasayfa</span>
              </a>
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
                <i class="fa-solid fa-headset text-white"></i>
                <span class="text-white font-medium">Agentler</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/agents/purchase') ?>">
                <i class="fa-solid fa-shopping-cart text-white"></i>
                <span class="text-white font-medium">Agent Satın Al</span>
              </a>
              <?php if(isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/agents/manage-products') ?>">
                <i class="fa-solid fa-cogs text-white"></i>
                <span class="text-white font-medium">Ürün Yönetimi</span>
              </a>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/agents/subscriptions') ?>">
                <i class="fa-solid fa-calendar-alt text-white"></i>
                <span class="text-white font-medium">Abonelik Yönetimi</span>
              </a>
              <?php endif; ?>
              
              <?php if(isset($_SESSION['user']) && in_array($_SESSION['user']['role']??'', ['groupadmin'])): ?>
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/agents/subscriptions') ?>">
                <i class="fa-solid fa-calendar-check text-white"></i>
                <span class="text-white font-medium">Aboneliklerim</span>
              </a>
              <?php endif; ?>
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
              <a class="flex items-center space-x-3 p-3 bg-white/10 hover:bg-white/20 rounded-lg transition-colors duration-200" href="<?= Url::to('/payment-settings') ?>">
                <i class="fa-solid fa-credit-card text-white"></i>
                <span class="text-white font-medium">Ödeme Ayarları</span>
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

      // Language menu toggle
      const langBtn = document.getElementById('lang-menu-btn');
      const langMenu = document.getElementById('lang-menu');
      const langContainer = document.getElementById('lang-menu-container');

      if (langBtn && langMenu) {
        langBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          langMenu.classList.toggle('hidden');

          // Rotate chevron icon
          const chevron = langBtn.querySelector('.fa-chevron-down');
          if (chevron) {
            chevron.classList.toggle('rotate-180');
          }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
          if (langContainer && !langContainer.contains(e.target)) {
            langMenu.classList.add('hidden');
            const chevron = langBtn.querySelector('.fa-chevron-down');
            if (chevron) {
              chevron.classList.remove('rotate-180');
            }
          }
        });
      }

      // Profile menu toggle
      const profileBtn = document.getElementById('profile-menu-btn');
      const profileMenu = document.getElementById('profile-menu');
      const profileContainer = document.getElementById('profile-menu-container');

      if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          profileMenu.classList.toggle('hidden');

          // Rotate chevron icon
          const chevron = profileBtn.querySelector('.fa-chevron-down');
          if (chevron) {
            chevron.classList.toggle('rotate-180');
          }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
          if (profileContainer && !profileContainer.contains(e.target)) {
            profileMenu.classList.add('hidden');
            const chevron = profileBtn.querySelector('.fa-chevron-down');
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

        // Close agent menu with Escape
        const agentMenu = document.getElementById('agent-menu');
        if (e.key === 'Escape' && agentMenu && !agentMenu.classList.contains('hidden')) {
          agentMenu.classList.add('hidden');
        }
      });

      // Agent menu toggle
      const agentBtn = document.getElementById('agent-menu-btn');
      const agentMenu = document.getElementById('agent-menu');
      const agentContainer = document.getElementById('agent-menu-container');

      if (agentBtn && agentMenu) {
        agentBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          agentMenu.classList.toggle('hidden');

          // Rotate chevron icon
          const chevron = agentBtn.querySelector('.fa-chevron-down');
          if (chevron) {
            chevron.classList.toggle('rotate-180');
          }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
          if (agentContainer && !agentContainer.contains(e.target)) {
            agentMenu.classList.add('hidden');
            const chevron = agentBtn.querySelector('.fa-chevron-down');
            if (chevron) {
              chevron.classList.remove('rotate-180');
            }
          }
        });
      }

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

