<?php $hideNav=true; $title=__('register_title').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <script>
    // Register sayfası için dark mode'u zorla
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
      <!-- Register Card -->
      <div class="glass-card login-card rounded-2xl shadow-2xl p-8">
        <!-- Logo Section -->
        <div class="logo-container text-center mb-8">
          <div class="relative inline-block">
            <!-- Multiple animated backgrounds -->
            <div class="absolute inset-0 bg-gradient-to-r from-green-500 via-emerald-600 to-teal-600 rounded-full blur-xl opacity-40 animate-pulse"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full blur-lg opacity-30 animate-bounce" style="animation-delay: 0.5s; animation-duration: 3s;"></div>

            <!-- Main logo with enhanced animation -->
            <div class="relative p-4 bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-full floating animate-spin-slow">
              <i class="fa-solid fa-user-plus text-3xl text-white animate-pulse"></i>
            </div>

            <!-- Decorative orbiting icons -->
            <div class="absolute inset-0 animate-spin" style="animation-duration: 20s;">
              <i class="fa-solid fa-envelope text-cyan-300 text-lg absolute top-0 left-1/2 transform -translate-x-1/2 animate-bounce" style="animation-delay: 0s;"></i>
              <i class="fa-solid fa-shield-halved text-green-300 text-sm absolute bottom-0 left-0 animate-pulse" style="animation-delay: 1s;"></i>
              <i class="fa-solid fa-key text-emerald-300 text-sm absolute bottom-0 right-0 animate-bounce" style="animation-delay: 2s;"></i>
              <i class="fa-solid fa-user-check text-teal-300 text-sm absolute top-0 right-0 animate-pulse" style="animation-delay: 3s;"></i>
            </div>
          </div>

          <!-- Enhanced title with rainbow effect -->
          <h1 class="text-4xl font-bold text-white mt-6 text-glow drop-shadow-lg animate-fade-in-up rainbow-text"><?= __('register_title') ?></h1>
          <p class="label-text text-lg mt-3 font-medium animate-fade-in-up" style="animation-delay: 0.3s;"><?= __('create_account_subtitle') ?></p>

          <!-- Additional decorative elements -->
          <div class="flex justify-center gap-4 mt-4">
            <div class="w-3 h-3 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-ping"></div>
            <div class="w-3 h-3 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
            <div class="w-3 h-3 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full animate-ping" style="animation-delay: 1s;"></div>
          </div>
        </div>

        <!-- Language Selector -->
        <div class="language-selector mb-6 animate-fade-in-up" style="animation-delay: 0.4s;">
          <div class="flex justify-center">
            <div class="relative inline-block">
              <select
                id="languageSelect"
                onchange="changeLanguage(this.value)"
                class="appearance-none bg-white/10 backdrop-blur-sm border-2 border-white/20 rounded-xl px-4 py-2 pr-8 text-white text-sm font-medium hover:bg-white/15 hover:border-white/30 focus:bg-white/20 focus:border-emerald-300 focus:outline-none transition-all duration-300 cursor-pointer">
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

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <div class="error-message mb-4 p-4 rounded-xl bg-red-500/30 border-2 border-red-500/50 text-white font-semibold">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-exclamation-triangle text-red-300"></i>
                <span class="text-base label-text"><?= htmlspecialchars($error) ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($success)): ?>
          <div class="mb-6 p-4 rounded-xl bg-green-500/30 border-2 border-green-500/50 text-white font-semibold">
            <div class="flex items-center gap-2">
              <i class="fa-solid fa-check-circle text-green-300"></i>
              <span class="text-base label-text"><?= htmlspecialchars($success) ?></span>
            </div>
          </div>
        <?php endif; ?>

        <!-- Register Form -->
        <form method="post" action="/VoiPanelAi/register" class="space-y-6" id="registerForm">
          <!-- Username Field -->
          <div class="input-group animate-scale-in" style="animation-delay: 0.1s;">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <div class="relative">
                <i class="fa-solid fa-user text-emerald-300 animate-pulse"></i>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-emerald-400 rounded-full animate-ping"></div>
              </div>
              <?= __('username') ?>
            </label>
            <div class="relative group">
              <input
                name="login"
                required
                minlength="3"
                maxlength="50"
                value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-14 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 focus:bg-white/20 transition-all duration-300 placeholder-text hover:border-emerald-400 hover:shadow-lg hover:shadow-emerald-500/25"
                placeholder="<?= __('enter_username') ?>">
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-user input-icon group-hover:text-emerald-300 group-hover:animate-bounce transition-all duration-300"></i>
              </div>
              <!-- Decorative corner elements -->
              <div class="absolute top-2 left-2 w-1 h-1 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full opacity-60"></div>
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full opacity-60"></div>
            </div>
          </div>

          <!-- Email Field (Optional) -->
          <div class="input-group animate-scale-in" style="animation-delay: 0.15s;">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <div class="relative">
                <i class="fa-solid fa-envelope text-cyan-300 animate-pulse"></i>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-cyan-400 rounded-full animate-ping"></div>
              </div>
              <?= __('email') ?> <span class="text-gray-400 text-sm">(<?= __('optional') ?>)</span>
            </label>
            <div class="relative group">
              <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-14 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-cyan-300 focus:border-cyan-300 focus:bg-white/20 transition-all duration-300 placeholder-text hover:border-cyan-400 hover:shadow-lg hover:shadow-cyan-500/25"
                placeholder="<?= __('enter_email') ?>">
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-envelope input-icon group-hover:text-cyan-300 group-hover:animate-bounce transition-all duration-300"></i>
              </div>
              <!-- Decorative corner elements -->
              <div class="absolute top-2 left-2 w-1 h-1 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full opacity-60"></div>
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-full opacity-60"></div>
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
                minlength="6"
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
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full opacity-60"></div>
            </div>
          </div>

          <!-- Confirm Password Field -->
          <div class="input-group animate-scale-in" style="animation-delay: 0.25s;">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <div class="relative">
                <i class="fa-solid fa-shield-halved text-pink-300 animate-pulse"></i>
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-pink-400 rounded-full animate-ping"></div>
              </div>
              <?= __('confirm_password') ?>
            </label>
            <div class="relative group">
              <input
                type="password"
                name="confirm_password"
                required
                minlength="6"
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-14 pr-14 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-pink-300 focus:border-pink-300 focus:bg-white/20 transition-all duration-300 placeholder-text hover:border-pink-400 hover:shadow-lg hover:shadow-pink-500/25"
                placeholder="<?= __('confirm_password_placeholder') ?>">
              <div class="absolute left-4 top-1/2 -translate-y-1/2">
                <i class="fa-solid fa-shield-halved input-icon group-hover:text-pink-300 group-hover:animate-bounce transition-all duration-300"></i>
              </div>
              <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 input-icon hover:text-white transition-colors group-hover:scale-110" id="toggleConfirmPassword">
                <i class="fa-solid fa-eye"></i>
              </button>
              <!-- Decorative corner elements -->
              <div class="absolute top-2 left-2 w-1 h-1 bg-gradient-to-br from-pink-400 to-rose-500 rounded-full opacity-60"></div>
              <div class="absolute top-2 right-2 w-1 h-1 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full opacity-60"></div>
            </div>
          </div>

          <!-- Register Button -->
          <div class="relative animate-scale-in" style="animation-delay: 0.3s;">
            <button
              type="submit"
              class="login-btn w-full bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 text-white font-semibold rounded-xl p-4 hover:from-green-500 hover:via-emerald-500 hover:to-teal-500 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-emerald-500/50 relative overflow-hidden group">
              <span class="flex items-center justify-center gap-2 button-text relative z-10">
                <i class="fa-solid fa-user-plus group-hover:animate-bounce"></i>
                <span><?= __('register_button') ?></span>
              </span>
              <!-- Button shine effect -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
              <!-- Decorative particles -->
              <div class="absolute top-2 left-4 w-1 h-1 bg-white/60 rounded-full animate-ping"></div>
              <div class="absolute top-2 right-8 w-1 h-1 bg-white/60 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
              <div class="absolute bottom-2 left-8 w-1 h-1 bg-white/60 rounded-full animate-ping" style="animation-delay: 1s;"></div>
            </button>
          </div>

          <!-- Login Link -->
          <div class="text-center animate-fade-in-up" style="animation-delay: 0.4s;">
            <p class="label-text text-base">
              <?= __('already_have_account') ?> 
              <a href="/panel/login" class="text-emerald-300 hover:text-emerald-200 font-semibold underline transition-colors duration-300">
                <?= __('login_here') ?>
              </a>
            </p>
          </div>
        </form>

        <script>
          function changeLanguage(lang) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/panel/change-lang';
            
            const langInput = document.createElement('input');
            langInput.type = 'hidden';
            langInput.name = 'lang';
            langInput.value = lang;
            
            form.appendChild(langInput);
            document.body.appendChild(form);
            form.submit();
          }

          // Password toggle functionality
          document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.querySelector('input[name="password"]');
            const icon = this.querySelector('i');
            if (passwordField.type === 'password') {
              passwordField.type = 'text';
              icon.classList.remove('fa-eye');
              icon.classList.add('fa-eye-slash');
            } else {
              passwordField.type = 'password';
              icon.classList.remove('fa-eye-slash');
              icon.classList.add('fa-eye');
            }
          });

          document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordField = document.querySelector('input[name="confirm_password"]');
            const icon = this.querySelector('i');
            if (passwordField.type === 'password') {
              passwordField.type = 'text';
              icon.classList.remove('fa-eye');
              icon.classList.add('fa-eye-slash');
            } else {
              passwordField.type = 'password';
              icon.classList.remove('fa-eye-slash');
              icon.classList.add('fa-eye');
            }
          });
        </script>

        <!-- Enhanced Decorative Elements -->
        <!-- Floating geometric shapes -->
        <div class="absolute top-6 right-6 w-20 h-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full opacity-30 blur-xl animate-bounce"></div>
        <div class="absolute bottom-6 left-6 w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full opacity-30 blur-xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-8 w-8 h-8 bg-gradient-to-br from-cyan-400 to-teal-500 rounded-lg opacity-40 blur-lg animate-ping" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/3 right-8 w-6 h-6 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full opacity-40 blur-md animate-pulse" style="animation-delay: 3s;"></div>

        <!-- Animated border elements -->
        <div class="absolute top-0 left-1/4 w-2 h-2 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full animate-spin" style="animation-duration: 2s;"></div>
        <div class="absolute top-0 right-1/4 w-2 h-2 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-spin" style="animation-duration: 3s;"></div>
        <div class="absolute bottom-0 left-1/3 w-2 h-2 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full animate-spin" style="animation-duration: 2.5s;"></div>
        <div class="absolute bottom-0 right-1/3 w-2 h-2 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full animate-spin" style="animation-duration: 3.5s;"></div>

        <!-- Sparkle effects -->
        <div class="absolute top-8 left-8 text-emerald-300 animate-pulse">
          <i class="fa-solid fa-star text-sm"></i>
        </div>
        <div class="absolute bottom-8 right-8 text-teal-300 animate-pulse" style="animation-delay: 1.5s;">
          <i class="fa-solid fa-sparkles text-sm"></i>
        </div>
        <div class="absolute top-1/2 right-12 text-green-300 animate-bounce" style="animation-delay: 2.5s;">
          <i class="fa-solid fa-circle text-xs"></i>
        </div>
      </div>
    </div>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>