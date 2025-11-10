<?php $hideNav=true; $title='Giriş - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <script>
    // Login sayfası için dark mode'u zorla
    document.documentElement.classList.add('dark');
    document.body.classList.add('dark');
    document.body.style.background = 'linear-gradient(45deg, #0f172a 0%, #1e293b 25%, #334155 50%, #475569 75%, #64748b 100%)';
    document.body.style.color = '#ffffff';
    document.body.style.backgroundSize = '400% 400%';
    document.body.style.animation = 'gradientShift 15s ease infinite';
  </script>
  <!-- Particle Background -->
  <div class="particles" id="particles"></div>

  <div class="min-h-screen flex items-center justify-center relative z-10" style="background: transparent;">
    <div class="w-full max-w-md p-4">
      <!-- Login Card -->
      <div class="glass-card login-card rounded-2xl shadow-2xl p-8">
        <!-- Logo Section -->
        <div class="logo-container text-center mb-8">
          <div class="relative inline-block">
            <!-- Multiple animated backgrounds -->
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 rounded-full blur-xl opacity-40 animate-pulse"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full blur-lg opacity-30 animate-bounce" style="animation-delay: 0.5s; animation-duration: 3s;"></div>

            <!-- Main logo with enhanced animation -->
            <div class="relative p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-full floating animate-spin-slow">
              <i class="fa-solid fa-wave-square text-3xl text-white animate-pulse"></i>
            </div>

            <!-- Decorative orbiting icons -->
            <div class="absolute inset-0 animate-spin" style="animation-duration: 20s;">
              <i class="fa-solid fa-phone text-cyan-300 text-lg absolute top-0 left-1/2 transform -translate-x-1/2 animate-bounce" style="animation-delay: 0s;"></i>
              <i class="fa-solid fa-message text-purple-300 text-sm absolute bottom-0 left-0 animate-pulse" style="animation-delay: 1s;"></i>
              <i class="fa-solid fa-headset text-pink-300 text-sm absolute bottom-0 right-0 animate-bounce" style="animation-delay: 2s;"></i>
              <i class="fa-solid fa-microphone text-blue-300 text-sm absolute top-0 right-0 animate-pulse" style="animation-delay: 3s;"></i>
            </div>
          </div>

          <!-- Enhanced title with rainbow effect -->
          <h1 class="text-4xl font-bold text-white mt-6 text-glow drop-shadow-lg animate-fade-in-up rainbow-text"><?= __('papam_voip_panel') ?></h1>
          <p class="label-text text-lg mt-3 font-medium animate-fade-in-up" style="animation-delay: 0.3s;"><?= __('modern_communication_solutions') ?></p>

          <!-- Additional decorative elements -->
          <div class="flex justify-center gap-4 mt-4">
            <div class="w-3 h-3 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full animate-ping"></div>
            <div class="w-3 h-3 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
            <div class="w-3 h-3 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-ping" style="animation-delay: 1s;"></div>
          </div>
        </div>

        <!-- Language Selector -->
        <div class="language-selector mb-6 animate-fade-in-up" style="animation-delay: 0.4s;">
          <div class="flex justify-center">
            <div class="relative inline-block">
              <select
                id="languageSelect"
                onchange="changeLanguage(this.value)"
                class="appearance-none bg-white/10 backdrop-blur-sm border-2 border-white/20 rounded-xl px-4 py-2 pr-8 text-white text-sm font-medium hover:bg-white/15 hover:border-white/30 focus:bg-white/20 focus:border-cyan-300 focus:outline-none transition-all duration-300 cursor-pointer">
                <option value="tr" <?= ($_SESSION['lang'] ?? 'en') == 'tr' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇹🇷 <?= __('turkish') ?></option>
                <option value="en" <?= ($_SESSION['lang'] ?? 'en') == 'en' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇺🇸 <?= __('english') ?></option>
                <option value="ru" <?= ($_SESSION['lang'] ?? 'en') == 'ru' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇷🇺 <?= __('russian') ?></option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <i class="fa-solid fa-chevron-down text-white/70 text-xs"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
          <div class="error-message mb-6 p-4 rounded-xl bg-red-500/30 border-2 border-red-500/50 text-white font-semibold">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-exclamation-triangle text-red-300"></i>
              <span class="text-base label-text"><?= htmlspecialchars($error) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="/VoipPanelAi/login" class="space-y-6" id="loginForm">
          <!-- Username Field -->
          <div class="input-group animate-scale-in" style="animation-delay: 0.1s;">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <div class="relative">
                <i class="fa-solid fa-user text-cyan-300 animate-pulse"></i>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-cyan-400 rounded-full animate-ping"></div>
              </div>
              <?= __('username') ?>
            </label>
            <div class="relative group">
              <input
                name="login"
                required
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-14 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-cyan-300 focus:border-cyan-300 focus:bg-white/20 transition-all duration-300 placeholder-text hover:border-cyan-400 hover:shadow-lg hover:shadow-cyan-500/25"
                placeholder="<?= __('enter_username') ?>">
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-user input-icon group-hover:text-cyan-300 group-hover:animate-bounce transition-all duration-300"></i>
              </div>
              <!-- Decorative corner elements -->
              <div class="absolute top-2 left-2 w-1 h-1 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full opacity-60"></div>
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full opacity-60"></div>
            </div>
          </div>

          <!-- Password Field -->
          <div class="input-group animate-scale-in" style="animation-delay: 0.2s;">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <div class="relative">
                <i class="fa-solid fa-lock text-purple-300 animate-pulse"></i>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-purple-400 rounded-full animate-ping"></div>
              </div>
              <?= __('password') ?>
            </label>
            <div class="relative group">
              <input
                type="password"
                name="password"
                required
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-14 pr-14 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-purple-300 focus:border-purple-300 focus:bg-white/20 transition-all duration-300 placeholder-text hover:border-purple-400 hover:shadow-lg hover:shadow-purple-500/25"
                placeholder="<?= __('enter_password') ?>">
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-lock input-icon group-hover:text-purple-300 group-hover:animate-bounce transition-all duration-300"></i>
              </div>
              <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 input-icon hover:text-white transition-colors group-hover:scale-110" id="togglePassword">
                <i class="fa-solid fa-eye"></i>
              </button>
              <!-- Decorative corner elements -->
              <div class="absolute top-2 left-2 w-1 h-1 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full opacity-60"></div>
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full opacity-60"></div>
            </div>
          </div>

          <!-- Login Button -->
          <div class="relative animate-scale-in" style="animation-delay: 0.3s;">
            <button
              type="submit"
              class="login-btn w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-semibold rounded-xl p-4 hover:from-indigo-500 hover:via-purple-500 hover:to-pink-500 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-purple-500/50 relative overflow-hidden group">
              <span class="flex items-center justify-center gap-2 button-text relative z-10">
                <i class="fa-solid fa-right-to-bracket group-hover:animate-bounce"></i>
                <span><?= __('login_button') ?></span>
              </span>
              <!-- Button shine effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
              <!-- Decorative particles -->
              <div class="absolute top-2 left-4 w-1 h-1 bg-white/60 rounded-full animate-ping"></div>
              <div class="absolute top-2 right-8 w-1 h-1 bg-white/60 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
              <div class="absolute bottom-2 left-8 w-1 h-1 bg-white/60 rounded-full animate-ping" style="animation-delay: 1s;"></div>
            </button>
          </div>
        </form>

        <script>
          function changeLanguage(lang) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/VoipPanelAi/change-lang';
            
            const langInput = document.createElement('input');
            langInput.type = 'hidden';
            langInput.name = 'lang';
            langInput.value = lang;
            
            form.appendChild(langInput);
            document.body.appendChild(form);
            form.submit();
          }
        </script>

        <!-- Enhanced Decorative Elements -->
        <!-- Floating geometric shapes -->
        <div class="absolute top-6 right-6 w-20 h-20 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full opacity-30 blur-xl animate-bounce"></div>
        <div class="absolute bottom-6 left-6 w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-full opacity-30 blur-xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-8 w-8 h-8 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg opacity-40 blur-lg animate-ping" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/3 right-8 w-6 h-6 bg-gradient-to-br from-green-400 to-teal-500 rounded-full opacity-40 blur-md animate-pulse" style="animation-delay: 3s;"></div>

        <!-- Animated border elements -->
        <div class="absolute top-0 left-1/4 w-2 h-2 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full animate-spin" style="animation-duration: 2s;"></div>
        <div class="absolute top-0 right-1/4 w-2 h-2 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full animate-spin" style="animation-duration: 3s;"></div>
        <div class="absolute bottom-0 left-1/3 w-2 h-2 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-spin" style="animation-duration: 2.5s;"></div>
        <div class="absolute bottom-0 right-1/3 w-2 h-2 bg-gradient-to-r from-green-400 to-teal-500 rounded-full animate-spin" style="animation-duration: 3.5s;"></div>

        <!-- Sparkle effects -->
        <div class="absolute top-8 left-8 text-yellow-300 animate-pulse">
          <i class="fa-solid fa-star text-sm"></i>
        </div>
        <div class="absolute bottom-8 right-8 text-cyan-300 animate-pulse" style="animation-delay: 1.5s;">
          <i class="fa-solid fa-sparkles text-sm"></i>
        </div>
        <div class="absolute top-1/2 right-12 text-purple-300 animate-bounce" style="animation-delay: 2.5s;">
          <i class="fa-solid fa-circle text-xs"></i>
        </div>
      </div>
    </div>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
