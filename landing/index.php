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
    <header class="fixed top-0 left-0 w-full z-50 transition-all duration-500" id="main-header">
        <div class="glass-nav px-6 py-4">
            <nav class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-purple rounded-xl flex items-center justify-center animate-pulse-glow">
                            <i class="fas fa-phone-volume text-2xl text-white"></i>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full animate-ping"></div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gradient-purple">PapaM VoIP</h1>
                        <p class="text-xs text-purple-200">Professional Communication</p>
                    </div>
                </div>
                
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#features" class="nav-link"><?= __('Özellikler') ?></a>
                    <a href="#pricing" class="nav-link"><?= __('Fiyatlandırma') ?></a>
                    <a href="/panel/" class="nav-link"><?= __('Panel Girişi') ?></a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="nav-button flex items-center space-x-2">
                            <i class="fas fa-globe"></i>
                            <span><?= strtoupper($currentLang) ?></span>
                        </button>
                        <div class="absolute top-full right-0 mt-2 py-2 w-32 glass-card opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                            <form method="POST" action="">
                                <input type="hidden" name="lang" value="tr">
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-white/10 text-white">Türkçe</button>
                            </form>
                            <form method="POST" action="">
                                <input type="hidden" name="lang" value="en">
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-white/10 text-white">English</button>
                            </form>
                            <form method="POST" action="">
                                <input type="hidden" name="lang" value="ru">
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-white/10 text-white">Русский</button>
                            </form>
                        </div>
                    </div>
                    <a href="/panel/" class="btn-primary"><?= __('Panel Girişi') ?></a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-background" id="hero">
        <div class="hero-content">
            <div data-animate="fade-in" data-delay="500">
                <h1 class="hero-title">
                    <?= __('Profesyonel') ?>
                    <span class="text-gradient-purple"><?= __('VoIP Panel') ?></span>
                    <?= __('Yönetim Sistemi') ?>
                </h1>
                <p class="hero-subtitle">
                    <?= __('Gelişmiş VoIP çağrı yönetimi ve raporlama sistemi ile işletmenizi yönetin') ?>
                </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center mt-12" data-animate="slide-up" data-delay="800">
                <a href="/panel/" class="btn-primary flex items-center space-x-3 text-lg px-10 py-5">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                    <span><?= __('Panel Girişi') ?></span>
                </a>
                <a href="https://t.me/lionmw" target="_blank" class="btn-outline flex items-center space-x-3 text-lg px-8 py-4">
                    <i class="fab fa-telegram text-xl"></i>
                    <span><?= __('İletişim') ?></span>
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