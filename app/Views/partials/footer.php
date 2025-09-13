  </main>

  <!-- Enhanced Footer with Animations -->
  <footer class="relative overflow-hidden border-t border-white/20 py-12 text-center">
    <!-- Animated Background Particles -->
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/20 via-purple-600/20 to-pink-600/20"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-transparent via-white/5 to-transparent animate-pulse"></div>

    <!-- Floating Particles Container -->
    <div class="footer-particles absolute inset-0 pointer-events-none"></div>

    <!-- Decorative Elements -->
    <div class="absolute top-4 left-1/4 w-16 h-16 bg-gradient-to-br from-cyan-400/30 to-blue-500/30 rounded-full blur-xl animate-bounce"></div>
    <div class="absolute bottom-4 right-1/4 w-20 h-20 bg-gradient-to-br from-purple-400/30 to-pink-500/30 rounded-full blur-xl animate-pulse"></div>
    <div class="absolute top-1/2 left-8 w-8 h-8 bg-gradient-to-br from-yellow-400/30 to-orange-500/30 rounded-lg blur-lg animate-ping"></div>
    <div class="absolute top-1/3 right-8 w-6 h-6 bg-gradient-to-br from-green-400/30 to-teal-500/30 rounded-full blur-md animate-pulse"></div>

    <!-- Main Content -->
    <div class="relative max-w-7xl mx-auto px-4 z-10">
      <!-- Animated Logo Section -->
      <div class="mb-8">
        <div class="inline-block relative">
          <!-- Multiple background layers with color rotation -->
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 rounded-full blur-xl opacity-50 animate-pulse"></div>
          <div class="absolute inset-0 bg-gradient-to-r from-cyan-400 to-blue-600 rounded-full blur-lg opacity-40 animate-bounce" style="animation-delay: 0.5s;"></div>

          <!-- Animated logo -->
          <div class="relative p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-full footer-logo animate-spin-slow">
            <i class="fa-solid fa-wave-square text-2xl text-white animate-pulse"></i>
          </div>

          <!-- Orbiting decorative elements -->
          <div class="absolute inset-0 animate-spin" style="animation-duration: 25s;">
            <i class="fa-solid fa-phone text-cyan-300 text-sm absolute top-0 left-1/2 transform -translate-x-1/2 animate-bounce" style="animation-delay: 0s;"></i>
            <i class="fa-solid fa-message text-purple-300 text-xs absolute bottom-0 left-0 animate-pulse" style="animation-delay: 1s;"></i>
            <i class="fa-solid fa-headset text-pink-300 text-xs absolute bottom-0 right-0 animate-bounce" style="animation-delay: 2s;"></i>
            <i class="fa-solid fa-microphone text-blue-300 text-xs absolute top-0 right-0 animate-pulse" style="animation-delay: 3s;"></i>
          </div>
        </div>

        <!-- Animated brand text -->
        <h2 class="text-2xl font-bold text-white mt-4 mb-2 footer-rainbow-text animate-fade-in-up"><?= __('papam_voip_panel') ?></h2>
        <p class="text-white/80 text-sm animate-fade-in-up" style="animation-delay: 0.2s;"><?= __('modern_communication_solutions') ?></p>

        <!-- Animated dots -->
        <div class="flex justify-center gap-3 mt-4">
          <div class="w-2 h-2 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full animate-ping"></div>
          <div class="w-2 h-2 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full animate-ping" style="animation-delay: 0.3s;"></div>
          <div class="w-2 h-2 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-ping" style="animation-delay: 0.6s;"></div>
          <div class="w-2 h-2 bg-gradient-to-r from-green-400 to-teal-500 rounded-full animate-ping" style="animation-delay: 0.9s;"></div>
        </div>
      </div>

      <!-- Enhanced Contact Section -->
      <div class="mb-8">
        <h3 class="text-xl font-bold text-white mb-4 footer-rainbow-text animate-fade-in-up" style="animation-delay: 0.3s;"><?= __('communication') ?></h3>
        <div class="flex flex-wrap items-center justify-center gap-6">
          <!-- Telegram Links with Enhanced Animations -->
          <a href="https://t.me/lionmw" target="_blank"
             class="footer-contact-link group relative flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/20 hover:border-cyan-300/50 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/25 animate-scale-in">
            <div class="relative">
              <i class="fab fa-telegram text-cyan-300 text-lg group-hover:animate-bounce"></i>
              <div class="absolute -top-1 -right-1 w-2 h-2 bg-cyan-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <span class="text-white font-medium group-hover:text-cyan-200 transition-colors">@lionmw</span>
          </a>

          <a href="https://t.me/Itsupportemre" target="_blank"
             class="footer-contact-link group relative flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/20 hover:border-purple-300/50 transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-purple-500/25 animate-scale-in" style="animation-delay: 0.1s;">
            <div class="relative">
              <i class="fab fa-telegram text-purple-300 text-lg group-hover:animate-bounce"></i>
              <div class="absolute -top-1 -right-1 w-2 h-2 bg-purple-400 rounded-full animate-ping opacity-75"></div>
            </div>
            <span class="text-white font-medium group-hover:text-purple-200 transition-colors">@Itsupportemre</span>
          </a>
        </div>
      </div>

      <!-- Enhanced Install Button -->
      <div class="mb-6">
        <button id="install-btn" class="hidden group relative px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-indigo-500 hover:via-purple-500 hover:to-pink-500 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl hover:shadow-purple-500/50 overflow-hidden">
          <span class="flex items-center justify-center gap-2 relative z-10">
            <i class="fa-solid fa-download group-hover:animate-bounce"></i>
            <span><?= __('download_app') ?></span>
          </span>
          <!-- Shine effect -->
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
        </button>
      </div>

      <!-- Copyright with Glow Effect -->
      <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent blur-sm"></div>
        <p class="relative text-white/90 font-medium animate-fade-in-up footer-rainbow-text" style="animation-delay: 0.5s;">
          © <?= date('Y') ?> <?= __('papam_voip_panel') ?>
        </p>
      </div>

      <!-- Sparkle Effects -->
      <div class="absolute top-6 right-6 text-yellow-300 animate-pulse">
        <i class="fa-solid fa-star text-sm"></i>
      </div>
      <div class="absolute bottom-6 left-6 text-purple-300 animate-pulse" style="animation-delay: 1.5s;">
        <i class="fa-solid fa-sparkles text-sm"></i>
      </div>
      <div class="absolute top-1/4 right-12 text-cyan-300 animate-bounce" style="animation-delay: 2.5s;">
        <i class="fa-solid fa-circle text-xs"></i>
      </div>
    </div>

    <!-- Additional decorative particles -->
    <div class="absolute top-0 left-1/4 w-2 h-2 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full animate-spin" style="animation-duration: 2s;"></div>
    <div class="absolute top-0 right-1/4 w-2 h-2 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full animate-spin" style="animation-duration: 3s;"></div>
    <div class="absolute bottom-0 left-1/3 w-2 h-2 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-spin" style="animation-duration: 2.5s;"></div>
    <div class="absolute bottom-0 right-1/3 w-2 h-2 bg-gradient-to-r from-green-400 to-teal-500 rounded-full animate-spin" style="animation-duration: 3.5s;"></div>
  </footer>
  <script>
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      document.getElementById('install-btn').classList.remove('hidden');
    });
    document.getElementById('install-btn').addEventListener('click', () => {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === 'accepted') {
            console.log('User accepted the install prompt');
          }
          deferredPrompt = null;
        });
      }
    });
  </script>
</body>
</html>

