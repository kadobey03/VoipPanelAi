<?php $title='Agent Satın Al - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl shadow-lg">
              <i class="fa-solid fa-shopping-cart text-3xl text-white"></i>
            </div>
            <div>
              <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">Agent Satın Al</h1>
              <p class="text-lg text-slate-600 dark:text-slate-400 mt-2">Size uygun agent paketini seçin ve satın alın</p>
            </div>
          </div>
          
          <?php if ($isSuper): ?>
          <div class="flex gap-4">
            <a href="/VoipPanelAi/agents/manage-products" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-cog"></i>
              Ürün Yönetimi
            </a>
          </div>
          <?php endif; ?>
        </div>

        <!-- Balance Info -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-6 border border-blue-200/50 dark:border-blue-700/50">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-500 rounded-xl">
              <i class="fa-solid fa-wallet text-white text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-blue-900 dark:text-blue-200">Mevcut Bakiyeniz</h3>
              <p class="text-3xl font-bold text-blue-600 dark:text-blue-300">$<?php echo number_format($balance, 2); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-6 py-4 rounded-2xl">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-check-circle text-emerald-500"></i>
        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-6 py-4 rounded-2xl">
      <div class="flex items-center gap-3">
        <i class="fa-solid fa-exclamation-triangle text-red-500"></i>
        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
      </div>
    </div>
    <?php endif; ?>

    <!-- Current Agents -->
    <?php if (!empty($userAgents)): ?>
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl">
            <i class="fa-solid fa-headset text-white"></i>
          </div>
          Mevcut Agentleriniz
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($userAgents as $agent): ?>
          <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-700 dark:to-slate-600 rounded-xl p-6 border border-slate-200 dark:border-slate-600">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-bold text-slate-900 dark:text-white"><?php echo htmlspecialchars($agent['product_name']); ?></h3>
              <span class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 rounded-full text-sm font-medium">
                Aktif
              </span>
            </div>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-slate-600 dark:text-slate-400">Numara:</span>
                <span class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($agent['agent_number']); ?></span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-600 dark:text-slate-400">Satın Alma:</span>
                <span class="font-medium text-slate-900 dark:text-white"><?php echo date('d.m.Y', strtotime($agent['purchase_date'])); ?></span>
              </div>
              <?php if ($agent['next_subscription_due']): ?>
              <div class="flex justify-between">
                <span class="text-slate-600 dark:text-slate-400">Sonraki Ödeme:</span>
                <span class="font-medium text-slate-900 dark:text-white"><?php echo date('d.m.Y', strtotime($agent['next_subscription_due'])); ?></span>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Available Products -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-200/50 dark:border-slate-700/50">
      <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-3">
        <div class="p-2 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl">
          <i class="fa-solid fa-store text-white"></i>
        </div>
        Satın Alınabilir Agent Paketleri
      </h2>

      <?php if (empty($products)): ?>
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full mb-4">
          <i class="fa-solid fa-box-open text-2xl text-slate-400"></i>
        </div>
        <p class="text-xl text-slate-600 dark:text-slate-400">Şu anda satın alınabilir ürün bulunmuyor</p>
      </div>
      <?php else: ?>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <?php foreach ($products as $product): ?>
        <div class="bg-gradient-to-br from-white to-slate-50 dark:from-slate-700 dark:to-slate-600 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
          <!-- Header -->
          <div class="bg-gradient-to-r from-emerald-500 to-green-600 p-6 text-white">
            <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="text-emerald-100 leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>
          </div>

          <!-- Content -->
          <div class="p-6">
            <!-- Features -->
            <div class="space-y-4 mb-6">
              <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-5 h-5 bg-emerald-100 dark:bg-emerald-900/50 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-phone text-emerald-600 dark:text-emerald-400 text-xs"></i>
                </div>
                <div>
                  <span class="text-slate-900 dark:text-white font-medium"><?php echo htmlspecialchars($product['phone_prefix']); ?></span>
                  <span class="text-slate-600 dark:text-slate-400"> li numara</span>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-5 h-5 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-clock text-blue-600 dark:text-blue-400 text-xs"></i>
                </div>
                <div>
                  <span class="text-slate-900 dark:text-white font-medium">$<?php echo number_format($product['per_minute_cost'], 4); ?></span>
                  <span class="text-slate-600 dark:text-slate-400"> dakika başı ücret</span>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-5 h-5 bg-purple-100 dark:bg-purple-900/50 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-user text-purple-600 dark:text-purple-400 text-xs"></i>
                </div>
                <div>
                  <span class="text-slate-900 dark:text-white font-medium"><?php echo $product['is_single_user'] ? 'Tek kullanıcı' : 'Çoklu kullanıcı'; ?></span>
                </div>
              </div>

              <?php if ($product['is_callback_enabled']): ?>
              <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-5 h-5 bg-orange-100 dark:bg-orange-900/50 rounded-full flex items-center justify-center">
                  <i class="fa-solid fa-phone-flip text-orange-600 dark:text-orange-400 text-xs"></i>
                </div>
                <div>
                  <span class="text-slate-900 dark:text-white font-medium">Geri aranabilir</span>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- Pricing -->
            <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-4 mb-6">
              <div class="text-center">
                <div class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
                  $<?php echo number_format($product['price'], 2); ?>
                </div>
                <?php if ($product['is_subscription']): ?>
                <div class="text-sm text-slate-600 dark:text-slate-400 mb-2">Kurulum Ücreti</div>
                <div class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                  + $<?php echo number_format($product['subscription_monthly_fee'], 2); ?>/ay
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Aylık abonelik ücreti</div>
                <?php else: ?>
                <div class="text-sm text-green-600 dark:text-green-400 font-medium">Tek Satın Alma - Ömür Boyu</div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Purchase Button -->
            <form method="post" action="/VoipPanelAi/agents/purchase" class="mb-0">
              <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
              <button type="submit" 
                      class="w-full px-6 py-4 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 <?php echo ($balance < $product['price']) ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                      <?php echo ($balance < $product['price']) ? 'disabled' : ''; ?>>
                <div class="flex items-center justify-center gap-2">
                  <?php if ($balance < $product['price']): ?>
                    <i class="fa-solid fa-wallet text-lg"></i>
                    <span>Yetersiz Bakiye</span>
                  <?php else: ?>
                    <i class="fa-solid fa-shopping-cart text-lg"></i>
                    <span>Satın Al</span>
                  <?php endif; ?>
                </div>
              </button>
            </form>

            <?php if ($balance < $product['price']): ?>
            <div class="mt-3 text-center">
              <a href="/VoipPanelAi/balance/topup" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 text-sm font-medium">
                <i class="fa-solid fa-plus-circle mr-1"></i>
                Bakiye Yükle
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Purchase form confirmation
  const purchaseForms = document.querySelectorAll('form[action="/VoipPanelAi/agents/purchase"]');
  
  purchaseForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const productName = this.closest('.bg-gradient-to-br').querySelector('h3').textContent;
      const price = this.closest('.bg-gradient-to-br').querySelector('.text-3xl').textContent;
      
      if (!confirm(`"${productName}" ürününü ${price} karşılığında satın almak istediğinizden emin misiniz?`)) {
        e.preventDefault();
      }
    });
  });
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>