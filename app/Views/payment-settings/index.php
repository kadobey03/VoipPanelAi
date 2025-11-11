<?php $title=__('payment_settings') . ' - ' . __('site_title'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-credit-card text-green-600"></i> <?= __('payment_settings') ?></h1>
  </div>
  <form method="post" class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-6">
    
    <!-- Cryptocurrency Settings Section -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2"><?= __('cryptocurrency_settings') ?></h3>
      <div class="grid md:grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('usdt_trc20_wallet_address') ?></label>
          <input type="text" name="crypto_usdt_wallet" value="<?= htmlspecialchars($settings['crypto_usdt_wallet'] ?? '') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900 font-mono text-sm"
                 placeholder="<?= __('tron_address_example') ?>">
          <p class="text-xs text-gray-500 mt-1"><?= __('usdt_payments_info') ?></p>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1"><?= __('payment_validity_minutes') ?></label>
            <input type="number" name="crypto_payment_timeout" value="<?= htmlspecialchars($settings['crypto_payment_timeout'] ?? '10') ?>"
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="60">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1"><?= __('required_confirmations') ?></label>
            <input type="number" name="crypto_required_confirmations" value="<?= htmlspecialchars($settings['crypto_required_confirmations'] ?? '19') ?>"
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="50">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1"><?= __('check_interval_seconds') ?></label>
            <input type="number" name="crypto_check_interval" value="<?= htmlspecialchars($settings['crypto_check_interval'] ?? '10') ?>"
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="5" max="300">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('trongrid_api_key') ?></label>
          <input type="text" name="crypto_tron_api_key" value="<?= htmlspecialchars($settings['crypto_tron_api_key'] ?? '') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900"
                 placeholder="<?= __('trongrid_api_key_placeholder') ?>">
          <p class="text-xs text-gray-500 mt-1"><?= __('blockchain_monitoring_info') ?></p>
        </div>
      </div>
    </div>

    <!-- Payment Security Settings -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2"><?= __('payment_security') ?></h3>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('hourly_payment_limit') ?></label>
          <input type="number" name="payment_hourly_limit" value="<?= htmlspecialchars($settings['payment_hourly_limit'] ?? '10') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="100">
          <p class="text-xs text-gray-500 mt-1"><?= __('hourly_payment_limit_desc') ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('daily_payment_limit') ?></label>
          <input type="number" name="payment_daily_limit" value="<?= htmlspecialchars($settings['payment_daily_limit'] ?? '50') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" max="1000">
          <p class="text-xs text-gray-500 mt-1"><?= __('daily_payment_limit_desc') ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('minimum_payment_amount') ?></label>
          <input type="number" name="payment_min_amount" value="<?= htmlspecialchars($settings['payment_min_amount'] ?? '1.00') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="0.01" step="0.01">
          <p class="text-xs text-gray-500 mt-1"><?= __('minimum_payment_amount_desc') ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('maximum_payment_amount') ?></label>
          <input type="number" name="payment_max_amount" value="<?= htmlspecialchars($settings['payment_max_amount'] ?? '10000.00') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900" min="1" step="0.01">
          <p class="text-xs text-gray-500 mt-1"><?= __('maximum_payment_amount_desc') ?></p>
        </div>
      </div>
    </div>

    <!-- Advanced Settings -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 border-b pb-2"><?= __('advanced_settings') ?></h3>
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('tron_network_endpoint') ?></label>
          <input type="text" name="tron_network_endpoint" value="<?= htmlspecialchars($settings['tron_network_endpoint'] ?? 'https://api.trongrid.io') ?>"
                 class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <p class="text-xs text-gray-500 mt-1"><?= __('tron_blockchain_api_desc') ?></p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1"><?= __('wallet_blacklist_check') ?></label>
          <select name="wallet_blacklist_enabled" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
            <option value="1" <?= ($settings['wallet_blacklist_enabled'] ?? '1') == '1' ? 'selected' : '' ?>><?= __('enabled') ?></option>
            <option value="0" <?= ($settings['wallet_blacklist_enabled'] ?? '1') == '0' ? 'selected' : '' ?>><?= __('disabled') ?></option>
          </select>
          <p class="text-xs text-gray-500 mt-1"><?= __('wallet_blacklist_desc') ?></p>
        </div>
      </div>
    </div>

    <button type="submit" name="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium">
      <?= __('save_payment_settings') ?>
    </button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>