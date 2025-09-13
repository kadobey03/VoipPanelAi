<?php $title='Bakiye Yükle - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent flex items-center justify-center gap-3 mb-2">
          <i class="fa-solid fa-circle-plus text-3xl"></i>
          Bakiye Yükle
        </h1>
        <p class="text-slate-600 dark:text-slate-400">Gruplarınıza bakiye eklemek için grup seçimi yapın</p>
      </div>

      <!-- Progress Indicator -->
      <div class="flex items-center justify-center mb-8">
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
              1
            </div>
            <span class="ml-2 text-sm font-medium text-indigo-600">Grup Seç</span>
          </div>
          <div class="w-12 h-0.5 bg-slate-300 dark:bg-slate-600"></div>
          <div class="flex items-center">
            <div class="w-8 h-8 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-slate-500 dark:text-slate-400 text-sm font-semibold">
              2
            </div>
            <span class="ml-2 text-sm font-medium text-slate-500 dark:text-slate-400">Miktar Belirle</span>
          </div>
        </div>
      </div>

      <!-- Main Form Card -->
      <div class="max-w-2xl mx-auto">
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
          <div class="p-8">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-users text-indigo-600 text-2xl"></i>
              </div>
              <h2 class="text-2xl font-semibold text-slate-800 dark:text-slate-200 mb-2">Grup Seçimi</h2>
              <p class="text-slate-600 dark:text-slate-400">Bakiye yüklemek istediğiniz grubu seçin</p>
            </div>

            <form id="groupSelectForm" class="space-y-6">
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                  <i class="fa-solid fa-users text-indigo-600 mr-2"></i>
                  Grup Seçin
                </label>
                <div class="relative">
                  <select id="groupSelect"
                          class="w-full px-4 py-4 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 appearance-none cursor-pointer"
                          required>
                    <option value="">Grup seçin...</option>
                    <?php
                    $db = \App\Helpers\DB::conn();
                    $groups = [];
                    if ($res = $db->query('SELECT id, name, balance FROM groups ORDER BY name')) {
                      while ($row = $res->fetch_assoc()) {
                        $groups[] = $row;
                      }
                    }
                    foreach ($groups as $g):
                    ?>
                      <option value="<?= (int)$g['id'] ?>" data-name="<?= htmlspecialchars($g['name']) ?>" data-balance="<?= (float)($g['balance'] ?? 0) ?>">
                        <?= htmlspecialchars($g['name']) ?> (ID: <?= (int)$g['id'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                    <i class="fa-solid fa-chevron-down text-slate-400"></i>
                  </div>
                </div>
                <?php if (empty($groups)): ?>
                  <div class="mt-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <div class="flex items-center gap-2">
                      <i class="fa-solid fa-exclamation-triangle text-amber-600"></i>
                      <span class="text-sm text-amber-800 dark:text-amber-400">
                        Kullanılabilir grup bulunmuyor. Önce gruplarınızı oluşturun.
                      </span>
                    </div>
                  </div>
                <?php endif; ?>
              </div>

              <div class="pt-4">
                <button type="button" id="openTopupBtn"
                        class="w-full inline-flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        <?php if (empty($groups)): ?>disabled<?php endif; ?>>
                  <i class="fa-solid fa-arrow-right text-lg"></i>
                  <span class="font-semibold">Devam Et</span>
                  <i class="fa-solid fa-arrow-right text-lg"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Information Cards -->
        <div class="grid md:grid-cols-2 gap-4 mt-6">
          <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg">
                <i class="fa-solid fa-info-circle text-emerald-600"></i>
              </div>
              <h3 class="font-semibold text-slate-800 dark:text-slate-200">Güvenli İşlem</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">Tüm bakiye yükleme işlemleri güvenli bir şekilde gerçekleştirilir.</p>
          </div>

          <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-xl p-4 border border-slate-200/30 dark:border-slate-700/30">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                <i class="fa-solid fa-clock text-blue-600"></i>
              </div>
              <h3 class="font-semibold text-slate-800 dark:text-slate-200">Anında Etkili</h3>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-400">Yüklenen bakiye hemen grup hesabına yansıtılır.</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="text-center mt-8">
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= \App\Helpers\Url::to('/groups') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-users"></i>
              <span>Grupları Yönet</span>
            </a>
            <span class="text-slate-400 dark:text-slate-600">•</span>
            <a href="<?= \App\Helpers\Url::to('/balance') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-wallet"></i>
              <span>Ana Bakiye</span>
            </a>
            <span class="text-slate-400 dark:text-slate-600">•</span>
            <a href="<?= \App\Helpers\Url::to('/transactions') ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
              <i class="fa-solid fa-clock-rotate-left"></i>
              <span>İşlem Geçmişi</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Topup Modal -->
    <div id="topupModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
      <div class="flex items-center justify-center min-h-screen p-4">
        <div id="modalContent" class="bg-white/95 dark:bg-slate-800/95 backdrop-blur-lg rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform scale-95 transition-transform duration-300">
          <!-- Modal Header -->
          <div class="relative bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                  <i class="fa-solid fa-circle-plus text-2xl"></i>
                </div>
                <div>
                  <h3 class="text-xl font-bold">Bakiye Yükleme Talebi</h3>
                  <p class="text-indigo-100 text-sm" id="modalGroupName">Grup Seçin</p>
                </div>
              </div>
              <button id="closeModal" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4 bg-white/20 rounded-full h-2">
              <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: 100%"></div>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
            <div id="modalError" class="hidden mb-4 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-lg">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-exclamation-triangle text-rose-600"></i>
                <span class="text-rose-800 dark:text-rose-400 text-sm" id="errorText"></span>
              </div>
            </div>

            <div id="modalSuccess" class="hidden mb-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle text-emerald-600"></i>
                <span class="text-emerald-800 dark:text-emerald-400 text-sm" id="successText"></span>
              </div>
            </div>

            <!-- Current Balance -->
            <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-emerald-600"></i>
                  </div>
                  <div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">Mevcut Grup Bakiyesi</div>
                    <div class="text-lg font-bold text-slate-900 dark:text-slate-100" id="currentBalance">$0.00</div>
                  </div>
                </div>
              </div>
            </div>

            <form id="topupForm" method="post" enctype="multipart/form-data" class="space-y-6">
              <?php
              $isSuper = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'superadmin';
              if (!$isSuper) {
                // Get payment methods
                $db = \App\Helpers\DB::conn();
                $methods = [];
                if ($r = $db->query('SELECT id,name,method_type,fee_percent,fee_fixed FROM payment_methods WHERE active=1 ORDER BY id DESC')) {
                  while ($row = $r->fetch_assoc()) {
                    $methods[] = $row;
                  }
                }
                ?>
                <!-- Payment Method Selection -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <i class="fa-solid fa-credit-card text-indigo-600 mr-2"></i>
                    Ödeme Yöntemi
                  </label>
                  <div class="relative">
                    <select id="modalMethodId" name="method_id" class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 appearance-none cursor-pointer" required>
                      <option value="">Ödeme yöntemi seçin...</option>
                      <?php foreach ($methods as $m): ?>
                        <option value="<?= (int)$m['id'] ?>" data-name="<?= htmlspecialchars($m['name']) ?>" data-p="<?= (float)$m['fee_percent'] ?>" data-f="<?= (float)$m['fee_fixed'] ?>">
                          <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['method_type']) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                      <i class="fa-solid fa-chevron-down text-slate-400"></i>
                    </div>
                  </div>
                  <input type="hidden" name="method" id="modalMethodName" value="">
                </div>
              <?php } else { ?>
                <input type="hidden" name="method" value="manual">
              <?php } ?>

              <!-- Amount Input -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                  <i class="fa-solid fa-dollar-sign text-emerald-600 mr-2"></i>
                  Yükleme Tutarı
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">$</span>
                  </div>
                  <input id="modalAmount" type="number" step="0.01" min="0.01" name="amount"
                         class="w-full pl-8 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300"
                         placeholder="0.00" required>
                </div>
              </div>

              <?php if (!$isSuper) { ?>
                <!-- Fee Calculation -->
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/50 dark:to-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                  <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
                      <i class="fa-solid fa-calculator text-amber-600"></i>
                    </div>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">Ücret Hesaplaması</span>
                  </div>
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="text-slate-600 dark:text-slate-400">Komisyon:</span>
                      <div class="font-semibold text-slate-900 dark:text-slate-100" id="modalFeeText">$0.00</div>
                    </div>
                    <div>
                      <span class="text-slate-600 dark:text-slate-400">Toplam Ödenecek:</span>
                      <div class="font-bold text-emerald-600 dark:text-emerald-400 text-lg" id="modalTotalText">$0.00</div>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <i class="fa-solid fa-sticky-note text-yellow-600 mr-2"></i>
                    Açıklama <span class="text-slate-500">(opsiyonel)</span>
                  </label>
                  <textarea name="note" rows="3"
                            class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-300 resize-none"
                            placeholder="Ek açıklama veya notunuz..."></textarea>
                </div>

                <!-- File Upload -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <i class="fa-solid fa-paperclip text-purple-600 mr-2"></i>
                    Dekont/Fatura <span class="text-slate-500">(opsiyonel)</span>
                  </label>
                  <div class="relative">
                    <input type="file" name="receipt" accept="image/*,application/pdf" id="modalFileInput"
                           class="hidden">
                    <label for="modalFileInput"
                           class="flex items-center justify-center w-full px-4 py-4 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-all duration-300 cursor-pointer">
                      <div class="text-center">
                        <i class="fa-solid fa-cloud-upload-alt text-2xl text-slate-400 mb-2"></i>
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300">Dosya Seçin</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">PNG, JPG, PDF (max 10MB)</div>
                      </div>
                    </label>
                    <div id="filePreview" class="mt-2 hidden">
                      <div class="flex items-center gap-2 p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                        <i class="fa-solid fa-file text-slate-600 dark:text-slate-400"></i>
                        <span class="text-sm text-slate-700 dark:text-slate-300" id="fileName"></span>
                        <button type="button" onclick="clearFile()" class="ml-auto text-rose-500 hover:text-rose-700">
                          <i class="fa-solid fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                  <div class="flex items-start gap-2">
                    <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="text-sm text-blue-800 dark:text-blue-400">
                      <strong>Not:</strong> Talebiniz onaylandıktan sonra grup bakiyenize yansıtılacaktır.
                    </div>
                  </div>
                </div>
              <?php } ?>
            </form>
          </div>

          <!-- Modal Footer -->
          <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700">
            <div class="flex gap-3">
              <button id="cancelModal" class="flex-1 px-4 py-3 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors font-medium">
                İptal
              </button>
              <button id="submitModal" type="submit" form="topupForm"
                      class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none font-medium">
                <i class="fa-solid fa-paper-plane mr-2"></i>
                Talebi Gönder
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
      animation: fade-in 0.6s ease-out;
    }

    /* Custom select styling */
    select {
      background-image: none !important;
    }
    select:focus {
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Modal animations */
    .modal-enter {
      opacity: 0;
      transform: scale(0.95);
    }
    .modal-enter-active {
      opacity: 1;
      transform: scale(1);
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
  </style>

  <script>
    // Modal functionality
    let selectedGroup = null;
    let currentBalance = 0;

    function openTopupModal(groupId, groupName, balance) {
      selectedGroup = groupId;
      currentBalance = balance;
      document.getElementById('modalGroupName').textContent = groupName;
      document.getElementById('currentBalance').textContent = '$' + parseFloat(balance).toFixed(2);

      // Add form action
      document.getElementById('topupForm').action = '<?= \App\Helpers\Url::to('/groups/topup') ?>?id=' + groupId;

      // Show modal
      const modal = document.getElementById('topupModal');
      const modalContent = document.getElementById('modalContent');
      modal.classList.remove('hidden');
      setTimeout(() => {
        modal.classList.add('opacity-100');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
      }, 10);

      // Reset form
      document.getElementById('topupForm').reset();
      document.getElementById('modalError').classList.add('hidden');
      document.getElementById('modalSuccess').classList.add('hidden');
      document.getElementById('filePreview').classList.add('hidden');
      calculateFee();
    }

    function closeTopupModal() {
      const modal = document.getElementById('topupModal');
      const modalContent = document.getElementById('modalContent');
      modal.classList.remove('opacity-100');
      modalContent.classList.remove('scale-100');
      modalContent.classList.add('scale-95');
      setTimeout(() => {
        modal.classList.add('hidden');
      }, 300);
    }

    // Fee calculation
    function calculateFee() {
      const methodSelect = document.getElementById('modalMethodId');
      const amountInput = document.getElementById('modalAmount');
      const feeText = document.getElementById('modalFeeText');
      const totalText = document.getElementById('modalTotalText');
      const methodNameInput = document.getElementById('modalMethodName');

      if (!methodSelect || !amountInput) return;

      const selectedOption = methodSelect.options[methodSelect.selectedIndex];
      if (!selectedOption) return;

      const p = parseFloat(selectedOption.getAttribute('data-p') || '0');
      const f = parseFloat(selectedOption.getAttribute('data-f') || '0');
      const name = selectedOption.getAttribute('data-name') || '';
      const amt = parseFloat(amountInput.value || '0');

      const fee = (amt * (p / 100.0)) + f;
      const total = amt + fee;

      if (feeText) feeText.textContent = '$' + fee.toFixed(2) + ' (' + p.toFixed(2) + '% + $' + f.toFixed(2) + ')';
      if (totalText) totalText.textContent = '$' + total.toFixed(2);
      if (methodNameInput) methodNameInput.value = name;
    }

    // File upload preview
    document.getElementById('modalFileInput').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').classList.remove('hidden');
      }
    });

    function clearFile() {
      document.getElementById('modalFileInput').value = '';
      document.getElementById('filePreview').classList.add('hidden');
    }

    // Event listeners
    document.getElementById('closeModal').addEventListener('click', closeTopupModal);
    document.getElementById('cancelModal').addEventListener('click', closeTopupModal);
    document.getElementById('topupModal').addEventListener('click', function(e) {
      if (e.target === this) closeTopupModal();
    });

    // Group selection and modal opening
    document.getElementById('openTopupBtn').addEventListener('click', function() {
      const groupSelect = document.getElementById('groupSelect');
      if (!groupSelect.value) {
        alert('Lütfen bir grup seçin.');
        return;
      }

      const selectedOption = groupSelect.options[groupSelect.selectedIndex];
      const groupId = groupSelect.value;
      const groupName = selectedOption.getAttribute('data-name');
      const balance = selectedOption.getAttribute('data-balance');

      openTopupModal(groupId, groupName, balance);
    });

    // Form change listeners
    document.getElementById('modalMethodId')?.addEventListener('change', calculateFee);
    document.getElementById('modalAmount')?.addEventListener('input', calculateFee);

    // Form submission
    document.getElementById('topupForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      // Show loading state
      const submitBtn = document.getElementById('submitModal');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Gönderiliyor...';
      submitBtn.disabled = true;

      fetch(this.action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // Check for success/error in response
        if (data.includes('success') || data.includes('başarıyla')) {
          document.getElementById('modalSuccess').classList.remove('hidden');
          document.getElementById('successText').textContent = 'Bakiye yükleme talebiniz başarıyla gönderildi!';
          document.getElementById('modalError').classList.add('hidden');
          setTimeout(() => {
            closeTopupModal();
            location.reload();
          }, 2000);
        } else {
          document.getElementById('modalError').classList.remove('hidden');
          document.getElementById('errorText').textContent = 'Bir hata oluştu. Lütfen tekrar deneyin.';
          document.getElementById('modalSuccess').classList.add('hidden');
        }
      })
      .catch(error => {
        document.getElementById('modalError').classList.remove('hidden');
        document.getElementById('errorText').textContent = 'Bağlantı hatası. Lütfen tekrar deneyin.';
        document.getElementById('modalSuccess').classList.add('hidden');
      })
      .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
    });
  </script>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

