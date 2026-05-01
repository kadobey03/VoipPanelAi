<?php $hideNav=true; $title=__('login_title').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
<script>
  document.documentElement.classList.add('dark');
  document.body.classList.add('dark','login-background');
</script>

<!-- Particle Background -->
<div class="particles" id="particles"></div>

<div class="min-h-screen flex items-center justify-center relative z-10">
  <div class="w-full max-w-md p-4">
    <div class="glass-card login-card rounded-2xl shadow-2xl p-8 relative overflow-hidden">

      <!-- Subtle corner glows (static, no animation) -->
      <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="absolute bottom-0 left-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Logo -->
      <div class="logo-container text-center mb-8">
        <div class="inline-block relative">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-600 to-pink-600 rounded-full blur-lg opacity-40 animate-pulse pointer-events-none"></div>
          <div class="relative p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-full floating animate-spin-slow">
            <i class="fa-solid fa-wave-square text-3xl text-white"></i>
          </div>
          <!-- Orbiting icons -->
          <div class="absolute inset-0 animate-spin" style="animation-duration:20s;will-change:transform;">
            <i class="fa-solid fa-phone text-cyan-300 text-sm absolute top-0 left-1/2 -translate-x-1/2"></i>
            <i class="fa-solid fa-message text-purple-300 text-xs absolute bottom-0 left-0"></i>
            <i class="fa-solid fa-headset text-pink-300 text-xs absolute bottom-0 right-0"></i>
            <i class="fa-solid fa-microphone text-blue-300 text-xs absolute top-0 right-0"></i>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-white mt-6 text-glow animate-fade-in-up rainbow-text"><?= __('papam_voip_panel') ?></h1>
        <p class="label-text text-base mt-2 font-medium animate-fade-in-up" style="animation-delay:0.2s;"><?= __('modern_communication_solutions') ?></p>
      </div>

      <!-- Language Selector -->
      <div class="mb-6 flex justify-center animate-fade-in-up" style="animation-delay:0.3s;">
        <div class="relative inline-block">
          <select id="languageSelect" onchange="changeLanguage(this.value)"
            class="appearance-none bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-4 py-2 pr-8 text-white text-sm font-medium hover:bg-white/15 focus:bg-white/20 focus:border-cyan-300 focus:outline-none transition-all duration-200 cursor-pointer">
            <option value="tr" <?= ($_SESSION['lang'] ?? 'en') == 'tr' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇹🇷 <?= __('turkish') ?></option>
            <option value="en" <?= ($_SESSION['lang'] ?? 'en') == 'en' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇺🇸 <?= __('english') ?></option>
            <option value="ru" <?= ($_SESSION['lang'] ?? 'en') == 'ru' ? 'selected' : '' ?> class="bg-gray-800 text-white">🇷🇺 <?= __('russian') ?></option>
          </select>
          <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
            <i class="fa-solid fa-chevron-down text-white/60 text-xs"></i>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <?php if (!empty($error)): ?>
      <div class="error-message mb-5 p-4 rounded-xl bg-red-500/25 border border-red-500/40 text-white font-semibold">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-exclamation-triangle text-red-300"></i>
          <span class="label-text"><?= $error ?></span>
        </div>
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form method="post" action="<?= \App\Helpers\Url::to('/login') ?>" class="space-y-5" id="loginForm">

        <!-- Username -->
        <div class="input-group">
          <label class="block label-text text-sm font-semibold mb-2 flex items-center gap-2">
            <i class="fa-solid fa-user text-cyan-300"></i>
            <?= __('username') ?>
          </label>
          <div class="relative">
            <input name="login" required
              class="form-input input-text w-full border border-white/25 rounded-xl p-3.5 pl-12 bg-white/10 backdrop-blur-sm focus:ring-2 focus:ring-cyan-400/50 focus:border-cyan-400 focus:bg-white/15 transition-all duration-200 placeholder-text"
              placeholder="<?= __('enter_username') ?>">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
              <i class="fa-solid fa-user input-icon text-sm"></i>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="input-group">
          <label class="block label-text text-sm font-semibold mb-2 flex items-center gap-2">
            <i class="fa-solid fa-lock text-purple-300"></i>
            <?= __('password') ?>
          </label>
          <div class="relative">
            <input type="password" name="password" required
              class="form-input input-text w-full border border-white/25 rounded-xl p-3.5 pl-12 pr-12 bg-white/10 backdrop-blur-sm focus:ring-2 focus:ring-purple-400/50 focus:border-purple-400 focus:bg-white/15 transition-all duration-200 placeholder-text"
              placeholder="<?= __('enter_password') ?>">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
              <i class="fa-solid fa-lock input-icon text-sm"></i>
            </div>
            <button type="button" id="togglePassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 input-icon hover:text-white transition-colors duration-150">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <!-- Submit -->
        <div class="animate-scale-in" style="animation-delay:0.3s;">
          <button type="submit"
            class="login-btn w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-semibold rounded-xl p-4 hover:from-indigo-500 hover:via-purple-500 hover:to-pink-500 hover:scale-[1.02] transition-all duration-200 shadow-lg hover:shadow-purple-500/40 relative overflow-hidden">
            <span class="flex items-center justify-center gap-2 button-text relative z-10">
              <i class="fa-solid fa-right-to-bracket"></i>
              <span><?= __('login_button') ?></span>
            </span>
          </button>
        </div>
      </form>

      <!-- Register link -->
      <div class="text-center mt-6 animate-fade-in-up" style="animation-delay:0.45s;">
        <div class="h-px w-full bg-gradient-to-r from-transparent via-white/20 to-transparent mb-4"></div>
        <p class="label-text text-white/60 text-sm mb-3">
          <i class="fa-solid fa-user-plus text-cyan-300 mr-1"></i><?= __('no_account') ?>
        </p>
        <a href="<?= \App\Helpers\Url::to('/register') ?>"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-cyan-500/30">
          <i class="fa-solid fa-user-plus"></i>
          <span><?= __('sign_up_here') ?></span>
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function changeLanguage(lang) {
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= \App\Helpers\Url::to('/change-lang') ?>';
  var input = document.createElement('input');
  input.type = 'hidden'; input.name = 'lang'; input.value = lang;
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
}
</script>
<script src="<?= \App\Helpers\Url::to('/assets/js/login.js') ?>" defer></script>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>