</main>

  <footer class="relative overflow-hidden border-t border-white/10 py-10 text-center">
    <!-- Subtle background -->
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/10 via-purple-600/10 to-pink-600/10 pointer-events-none"></div>
    <div class="absolute top-4 left-1/4 w-20 h-20 bg-indigo-500/15 rounded-full blur-2xl pointer-events-none"></div>
    <div class="absolute bottom-4 right-1/4 w-24 h-24 bg-purple-500/15 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 z-10">

      <!-- Logo -->
      <div class="mb-6">
        <div class="inline-block relative">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 rounded-full blur-lg opacity-40 animate-pulse pointer-events-none"></div>
          <div class="relative p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-full animate-spin-slow">
            <i class="fa-solid fa-wave-square text-2xl text-white"></i>
          </div>
        </div>
        <h2 class="text-xl font-bold text-white mt-4 mb-1 rainbow-text"><?= __('papam_voip_panel') ?></h2>
        <p class="text-white/70 text-sm"><?= __('modern_communication_solutions') ?></p>
      </div>

      <!-- Contact -->
      <div class="mb-6">
        <h3 class="text-base font-semibold text-white/80 mb-3"><?= __('communication') ?></h3>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <a href="https://t.me/lionmw" target="_blank"
             class="group flex items-center gap-2 px-4 py-2 bg-white/10 rounded-xl border border-white/15 hover:bg-white/20 hover:border-cyan-300/40 transition-all duration-250 hover:scale-105">
            <i class="fab fa-telegram text-cyan-300 text-base"></i>
            <span class="text-white text-sm font-medium">@lionmw</span>
          </a>
          <a href="https://t.me/Itsupportemre" target="_blank"
             class="group flex items-center gap-2 px-4 py-2 bg-white/10 rounded-xl border border-white/15 hover:bg-white/20 hover:border-purple-300/40 transition-all duration-250 hover:scale-105">
            <i class="fab fa-telegram text-purple-300 text-base"></i>
            <span class="text-white text-sm font-medium">@Itsupportemre</span>
          </a>
        </div>
      </div>

      <!-- Install button (PWA) -->
      <div class="mb-5">
        <button id="install-btn" class="hidden group px-6 py-2.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white text-sm font-semibold rounded-xl hover:opacity-90 transition-opacity duration-200 shadow-lg">
          <i class="fa-solid fa-download mr-2"></i><?= __('download_app') ?>
        </button>
      </div>

      <!-- Copyright -->
      <p class="text-white/60 text-sm">© <?= date('Y') ?> <?= __('papam_voip_panel') ?></p>
    </div>
  </footer>

  <script>
    var deferredPrompt;
    window.addEventListener('beforeinstallprompt', function(e) {
      e.preventDefault();
      deferredPrompt = e;
      var btn = document.getElementById('install-btn');
      if (btn) btn.classList.remove('hidden');
    });
    var installBtn = document.getElementById('install-btn');
    if (installBtn) {
      installBtn.addEventListener('click', function() {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then(function() { deferredPrompt = null; });
        }
      });
    }
  </script>
</body>
</html>