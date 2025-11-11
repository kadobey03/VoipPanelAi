<?php $title=__('agent_product_management_title').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl shadow-lg">
              <i class="fa-solid fa-cogs text-3xl text-white"></i>
            </div>
            <div>
              <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">Agent Ürün Yönetimi</h1>
              <p class="text-lg text-slate-600 dark:text-slate-400 mt-2">Agent satın alma ürünlerini yönetin</p>
            </div>
          </div>
          
          <div class="flex gap-4">
            <button onclick="openCreateModal()" class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:shadow-emerald-500/25 transition-all duration-300 transform hover:scale-105">
              <i class="fa-solid fa-plus"></i>
              Yeni Ürün Ekle
            </button>
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

    <!-- Products List -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="p-6 border-b border-slate-200 dark:border-slate-700">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl">
            <i class="fa-solid fa-box text-white"></i>
          </div>
          Mevcut Ürünler
        </h2>
      </div>

      <?php if (empty($products)): ?>
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full mb-4">
          <i class="fa-solid fa-box-open text-2xl text-slate-400"></i>
        </div>
        <p class="text-xl text-slate-600 dark:text-slate-400">Henüz ürün bulunmuyor</p>
        <button onclick="openCreateModal()" class="mt-4 text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-200 font-medium">
          İlk ürününüzü ekleyin
        </button>
      </div>
      <?php else: ?>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Ürün</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Prefix</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Dakika/Ücret</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Fiyat</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Tip</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Durum</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">İşlemler</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($products as $product): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($product['name']); ?></div>
                  <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($product['description']); ?></div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-mono">
                <?php echo htmlspecialchars($product['phone_prefix']); ?>
              </td>
              <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                $<?php echo number_format($product['per_minute_cost'], 4); ?>
              </td>
              <td class="px-6 py-4">
                <div class="text-lg font-bold text-slate-900 dark:text-white">$<?php echo number_format($product['price'], 2); ?></div>
                <?php if ($product['is_subscription'] && $product['subscription_monthly_fee'] > 0): ?>
                <div class="text-sm text-orange-600 dark:text-orange-400">+$<?php echo number_format($product['subscription_monthly_fee'], 2); ?>/ay</div>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-col gap-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $product['is_subscription'] ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'; ?>">
                    <?php echo $product['is_subscription'] ? 'Abonelik' : 'Tek Satın Alma'; ?>
                  </span>
                  <?php if ($product['is_callback_enabled']): ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                    Geri Aranabilir
                  </span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $product['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'; ?>">
                  <?php echo $product['is_active'] ? 'Aktif' : 'Pasif'; ?>
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" 
                          class="inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-edit mr-1"></i>
                    Düzenle
                  </button>
                  <button onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')" 
                          class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-trash mr-1"></i>
                    Sil
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Create/Edit Modal -->
<div id="productModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <form id="productForm" method="post" action="/VoipPanelAi/agents/manage-products">
        <input type="hidden" name="action" id="formAction" value="create">
        <input type="hidden" name="id" id="productId" value="">
        
        <!-- Header -->
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center justify-between">
            <h3 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Yeni Ürün Ekle</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
              <i class="fa-solid fa-times text-xl"></i>
            </button>
          </div>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-6">
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Ürün Adı *</label>
              <input type="text" name="name" id="productName" required 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Telefon Prefix</label>
              <input type="text" name="phone_prefix" id="phonePrefix" value="0905" 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Açıklama</label>
            <textarea name="description" id="productDescription" rows="3" 
                      class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white"></textarea>
          </div>
          
          <!-- Pricing -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Dakika Başı Ücret ($) *</label>
              <input type="number" name="per_minute_cost" id="perMinuteCost" step="0.0001" value="0.4500" required 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Ana Fiyat ($) *</label>
              <input type="number" name="price" id="productPrice" step="0.01" required 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
          </div>
          
          <!-- Features -->
          <div class="space-y-4">
            <div class="flex items-center">
              <input type="checkbox" name="is_single_user" id="isSingleUser" checked 
                     class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="isSingleUser" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Tek kullanıcı için</label>
            </div>
            
            <div class="flex items-center">
              <input type="checkbox" name="is_callback_enabled" id="isCallbackEnabled" 
                     class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="isCallbackEnabled" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Geri aranabilir</label>
            </div>
            
            <div class="flex items-center">
              <input type="checkbox" name="is_subscription" id="isSubscription" onchange="toggleSubscription()" 
                     class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="isSubscription" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Abonelik ürünü</label>
            </div>
            
            <div class="flex items-center">
              <input type="checkbox" name="is_active" id="isActive" checked 
                     class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 dark:focus:ring-emerald-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
              <label for="isActive" class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Ürün aktif</label>
            </div>
          </div>
          
          <!-- Subscription Fields -->
          <div id="subscriptionFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Aylık Abonelik Ücreti ($)</label>
              <input type="number" name="subscription_monthly_fee" id="subscriptionMonthlyFee" step="0.01" value="0" 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kurulum Ücreti ($)</label>
              <input type="number" name="setup_fee" id="setupFee" step="0.01" value="0" 
                     class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-emerald-500 dark:bg-slate-700 dark:text-white">
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="p-6 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
          <button type="button" onclick="closeModal()" 
                  class="px-6 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            İptal
          </button>
          <button type="submit" 
                  class="px-6 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors">
            <span id="submitButtonText">Ürün Ekle</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="post" action="/VoipPanelAi/agents/manage-products" style="display: none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" id="deleteId" value="">
</form>

<script>
function openCreateModal() {
  document.getElementById('formAction').value = 'create';
  document.getElementById('modalTitle').textContent = 'Yeni Ürün Ekle';
  document.getElementById('submitButtonText').textContent = 'Ürün Ekle';
  document.getElementById('productForm').reset();
  document.getElementById('productId').value = '';
  document.getElementById('isSingleUser').checked = true;
  document.getElementById('isActive').checked = true;
  document.getElementById('phonePrefix').value = '0905';
  document.getElementById('perMinuteCost').value = '0.4500';
  toggleSubscription();
  document.getElementById('productModal').classList.remove('hidden');
}

function openEditModal(product) {
  document.getElementById('formAction').value = 'update';
  document.getElementById('modalTitle').textContent = 'Ürün Düzenle';
  document.getElementById('submitButtonText').textContent = 'Güncelle';
  document.getElementById('productId').value = product.id;
  document.getElementById('productName').value = product.name;
  document.getElementById('productDescription').value = product.description || '';
  document.getElementById('phonePrefix').value = product.phone_prefix;
  document.getElementById('perMinuteCost').value = product.per_minute_cost;
  document.getElementById('productPrice').value = product.price;
  document.getElementById('isSingleUser').checked = product.is_single_user == 1;
  document.getElementById('isCallbackEnabled').checked = product.is_callback_enabled == 1;
  document.getElementById('isSubscription').checked = product.is_subscription == 1;
  document.getElementById('isActive').checked = product.is_active == 1;
  document.getElementById('subscriptionMonthlyFee').value = product.subscription_monthly_fee || 0;
  document.getElementById('setupFee').value = product.setup_fee || 0;
  toggleSubscription();
  document.getElementById('productModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('productModal').classList.add('hidden');
}

function toggleSubscription() {
  const isSubscription = document.getElementById('isSubscription').checked;
  const subscriptionFields = document.getElementById('subscriptionFields');
  
  if (isSubscription) {
    subscriptionFields.classList.remove('hidden');
  } else {
    subscriptionFields.classList.add('hidden');
    document.getElementById('subscriptionMonthlyFee').value = '0';
    document.getElementById('setupFee').value = '0';
  }
}

function confirmDelete(id, name) {
  if (confirm(`"${name}" ürününü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`)) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteForm').submit();
  }
}

// Close modal when clicking outside
document.getElementById('productModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && !document.getElementById('productModal').classList.contains('hidden')) {
    closeModal();
  }
});
</script>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>