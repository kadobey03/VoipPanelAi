  </main>
  <footer class="border-t border-slate-200/40 dark:border-slate-700/40 py-6 text-center text-sm text-slate-500">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-center gap-4 mb-4">
        <button id="install-btn" class="hidden px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
          <i class="fa-solid fa-download"></i> Uygulamayı İndir
        </button>
      </div>
      <div class="mb-4">
        <h3 class="font-semibold mb-2">İletişim</h3>
        <div class="flex items-center justify-center gap-4">
          <a href="https://t.me/lionmw" target="_blank" class="flex items-center gap-1 hover:text-blue-500 transition">
            <i class="fab fa-telegram"></i> @lionmw
          </a>
          <a href="https://t.me/Itsupportemre" target="_blank" class="flex items-center gap-1 hover:text-blue-500 transition">
            <i class="fab fa-telegram"></i> @Itsupportemre
          </a>
        </div>
      </div>
      © <?= date('Y') ?> PapaM VoIP Panel
    </div>
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

