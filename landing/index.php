<?php
// Start session for language support
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include language helper
require_once __DIR__ . '/includes/LangHelper.php';

// Handle language changes
if (isset($_POST['lang']) && in_array($_POST['lang'], ['tr', 'en', 'ru'])) {
    LangHelper::setLang($_POST['lang']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get current language
$currentLang = LangHelper::getCurrentLang();

// Set meta data
$title = __('PapaM VoIP Panel - Professional VoIP Management System');
$description = __('Advanced VoIP call management and reporting system with agent tracking, group management, balance control and detailed analytics.');
$keywords = __('VoIP panel, call management, agent tracking, CDR analysis, VoIP reporting, call center management');
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
    <meta name="author" content="PapaM VoIP">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(__('Transform your business communication with our premium VoIP solutions')) ?>">
    <meta property="og:type" content="website">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="assets/css/landing-animations.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-purple-dark text-white">
    <!-- Particle Canvas Background -->
    <canvas id="particle-canvas" class="particle-canvas"></canvas>
    
    <!-- Header -->
    <header class="fixed top-0 left-0 w-full z-50 main-nav floating-header" id="main-header">
        <div class="glass-nav px-4 sm:px-6 py-4">
            <nav class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo Section -->
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-phone-volume text-2xl text-white"></i>
                        <div class="logo-pulse"></div>
                    </div>
                    <div>
                        <h1 class="logo-text">PapaM VoIP</h1>
                        <p class="logo-subtitle hidden sm:block">Professional Communication</p>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#features" class="nav-link hover:text-purple-300 transition-colors duration-300"><?= __('Özellikler') ?></a>
                    <a href="#pricing" class="nav-link hover:text-purple-300 transition-colors duration-300"><?= __('Fiyatlandırma') ?></a>
                    <a href="/VoipPanelAi/" class="nav-link hover:text-purple-300 transition-colors duration-300"><?= __('Panel Girişi') ?></a>
                </div>
                
                <!-- Language & Login Section -->
                <div class="flex items-center space-x-3">
                    <!-- Language Dropdown -->
                    <div class="language-dropdown">
                        <button class="language-button">
                            <span class="language-flag">
                                <?php
                                $flags = ['tr' => '🇹🇷', 'en' => '🇬🇧', 'ru' => '🇷🇺'];
                                echo $flags[$currentLang] ?? '🇹🇷';
                                ?>
                            </span>
                            <span class="text-sm font-medium text-white hidden sm:inline"><?= strtoupper($currentLang) ?></span>
                            <i class="fas fa-chevron-down text-xs chevron-icon"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="language-dropdown-menu">
                            <form method="POST" action="" class="w-full">
                                <input type="hidden" name="lang" value="tr">
                                <button type="submit" class="language-option <?= $currentLang === 'tr' ? 'active' : '' ?>">
                                    <span class="language-flag">🇹🇷</span>
                                    <span>Türkçe</span>
                                </button>
                            </form>
                            <form method="POST" action="" class="w-full">
                                <input type="hidden" name="lang" value="en">
                                <button type="submit" class="language-option <?= $currentLang === 'en' ? 'active' : '' ?>">
                                    <span class="language-flag">🇬🇧</span>
                                    <span>English</span>
                                </button>
                            </form>
                            <form method="POST" action="" class="w-full">
                                <input type="hidden" name="lang" value="ru">
                                <button type="submit" class="language-option <?= $currentLang === 'ru' ? 'active' : '' ?>">
                                    <span class="language-flag">🇷🇺</span>
                                    <span>Русский</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Login & Register Buttons -->
                    <div class="flex items-center space-x-2">
                        <a href="/VoipPanelAi/" class="btn-outline text-sm px-3 py-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="hidden sm:inline ml-2"><?= __('Giriş') ?></span>
                        </a>
                        <a href="/VoipPanelAi/register" class="btn-primary flex items-center space-x-2 px-4 py-2 text-sm">
                            <i class="fas fa-user-plus"></i>
                            <span class="hidden sm:inline"><?= __('Kayıt Ol') ?></span>
                        </a>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors duration-300" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-white"></i>
                    </button>
                </div>
            </nav>
            
            <!-- Mobile Navigation -->
            <div class="mobile-menu lg:hidden mt-4 py-4 border-t border-white/20 hidden" id="mobile-menu">
                <div class="flex flex-col space-y-2">
                    <a href="#features" class="mobile-nav-link"><?= __('Özellikler') ?></a>
                    <a href="#pricing" class="mobile-nav-link"><?= __('Fiyatlandırma') ?></a>
                    <a href="/VoipPanelAi/" class="mobile-nav-link"><?= __('Panel Girişi') ?></a>
                    <a href="/VoipPanelAi/register" class="mobile-nav-link"><?= __('Kayıt Ol') ?></a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        menu.classList.toggle('active');
    }
    
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.getElementById('main-header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    </script>

    <!-- Hero Section -->
    <section class="hero-background" id="hero">
        <div class="hero-content">
            <div data-animate="fade-in" data-delay="500">
                <h1 class="hero-title">
                    <?= __('Profesyonel VoIP Çözümleri') ?>
                </h1>
                <p class="hero-subtitle">
                    <?= __('İşletmeniz için güvenilir, yüksek kaliteli VoIP hizmetleri') ?>
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mt-12" data-animate="slide-up" data-delay="800">
                <a href="/VoipPanelAi/register" class="btn-primary flex items-center space-x-3 text-lg px-10 py-5">
                    <i class="fas fa-user-plus text-xl"></i>
                    <span><?= __('Ücretsiz Kayıt Ol') ?></span>
                </a>
                <a href="/VoipPanelAi/" class="btn-outline flex items-center space-x-3 text-lg px-8 py-4">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                    <span><?= __('Panel Girişi') ?></span>
                </a>
            </div>
            
            <div class="flex flex-col items-center mt-8" data-animate="fade-in" data-delay="1000">
                <p class="text-purple-200 text-lg mb-4"><?= __('Sorularınız mı var?') ?></p>
                <a href="https://t.me/lionmw" target="_blank" class="flex items-center space-x-3 text-cyan-300 hover:text-cyan-200 transition-colors">
                    <i class="fab fa-telegram text-2xl"></i>
                    <span class="text-lg font-semibold"><?= __('Telegram\'dan İletişime Geçin') ?></span>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-16 max-w-4xl mx-auto" data-animate="slide-up" data-delay="1000">
                <div class="glass-card p-6 text-center">
                    <div class="text-3xl font-bold text-purple-300" data-counter="500">0</div>
                    <div class="text-purple-200 text-sm mt-2"><?= __('Aktif Müşteri') ?></div>
                </div>
                <div class="glass-card p-6 text-center">
                    <div class="text-3xl font-bold text-purple-300" data-counter="99">0</div>
                    <div class="text-purple-200 text-sm mt-2"><?= __('Uptime %') ?></div>
                </div>
                <div class="glass-card p-6 text-center">
                    <div class="text-3xl font-bold text-purple-300" data-counter="24">0</div>
                    <div class="text-purple-200 text-sm mt-2"><?= __('Saat Destek') ?></div>
                </div>
                <div class="glass-card p-6 text-center">
                    <div class="text-3xl font-bold text-purple-300" data-counter="50">0</div>
                    <div class="text-purple-200 text-sm mt-2"><?= __('Ülke Kapsamı') ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white" id="features">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-animate="fade-in" data-delay="200">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6"><?= __('Özelliklerimiz') ?></h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto"><?= __('Gelişmiş VoIP yönetim paneli ile tüm iletişim süreçlerinizi kontrol edin') ?></p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="300">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fas fa-headset text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('Agent Yönetimi') ?></h3>
                        <p class="feature-description"><?= __('Agent durumlarını takip edin, abonelikleri yönetin ve performans analizi yapın') ?></p>
                    </div>
                </div>
                
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="400">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fas fa-users text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('Grup & Bakiye Yönetimi') ?></h3>
                        <p class="feature-description"><?= __('Çoklu grup yapısı ile kullanıcıları organize edin ve bakiyeleri yönetin') ?></p>
                    </div>
                </div>
                
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="500">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fas fa-chart-line text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('Detaylı Raporlama') ?></h3>
                        <p class="feature-description"><?= __('Çağrı geçmişi, maliyet analizi ve performans raporları ile işinizi optimize edin') ?></p>
                    </div>
                </div>
                
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="600">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fas fa-phone text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('Çağrı Yönetimi') ?></h3>
                        <p class="feature-description"><?= __('Tüm çağrıları izleyin, kayıtları dinleyin ve CDR verilerini analiz edin') ?></p>
                    </div>
                </div>
                
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="700">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fas fa-code text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('API Entegrasyonu') ?></h3>
                        <p class="feature-description"><?= __('Güçlü API desteği ile üçüncü parti sistemlerle sorunsuz entegrasyon') ?></p>
                    </div>
                </div>
                
                <div class="feature-card bg-white shadow-lg" data-animate="slide-up" data-delay="800">
                    <div class="text-center">
                        <div class="feature-icon"><i class="fab fa-bitcoin text-purple-500"></i></div>
                        <h3 class="feature-title"><?= __('Crypto Ödeme Desteği') ?></h3>
                        <p class="feature-description"><?= __('USDT TRC20 ile güvenli ve hızlı blockchain ödemeleri') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section class="py-20 bg-gradient-purple-dark" id="pricing">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-animate="fade-in" data-delay="200">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6"><?= __('Hizmet Paketleri') ?></h2>
                <p class="text-xl text-purple-200 max-w-3xl mx-auto"><?= __('İhtiyaçlarınıza uygun VoIP panel ve agent hizmetlerimiz') ?></p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <div class="pricing-card" data-animate="slide-up" data-delay="300">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-gradient-purple rounded-2xl">
                            <i class="fas fa-headset text-3xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-2"><?= __('Agent Paketleri') ?></h3>
                        <div class="text-lg font-bold text-purple-300 mb-2"><?= __('Profesyonel agent hizmetleri') ?></div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('Agent Yönetimi') ?></li>
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('Abonelik Sistemi') ?></li>
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('Performans İzleme') ?></li>
                        </ul>
                        <a href="https://t.me/lionmw" target="_blank" class="btn-primary w-full"><?= __('Teklif Al') ?></a>
                    </div>
                </div>
                
                <div class="pricing-card-popular" data-animate="slide-up" data-delay="400">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gradient-to-r from-pink-500 to-purple-500 text-white px-4 py-1 rounded-full text-sm font-bold"><?= __('Popüler') ?></span>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 mb-6 bg-gradient-purple rounded-2xl">
                            <i class="fas fa-desktop text-3xl text-white"></i>
                        </div>
                        <h3 class="text-3xl font-bold text-white mb-2"><?= __('Panel Erişimi') ?></h3>
                        <div class="text-lg font-bold text-purple-300 mb-6"><?= __('Tam yönetim paneli erişimi') ?></div>
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('Çağrı Yönetimi') ?></li>
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('CDR Analizi') ?></li>
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('Grup Organizasyonu') ?></li>
                            <li class="flex items-center text-white"><i class="fas fa-check text-green-400 mr-3"></i><?= __('API Erişimi') ?></li>
                        </ul>
                        <a href="https://t.me/lionmw" target="_blank" class="btn-primary w-full"><?= __('Demo Talep Et') ?></a>
                    </div>
                </div>
            </div>
            
            <div class="mt-16 grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <div class="glass-card p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center">
                            <i class="fab fa-telegram text-2xl text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white"><?= __('Telegram İletişim') ?></h4>
                            <a href="https://t.me/lionmw" target="_blank" class="text-cyan-300 hover:text-cyan-200">@lionmw</a>
                        </div>
                    </div>
                </div>
                
                <div class="glass-card p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center">
                            <i class="fab fa-telegram text-2xl text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white"><?= __('Destek İletişimi') ?></h4>
                            <a href="https://t.me/Itsupportemre" target="_blank" class="text-emerald-300 hover:text-emerald-200">@Itsupportemre</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-purple rounded-xl flex items-center justify-center">
                        <i class="fas fa-phone-volume text-2xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gradient-purple">PapaM VoIP</h3>
                        <p class="text-gray-400 text-sm">Professional Communication Solutions</p>
                    </div>
                </div>
                <p class="text-gray-300 mb-6 max-w-2xl mx-auto">
                    <?= __('Gelişmiş VoIP panel yönetim sistemi ile çağrı yönetimi, agent takibi, bakiye kontrolü ve detaylı raporlama özelliklerini tek platformda sunuyoruz.') ?>
                </p>
                <div class="flex justify-center space-x-4 mb-8">
                    <a href="https://t.me/lionmw" target="_blank" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="https://t.me/Itsupportemre" target="_blank" class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20">
                        <i class="fab fa-telegram"></i>
                    </a>
                </div>
                <p class="text-gray-400">&copy; <?= date('Y') ?> PapaM VoIP. <?= __('Tüm hakları saklıdır.') ?></p>
            </div>
        </div>
    </footer>

    <script src="assets/js/landing-animations.js"></script>
</body>
</html>