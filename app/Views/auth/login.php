<?php $hideNav=true; $title='Giriş - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <!-- Particle Background -->
  <div class="particles" id="particles"></div>

  <div class="min-h-screen flex items-center justify-center relative z-10">
    <div class="w-full max-w-md p-4">
      <!-- Login Card -->
      <div class="glass-card login-card rounded-2xl shadow-2xl p-8">
        <!-- Logo Section -->
        <div class="logo-container text-center mb-8">
          <div class="relative inline-block">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full blur-xl opacity-30 animate-pulse"></div>
            <div class="relative p-4 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full floating">
              <i class="fa-solid fa-wave-square text-3xl text-white"></i>
            </div>
          </div>
          <h1 class="text-3xl font-bold text-white mt-4 text-glow drop-shadow-lg">PapaM VoIP Panel</h1>
          <p class="label-text text-base mt-2 font-medium">Modern İletişim Çözümleri</p>
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
        <form method="post" class="space-y-6" id="loginForm">
          <!-- Username Field -->
          <div class="input-group">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <i class="fa-solid fa-user text-indigo-300"></i>
              Kullanıcı Adı
            </label>
            <div class="relative">
              <input
                name="login"
                required
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-12 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 focus:bg-white/20 transition-all duration-300 placeholder-text"
                placeholder="Kullanıcı adınızı girin">
              <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 input-icon"></i>
            </div>
          </div>

          <!-- Password Field -->
          <div class="input-group">
            <label class="block label-text text-base font-semibold mb-3 flex items-center gap-2">
              <i class="fa-solid fa-lock text-indigo-300"></i>
              Şifre
            </label>
            <div class="relative">
              <input
                type="password"
                name="password"
                required
                class="form-input input-text w-full border-2 border-white/30 rounded-xl p-4 pl-12 pr-12 bg-white/15 backdrop-blur-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 focus:bg-white/20 transition-all duration-300 placeholder-text"
                placeholder="Şifrenizi girin">
              <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 input-icon"></i>
              <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 input-icon hover:text-white transition-colors" id="togglePassword">
                <i class="fa-solid fa-eye"></i>
              </button>
            </div>
          </div>

          <!-- Login Button -->
          <button
            type="submit"
            class="login-btn w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 text-white font-semibold rounded-xl p-4 hover:from-indigo-500 hover:via-purple-500 hover:to-blue-500 transform transition-all duration-300 shadow-lg hover:shadow-xl">
            <span class="flex items-center justify-center gap-2 button-text">
              <i class="fa-solid fa-right-to-bracket"></i>
              <span>Giriş Yap</span>
            </span>
          </button>
        </form>

        <!-- Footer Links -->
        <div class="mt-8 text-center">
          <a href="<?= \App\Helpers\Url::to('/install/') ?>" class="label-text text-base font-medium transition-colors duration-200 hover:underline">
            <i class="fa-solid fa-cog mr-2"></i>
            Kurulum
          </a>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-4 right-4 w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full opacity-20 blur-xl animate-pulse"></div>
        <div class="absolute bottom-4 left-4 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full opacity-20 blur-xl animate-pulse" style="animation-delay: 1s;"></div>
      </div>
    </div>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
