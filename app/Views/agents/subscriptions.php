<?php $title='Abonelik Yönetimi - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 border border-slate-200/50 dark:border-slate-700/50">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg">
              <i class="fa-solid fa-calendar-alt text-3xl text-white"></i>
            </div>
            <div>
              <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white">Abonelik Yönetimi</h1>
              <p class="text-lg text-slate-600 dark:text-slate-400 mt-2">Agent aboneliklerini yönetin ve takip edin</p>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl p-6 border border-emerald-200/50 dark:border-emerald-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-emerald-500 rounded-lg">
                <i class="fa-solid fa-check-circle text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-emerald-800 dark:text-emerald-300"><?php echo $stats['active_subscriptions'] ?? 0; ?></div>
                <div class="text-sm text-emerald-600 dark:text-emerald-400">Aktif Abonelik</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200/50 dark:border-blue-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-blue-500 rounded-lg">
                <i class="fa-solid fa-dollar-sign text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-800 dark:text-blue-300">$<?php echo number_format($stats['monthly_revenue'] ?? 0, 2); ?></div>
                <div class="text-sm text-blue-600 dark:text-blue-400">Bu Ay Gelir</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl p-6 border border-orange-200/50 dark:border-orange-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-orange-500 rounded-lg">
                <i class="fa-solid fa-exclamation-triangle text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-orange-800 dark:text-orange-300"><?php echo $stats['overdue_count'] ?? 0; ?></div>
                <div class="text-sm text-orange-600 dark:text-orange-400">Vadesi Geçmiş</div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-6 border border-red-200/50 dark:border-red-700/50">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 bg-red-500 rounded-lg">
                <i class="fa-solid fa-ban text-white"></i>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-red-800 dark:text-red-300"><?php echo $stats['suspended_count'] ?? 0; ?></div>
                <div class="text-sm text-red-600 dark:text-red-400">Askıya Alınan</div>
              </div>
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

    <!-- Overdue Payments -->
    <?php if (!empty($overduePayments)): ?>
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-red-50 dark:bg-red-900/20">
          <h2 class="text-2xl font-bold text-red-900 dark:text-red-200 flex items-center gap-3">
            <div class="p-2 bg-red-500 rounded-xl">
              <i class="fa-solid fa-exclamation-triangle text-white"></i>
            </div>
            Vadesi Geçmiş Ödemeler (<?php echo count($overduePayments); ?>)
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Kullanıcı</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Agent</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Tutar</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Vade Tarihi</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Gecikme</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Bakiye</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">İşlemler</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <?php foreach ($overduePayments as $payment): ?>
              <tr class="hover:bg-red-50 dark:hover:bg-red-900/10">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($payment['user_login']); ?></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($payment['group_name']); ?></div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($payment['product_name']); ?></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">#<?php echo htmlspecialchars($payment['agent_number']); ?></div>
                  </div>
                </td>
                <td class="px-6 py-4 text-lg font-bold text-slate-900 dark:text-white">
                  $<?php echo number_format($payment['amount'], 2); ?>
                </td>
                <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                  <?php echo date('d.m.Y', strtotime($payment['due_date'])); ?>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                    <?php echo $payment['days_overdue']; ?> gün
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm">
                    <div class="text-slate-900 dark:text-white">$<?php echo number_format($payment['group_balance'], 2); ?></div>
                    <div class="<?php echo $payment['group_balance'] >= $payment['amount'] ? 'text-green-600' : 'text-red-600'; ?>">
                      <?php echo $payment['group_balance'] >= $payment['amount'] ? 'Yeterli' : 'Yetersiz'; ?>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <form method="post" action="/VoipPanelAi/agents/subscriptions/process-manual" class="inline">
                      <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                      <input type="hidden" name="action" value="approve">
                      <button type="submit" 
                              class="inline-flex items-center px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg transition-colors"
                              onclick="return confirm('Bu ödemeyi manuel olarak onaylamak istediğinizden emin misiniz?')">
                        <i class="fa-solid fa-check mr-1"></i>
                        Onayla
                      </button>
                    </form>
                    <form method="post" action="/VoipPanelAi/agents/subscriptions/process-manual" class="inline">
                      <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                      <input type="hidden" name="action" value="reject">
                      <button type="submit" 
                              class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors"
                              onclick="return confirm('Bu ödemeyi reddetmek istediğinizden emin misiniz?')">
                        <i class="fa-solid fa-times mr-1"></i>
                        Reddet
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Active Subscriptions -->
    <div class="mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700">
          <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
            <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl">
              <i class="fa-solid fa-calendar-check text-white"></i>
            </div>
            Aktif Abonelikler (<?php echo count($activeSubscriptions); ?>)
          </h2>
        </div>

        <?php if (empty($activeSubscriptions)): ?>
        <div class="text-center py-12">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full mb-4">
            <i class="fa-solid fa-calendar-times text-2xl text-slate-400"></i>
          </div>
          <p class="text-xl text-slate-600 dark:text-slate-400">Aktif abonelik bulunmuyor</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-700">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Kullanıcı</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Agent</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Aylık Ücret</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Sonraki Ödeme</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Durum</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Satın Alma</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
              <?php foreach ($activeSubscriptions as $subscription): ?>
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($subscription['user_login']); ?></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($subscription['group_name']); ?></div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($subscription['product_name']); ?></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">#<?php echo htmlspecialchars($subscription['agent_number']); ?></div>
                  </div>
                </td>
                <td class="px-6 py-4 text-lg font-bold text-slate-900 dark:text-white">
                  $<?php echo number_format($subscription['subscription_monthly_fee'], 2); ?>
                </td>
                <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                  <?php 
                  $nextDue = strtotime($subscription['next_subscription_due']);
                  $now = time();
                  $daysUntil = ceil(($nextDue - $now) / (24 * 60 * 60));
                  echo date('d.m.Y', $nextDue);
                  ?>
                  <div class="text-xs <?php echo $daysUntil <= 3 ? 'text-red-500' : ($daysUntil <= 7 ? 'text-orange-500' : 'text-slate-400'); ?>">
                    <?php echo $daysUntil; ?> gün kaldı
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $subscription['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'; ?>">
                    <?php echo $subscription['status'] === 'active' ? 'Aktif' : ucfirst($subscription['status']); ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                  <?php echo date('d.m.Y', strtotime($subscription['purchase_date'])); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="p-6 border-b border-slate-200 dark:border-slate-700">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl">
            <i class="fa-solid fa-history text-white"></i>
          </div>
          Son Abonelik Ödemeleri
        </h2>
      </div>

      <?php if (empty($recentPayments)): ?>
      <div class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full mb-4">
          <i class="fa-solid fa-receipt text-2xl text-slate-400"></i>
        </div>
        <p class="text-xl text-slate-600 dark:text-slate-400">Henüz ödeme bulunmuyor</p>
      </div>
      <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-700">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Kullanıcı</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Agent</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Tutar</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Durum</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Vade/Ödeme</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900 dark:text-white">Yöntem</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            <?php foreach ($recentPayments as $payment): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($payment['user_login']); ?></div>
                  <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($payment['group_name']); ?></div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <div class="font-medium text-slate-900 dark:text-white"><?php echo htmlspecialchars($payment['product_name']); ?></div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">#<?php echo htmlspecialchars($payment['agent_number']); ?></div>
                </div>
              </td>
              <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                $<?php echo number_format($payment['amount'], 2); ?>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                  <?php
                  switch($payment['status']) {
                    case 'paid': echo 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'; break;
                    case 'pending': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'; break;
                    case 'failed': echo 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'; break;
                    case 'overdue': echo 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300'; break;
                    default: echo 'bg-slate-100 text-slate-800 dark:bg-slate-900/50 dark:text-slate-300';
                  }
                  ?>">
                  <?php
                  switch($payment['status']) {
                    case 'paid': echo 'Ödendi'; break;
                    case 'pending': echo 'Bekliyor'; break;
                    case 'failed': echo 'Başarısız'; break;
                    case 'overdue': echo 'Vadesi Geçmiş'; break;
                    default: echo ucfirst($payment['status']);
                  }
                  ?>
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                <div>Vade: <?php echo date('d.m.Y', strtotime($payment['due_date'])); ?></div>
                <?php if ($payment['payment_date']): ?>
                <div class="text-green-600">Ödendi: <?php echo date('d.m.Y', strtotime($payment['payment_date'])); ?></div>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">
                <?php echo $payment['payment_method'] ?: 'Otomatik'; ?>
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

<?php require dirname(__DIR__).'/partials/footer.php'; ?>