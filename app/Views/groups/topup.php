<!DOCTYPE html>
<html lang="<?= \App\Helpers\Lang::current() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('load_balance_title') ?> - <?= htmlspecialchars($group['name']) ?></title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style>
    @keyframes pulse-ring { 0%{transform:scale(.9);opacity:.7} 70%{transform:scale(1.15);opacity:0} 100%{transform:scale(.9);opacity:0} }
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    @keyframes spin-slow { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    @keyframes bounce-in { 0%{transform:scale(0.3);opacity:0} 50%{transform:scale(1.1)} 70%{transform:scale(0.9)} 100%{transform:scale(1);opacity:1} }
    @keyframes slide-up { from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }
    @keyframes glow { 0%,100%{box-shadow:0 0 20px rgba(99,102,241,.4)} 50%{box-shadow:0 0 40px rgba(99,102,241,.8),0 0 60px rgba(139,92,246,.3)} }

    .pulse-ring::before {
      content:''; position:absolute; inset:-8px; border-radius:9999px;
      border:3px solid rgba(99,102,241,.4); animation:pulse-ring 2s ease-out infinite;
    }
    .float-anim { animation:float 3s ease-in-out infinite; }
    .shimmer-bg {
      background: linear-gradient(90deg, rgba(255,255,255,.05) 25%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.05) 75%);
      background-size:200% 100%; animation:shimmer 2s infinite;
    }
    .spin-slow { animation:spin-slow 8s linear infinite; }
    .bounce-in { animation:bounce-in .6s cubic-bezier(.36,.07,.19,.97) both; }
    .slide-up  { animation:slide-up .5s ease both; }
    .glow-card { animation:glow 3s ease-in-out infinite; }

    .crypto-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    }
    .dark-glass {
      background: rgba(15,23,42,.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
    }
    .wallet-input {
      background: rgba(255,255,255,.07);
      border: 1px solid rgba(255,255,255,.15);
      font-family: 'Courier New', monospace;
      letter-spacing:.05em;
      color:#e2e8f0;
    }
    .wallet-input:focus { outline:none; border-color:rgba(99,102,241,.6); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
    .step-item { transition:all .3s ease; }
    .step-item.active { transform:translateX(4px); }
  </style>
</head>
<body class="min-h-screen bg-slate-950" style="background:radial-gradient(ellipse at top,#1e1b4b 0%,#0f172a 50%,#0a0a0f 100%)">

  <!-- Animated BG particles -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-violet-600/10 rounded-full blur-3xl spin-slow"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-600/8 rounded-full blur-3xl" style="animation:spin-slow 12s linear infinite reverse"></div>
    <div class="absolute top-3/4 left-1/2 w-48 h-48 bg-fuchsia-600/8 rounded-full blur-3xl float-anim"></div>
  </div>

  <div class="relative z-10 min-h-screen flex flex-col items-center justify-start px-4 py-8">

    <!-- Top nav bar -->
    <div class="w-full max-w-lg mb-6 flex items-center justify-between slide-up">
      <a href="<?= \App\Helpers\Url::to('/topups') ?>"
         class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white text-sm font-medium transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <?= __('back') ?>
      </a>
      <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
        <div class="w-2 h-2 rounded-full bg-emerald-400" style="animation:pulse-ring 1.5s ease-out infinite;position:relative"></div>
        <span class="text-emerald-300 text-xs font-semibold">TRC20 Network</span>
      </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($error)): ?>
    <div class="w-full max-w-lg mb-4 slide-up">
      <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($ok)): ?>
    <div class="w-full max-w-lg mb-4 slide-up">
      <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($ok) ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($cryptoPaymentData) && $cryptoPaymentData): ?>
    <!-- ══════════════ CRYPTO PAYMENT UI ══════════════ -->

    <!-- Main payment card -->
    <div class="w-full max-w-lg slide-up">
      <div class="rounded-3xl overflow-hidden border border-white/10 shadow-2xl shadow-violet-900/30 glow-card" style="background:rgba(15,23,42,.9);backdrop-filter:blur(24px)">

        <!-- Header gradient -->
        <div class="relative overflow-hidden p-6" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#a855f7 100%)">
          <div class="absolute inset-0 shimmer-bg"></div>
          <div class="relative flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-white font-bold text-lg"><?= __('usdt_trc20_payment') ?></span>
              </div>
              <p class="text-indigo-200 text-sm"><?= htmlspecialchars($group['name']) ?> · <?= __('secure_blockchain_payment') ?></p>
            </div>
            <div id="paymentStatus"
                 class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-400/20 border border-amber-300/40 text-amber-200 flex items-center gap-1.5 whitespace-nowrap">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block" style="animation:pulse-ring 1.5s ease-out infinite;position:relative"></span>
              <?= __('payment_waiting') ?>
            </div>
          </div>

          <!-- Amount display -->
          <div class="mt-6 text-center">
            <div class="text-5xl font-black text-white tracking-tight float-anim">
              <?= number_format((float)($cryptoPaymentData['amount_with_commission'] ?? ($cryptoPaymentData['amount'] * 1.02)), 2) ?>
            </div>
            <div class="text-indigo-200 font-semibold text-lg mt-1">USDT · TRC20</div>
          </div>
        </div>

        <!-- Amount breakdown -->
        <div class="p-5 border-b border-white/8">
          <div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08)">
            <div class="flex justify-between items-center px-4 py-3">
              <span class="text-slate-400 text-sm">Yüklenecek Bakiye</span>
              <span class="text-white font-semibold text-sm"><?= number_format((float)$cryptoPaymentData['amount'], 2) ?> USDT</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-t border-white/5">
              <span class="text-amber-400 text-sm">Komisyon (%<?= number_format((float)($cryptoPaymentData['commission_percent'] ?? 2), 2) ?>)</span>
              <span class="text-amber-400 font-semibold text-sm">+<?= number_format((float)($cryptoPaymentData['commission_amount'] ?? $cryptoPaymentData['amount'] * 0.02), 2) ?> USDT</span>
            </div>
            <div class="flex justify-between items-center px-4 py-3 border-t border-white/8" style="background:rgba(99,102,241,.08)">
              <span class="text-indigo-300 font-bold text-sm">Gönderilecek Toplam</span>
              <span class="text-white font-black text-sm"><?= number_format((float)($cryptoPaymentData['amount_with_commission'] ?? $cryptoPaymentData['amount'] * 1.02), 2) ?> USDT</span>
            </div>
          </div>
        </div>

        <!-- Wallet address -->
        <div class="p-5 border-b border-white/8">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">
            <?= __('wallet_address') ?>
          </label>
          <div class="flex gap-2">
            <input type="text" id="walletAddress"
                   value="<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>"
                   class="wallet-input flex-1 px-4 py-3 rounded-xl text-sm"
                   readonly>
            <button onclick="copyAddress()" id="copyBtn"
                    class="flex-shrink-0 px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm transition-all hover:scale-105 active:scale-95">
              <?= __('copy_button') ?>
            </button>
          </div>
        </div>

        <!-- QR Code -->
        <div class="p-5 border-b border-white/8 text-center">
          <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4"><?= __('qr_payment') ?></div>
          <div class="inline-flex items-center justify-center p-4 rounded-2xl bg-white shadow-xl shadow-violet-900/30">
            <div id="qrcode"></div>
          </div>
          <p class="text-slate-500 text-xs mt-3"><?= __('qr_instruction') ?></p>
        </div>

        <!-- Progress & Status -->
        <div class="p-5 border-b border-white/8">
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ödeme Durumu</span>
            <span class="text-xs text-slate-500" id="checkingLabel">Blockchain izleniyor...</span>
          </div>
          <div class="w-full h-2 rounded-full bg-white/8 overflow-hidden">
            <div id="progressBar"
                 class="h-full rounded-full transition-all duration-700"
                 style="width:20%;background:linear-gradient(90deg,#6366f1,#a855f7)"></div>
          </div>

          <!-- Steps -->
          <div class="mt-4 space-y-2">
            <div class="step-item active flex items-center gap-3 p-3 rounded-xl" style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2)">
              <div class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span class="text-indigo-300 text-sm font-medium"><?= __('usdt_only_instruction') ?></span>
            </div>
            <div class="step-item flex items-center gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)">
              <div class="w-6 h-6 rounded-full bg-blue-500/20 border border-blue-500/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span class="text-slate-400 text-sm"><?= __('confirmation_required') ?></span>
            </div>
            <div class="step-item flex items-center gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)">
              <div class="w-6 h-6 rounded-full bg-purple-500/20 border border-purple-500/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              </div>
              <span class="text-slate-400 text-sm"><?= __('auto_balance_loading') ?></span>
            </div>
          </div>
        </div>

        <!-- Timer & Cancel -->
        <div class="p-5">
          <div class="flex items-center justify-between p-4 rounded-2xl mb-4"
               style="background:linear-gradient(135deg,rgba(239,68,68,.15),rgba(249,115,22,.1));border:1px solid rgba(239,68,68,.25)">
            <div>
              <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('remaining_time') ?></div>
              <div id="countdown" class="text-3xl font-black text-red-400 font-mono tabular-nums">--:--</div>
            </div>
            <div class="text-right">
              <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('last_validity') ?></div>
              <div id="expiryTime" class="text-slate-300 font-semibold">--:--</div>
            </div>
          </div>

          <button onclick="showCancelModal()"
                  class="w-full py-3.5 rounded-2xl font-bold text-sm transition-all hover:scale-[1.02] active:scale-[.98]"
                  style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <?= __('cancel_payment') ?>
          </button>
        </div>
      </div>
    </div>

    <!-- ══════ MODALS ══════ -->

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(8px)">
      <div class="bounce-in w-full max-w-sm rounded-3xl overflow-hidden border border-emerald-500/30 shadow-2xl"
           style="background:rgba(15,23,42,.95);backdrop-filter:blur(20px)">
        <div class="p-8 text-center">
          <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center"
               style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 40px rgba(16,185,129,.4)">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h3 class="text-2xl font-black text-white mb-2"><?= __('payment_received') ?></h3>
          <p class="text-slate-400 mb-6 text-sm"><?= __('usdt_transfer_confirmed') ?></p>
          <button onclick="location.reload()"
                  class="w-full py-3.5 rounded-2xl font-bold text-white transition-all hover:scale-[1.02] active:scale-[.98]"
                  style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 4px 20px rgba(16,185,129,.3)">
            <?= __('continue_button') ?>
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Confirm Modal -->
    <div id="cancelModal" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(8px)">
      <div class="bounce-in w-full max-w-sm rounded-3xl overflow-hidden border border-red-500/30 shadow-2xl"
           style="background:rgba(15,23,42,.95);backdrop-filter:blur(20px)">
        <div class="p-8 text-center">
          <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center"
               style="background:rgba(239,68,68,.15);border:2px solid rgba(239,68,68,.3)">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <h3 class="text-xl font-black text-white mb-2"><?= __('cancel_payment_title') ?></h3>
          <p class="text-slate-400 mb-6 text-sm"><?= __('cancel_payment_confirmation') ?></p>
          <div class="flex gap-3">
            <button onclick="hideCancelModal()"
                    class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-300 transition-all hover:scale-[1.02]"
                    style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1)">
              <?= __('cancel') ?>
            </button>
            <button onclick="confirmCancel()"
                    class="flex-1 py-3 rounded-xl font-bold text-sm text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 4px 15px rgba(239,68,68,.3)">
              <?= __('yes_cancel') ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Success Modal -->
    <div id="cancelSuccessModal" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4"
         style="background:rgba(0,0,0,.75);backdrop-filter:blur(8px)">
      <div class="bounce-in w-full max-w-sm rounded-3xl overflow-hidden border border-slate-500/30 shadow-2xl"
           style="background:rgba(15,23,42,.95);backdrop-filter:blur(20px)">
        <div class="p-8 text-center">
          <div class="w-20 h-20 mx-auto mb-5 rounded-full flex items-center justify-center"
               style="background:rgba(148,163,184,.1);border:2px solid rgba(148,163,184,.2)">
            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-black text-white mb-2"><?= __('canceled') ?></h3>
          <p class="text-slate-400 mb-6 text-sm"><?= __('payment_request_canceled') ?></p>
          <button onclick="location.href='<?= \App\Helpers\Url::to('/groups/topup?id=' . $group['id']) ?>'"
                  class="w-full py-3.5 rounded-2xl font-bold text-white transition-all hover:scale-[1.02]"
                  style="background:linear-gradient(135deg,#6366f1,#7c3aed)">
            <?= __('ok_button') ?>
          </button>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- ══════════════ PAYMENT FORM ══════════════ -->

    <!-- Balance info card -->
    <div class="w-full max-w-lg mb-5 slide-up">
      <div class="flex items-center gap-4 p-5 rounded-2xl border border-white/10"
           style="background:rgba(255,255,255,.04);backdrop-filter:blur(12px)">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
             style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 0 20px rgba(16,185,129,.3)">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <div>
          <div class="text-slate-400 text-xs font-semibold uppercase tracking-widest"><?= __('current_balance') ?></div>
          <div class="text-white text-2xl font-black">$<?= number_format((float)$group['balance'], 2) ?></div>
        </div>
        <div class="ml-auto text-right">
          <div class="text-slate-400 text-xs"><?= htmlspecialchars($group['name']) ?></div>
          <div class="text-slate-500 text-xs mt-0.5">Grup bakiyesi</div>
        </div>
      </div>
    </div>

    <!-- Form card -->
    <div class="w-full max-w-lg slide-up">
      <div class="rounded-3xl overflow-hidden border border-white/10 shadow-2xl"
           style="background:rgba(15,23,42,.85);backdrop-filter:blur(24px)">

        <!-- Form header -->
        <div class="px-6 pt-6 pb-4 border-b border-white/8">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl flex items-center justify-center"
                 style="background:linear-gradient(135deg,#6366f1,#7c3aed)">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <div>
              <h2 class="text-white font-bold text-lg"><?= __('load_balance_title') ?></h2>
              <p class="text-slate-400 text-xs">Güvenli blockchain ödeme</p>
            </div>
          </div>
        </div>

        <form method="post" enctype="multipart/form-data" class="p-6 space-y-5">

          <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin'): ?>
            <?php
              if (!isset($methods) || !is_array($methods) || count($methods) === 0) {
                $db = \App\Helpers\DB::conn();
                $methods = [];
                if ($r = $db->query('SELECT id,name,method_type,fee_percent,fee_fixed FROM payment_methods WHERE active=1 ORDER BY id DESC')) {
                  while ($row = $r->fetch_assoc()) { $methods[] = $row; }
                }
              }
            ?>
            <!-- Payment Method -->
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Ödeme Yöntemi</label>
              <select id="method_id" name="method_id" required
                      class="w-full px-4 py-3.5 rounded-2xl text-sm font-medium text-white transition-all"
                      style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);outline:none"
                      onfocus="this.style.borderColor='rgba(99,102,241,.6)'" onblur="this.style.borderColor='rgba(255,255,255,.12)'">
                <?php foreach ($methods as $m): ?>
                  <option value="<?= (int)$m['id'] ?>"
                          data-name="<?= htmlspecialchars($m['name']) ?>"
                          data-type="<?= htmlspecialchars($m['method_type']) ?>"
                          data-p="<?= (float)$m['fee_percent'] ?>"
                          data-f="<?= (float)$m['fee_fixed'] ?>"
                          style="background:#0f172a">
                    <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['method_type']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="method" id="method_name" value="">
            </div>
          <?php else: ?>
            <input type="hidden" name="method" value="manual">
          <?php endif; ?>

          <!-- Amount -->
          <div>
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Tutar (USDT)</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">$</span>
              <input id="amount" type="number" step="0.01" min="0.01" name="amount" required
                     placeholder="0.00"
                     class="w-full pl-9 pr-4 py-3.5 rounded-2xl text-white font-bold text-lg transition-all"
                     style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);outline:none"
                     onfocus="this.style.borderColor='rgba(99,102,241,.6)'" onblur="this.style.borderColor='rgba(255,255,255,.12)'">
            </div>
          </div>

          <!-- Fee breakdown -->
          <div id="feeSection"></div>

          <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin'): ?>
          <!-- Note -->
          <div id="noteSection">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Açıklama (opsiyonel)</label>
            <input name="note" placeholder="Not / ek açıklama"
                   class="w-full px-4 py-3.5 rounded-2xl text-white text-sm transition-all"
                   style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);outline:none"
                   onfocus="this.style.borderColor='rgba(99,102,241,.6)'" onblur="this.style.borderColor='rgba(255,255,255,.12)'">
          </div>

          <!-- Receipt upload -->
          <div id="receiptSection">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Dekont (opsiyonel)</label>
            <label class="flex items-center gap-3 px-4 py-3.5 rounded-2xl cursor-pointer transition-all"
                   style="background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.15)"
                   onmouseenter="this.style.borderColor='rgba(99,102,241,.4)'" onmouseleave="this.style.borderColor='rgba(255,255,255,.15)'">
              <svg class="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span class="text-slate-400 text-sm" id="receiptLabel">Resim veya PDF seçin</span>
              <input type="file" name="receipt" accept="image/*,application/pdf" class="hidden"
                     onchange="document.getElementById('receiptLabel').textContent = this.files[0]?.name || 'Resim veya PDF seçin'">
            </label>
          </div>

          <div class="flex items-start gap-2 p-3 rounded-xl" style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15)">
            <svg class="w-4 h-4 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-indigo-300 text-xs">Not: Talebiniz onaylanınca grup bakiyenize yansır.</p>
          </div>
          <?php endif; ?>

          <!-- Submit -->
          <button id="submitBtn" type="submit"
                  class="w-full py-4 rounded-2xl font-black text-white text-base transition-all hover:scale-[1.02] active:scale-[.98] relative overflow-hidden"
                  style="background:linear-gradient(135deg,#6366f1,#7c3aed,#a855f7);box-shadow:0 8px 30px rgba(99,102,241,.4)">
            <span class="relative z-10 flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
              </svg>
              İlerle
            </span>
            <div class="absolute inset-0 shimmer-bg"></div>
          </button>
        </form>
      </div>
    </div>

    <?php endif; ?>

    <!-- Bottom link -->
    <div class="mt-6 slide-up">
      <a href="<?= \App\Helpers\Url::to('/topups') ?>"
         class="text-slate-500 hover:text-indigo-400 text-sm transition-colors underline underline-offset-4">
        Bakiye Yükleme Talepleri
      </a>
    </div>
  </div>

  <!-- ══════════════ SCRIPTS ══════════════ -->
  <?php if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin'): ?>
  <script>
  (function(){
    var CRYPTO_COMM = 2.00;
    function isCrypto() {
      var s=document.getElementById('method_id'); if(!s) return false;
      return (s.options[s.selectedIndex]?.getAttribute('data-type')||'').toLowerCase()==='cryptocurrency';
    }
    function calc(){
      var s=document.getElementById('method_id');
      var amt=parseFloat(document.getElementById('amount')?.value||0);
      var fs=document.getElementById('feeSection'); if(!fs) return;
      if(s){
        var opt=s.options[s.selectedIndex];
        var mn=document.getElementById('method_name'); if(mn) mn.value=opt.getAttribute('data-name')||'';
        var p=parseFloat(opt.getAttribute('data-p')||0);
        var f=parseFloat(opt.getAttribute('data-f')||0);
        if(isCrypto() && amt>0){
          var comm=amt*(CRYPTO_COMM/100); var tot=amt+comm;
          fs.innerHTML='<div class="rounded-2xl overflow-hidden" style="background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2)">'+
            '<div class="flex justify-between px-4 py-3 text-sm"><span class="text-slate-400">Yüklenecek</span><span class="text-white font-semibold">'+amt.toFixed(2)+' USDT</span></div>'+
            '<div class="flex justify-between px-4 py-3 text-sm border-t border-white/5"><span class="text-amber-400">Komisyon (%'+CRYPTO_COMM.toFixed(2)+')</span><span class="text-amber-400 font-semibold">+'+comm.toFixed(2)+' USDT</span></div>'+
            '<div class="flex justify-between px-4 py-3 text-sm border-t border-white/8" style="background:rgba(99,102,241,.08)"><span class="text-indigo-300 font-bold">Toplam</span><span class="text-white font-black">'+tot.toFixed(2)+' USDT</span></div>'+
            '</div>';
        } else if(amt>0 && (p>0||f>0)){
          var fee=(amt*(p/100))+f; var tot2=amt+fee;
          fs.innerHTML='<div class="rounded-2xl overflow-hidden" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08)">'+
            '<div class="flex justify-between px-4 py-3 text-sm"><span class="text-slate-400">Tutar</span><span class="text-white font-semibold">'+amt.toFixed(2)+'</span></div>'+
            '<div class="flex justify-between px-4 py-3 text-sm border-t border-white/5"><span class="text-slate-400">Komisyon</span><span class="text-slate-300">+'+fee.toFixed(2)+'</span></div>'+
            '<div class="flex justify-between px-4 py-3 text-sm border-t border-white/8" style="background:rgba(99,102,241,.08)"><span class="text-indigo-300 font-bold">Toplam</span><span class="text-white font-black">'+tot2.toFixed(2)+'</span></div>'+
            '</div>';
        } else { fs.innerHTML=''; }
      }
      // show/hide note+receipt for crypto
      var ns=document.getElementById('noteSection'); var rs=document.getElementById('receiptSection');
      if(isCrypto()){ if(ns) ns.style.display='none'; if(rs) rs.style.display='none'; }
      else { if(ns) ns.style.display=''; if(rs) rs.style.display=''; }
    }
    var m=document.getElementById('method_id'); if(m) m.addEventListener('change',calc);
    var a=document.getElementById('amount'); if(a) a.addEventListener('input',calc);
    calc();
  })();
  </script>
  <?php endif; ?>

  <?php if (isset($cryptoPaymentData) && $cryptoPaymentData): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
  <script>
  let payInterval, cdInterval;

  function copyAddress() {
    const inp=document.getElementById('walletAddress');
    const btn=document.getElementById('copyBtn');
    const orig=btn.innerHTML;
    const copy=()=>{ btn.innerHTML='✅ Kopyalandı!'; btn.style.background='#059669'; setTimeout(()=>{ btn.innerHTML=orig; btn.style.background=''; },2000); };
    if(navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(inp.value).then(copy).catch(()=>{ inp.select(); document.execCommand('copy'); copy(); });
    } else { inp.select(); inp.setSelectionRange(0,99999); document.execCommand('copy'); copy(); }
  }

  document.addEventListener('DOMContentLoaded',()=>{ genQR(); startMonitor(); startCountdown(); });

  function genQR(){
    const addr='<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>';
    const c=document.getElementById('qrcode');
    try{
      const qr=qrcode(0,'M'); qr.addData(addr); qr.make();
      const img=document.createElement('img');
      img.src=qr.createDataURL(4,8); img.style.cssText='width:180px;height:180px;border-radius:8px;display:block';
      c.appendChild(img);
    } catch(e){ c.innerHTML='<p class="text-slate-500 text-xs p-2">QR oluşturulamadı</p>'; }
  }

  function startMonitor(){ payInterval=setInterval(checkStatus,10000); checkStatus(); }

  function checkStatus(){
    const pid=<?= $cryptoPaymentData['payment_id'] ?? 0 ?>;
    fetch('<?= \App\Helpers\Url::to('/api/check-payment-status.php') ?>',{
      method:'POST', headers:{'Content-Type':'application/json'},
      body:JSON.stringify({payment_id:pid,wallet_address:'<?= htmlspecialchars($cryptoPaymentData['wallet_address']) ?>'})
    }).then(r=>r.ok?r.json():null).then(d=>{ if(d) updateUI(d); }).catch(()=>{});
  }

  function updateUI(d){
    const st=document.getElementById('paymentStatus');
    const pb=document.getElementById('progressBar');
    const lbl=document.getElementById('checkingLabel');
    if(d.status==='confirmed'){
      st.innerHTML='<span style="background:#10b981;width:6px;height:6px;border-radius:50%;display:inline-block;margin-right:6px"></span>✅ Onaylandı';
      pb.style.width='100%'; pb.style.background='linear-gradient(90deg,#10b981,#059669)';
      clearInterval(payInterval); clearInterval(cdInterval);
      document.getElementById('successModal').classList.remove('hidden');
    } else if(d.status==='pending' && d.confirmations>0){
      const pct=Math.min((d.confirmations/19)*100,90);
      st.innerHTML=`<span style="background:#f59e0b;width:6px;height:6px;border-radius:50%;display:inline-block;margin-right:6px"></span>⏳ ${d.confirmations}/19`;
      pb.style.width=(20+pct)+'%';
      if(lbl) lbl.textContent=`${d.confirmations} onay bekleniyor...`;
    } else if(d.status==='detected'){
      st.innerHTML='<span style="background:#3b82f6;width:6px;height:6px;border-radius:50%;display:inline-block;margin-right:6px"></span>🔍 Tespit Edildi';
      pb.style.width='55%';
    }
  }

  function startCountdown(){
    const mins=<?= $cryptoPaymentData['timeout_minutes'] ?? 10 ?>;
    const created=new Date('<?= $cryptoPaymentData['created_at'] ?? '' ?>').getTime();
    const expiry=created+(mins*60000);
    const et=document.getElementById('expiryTime');
    if(et) et.textContent=new Date(expiry).toLocaleTimeString('tr-TR',{hour:'2-digit',minute:'2-digit',hour12:false});
    cdInterval=setInterval(()=>{
      const left=Math.max(0,expiry-Date.now());
      const m=Math.floor(left/60000); const s=Math.floor((left%60000)/1000);
      const cd=document.getElementById('countdown');
      if(cd){ cd.textContent=`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`; if(left===0){cd.style.color='#ef4444';} }
      if(left===0){
        clearInterval(cdInterval); clearInterval(payInterval);
        const st=document.getElementById('paymentStatus');
        if(st){ st.innerHTML='⏰ Süre Doldu'; st.style.color='#fca5a5'; }
      }
    },1000);
  }

  function showCancelModal(){ document.getElementById('cancelModal').classList.remove('hidden'); }
  function hideCancelModal(){ document.getElementById('cancelModal').classList.add('hidden'); }

  function confirmCancel(){
    const pid=<?= $cryptoPaymentData['payment_id'] ?? 0 ?>;
    const gid=<?= $group['id'] ?? 0 ?>;
    hideCancelModal();
    const st=document.getElementById('paymentStatus');
    if(st) st.innerHTML='⏳ İptal ediliyor...';
    fetch('<?= \App\Helpers\Url::to('/groups/cancel-crypto-payment') ?>',{
      method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify({payment_id:pid,group_id:gid})
    }).then(r=>r.ok?r.json():Promise.reject(r.status))
    .then(d=>{
      if(d.success){ clearInterval(payInterval); clearInterval(cdInterval); document.getElementById('cancelSuccessModal').classList.remove('hidden'); }
      else { if(st) st.innerHTML='🔄 Ödeme Bekleniyor'; alert('İptal başarısız: '+(d.error||'Hata')); }
    }).catch(e=>{ if(st) st.innerHTML='🔄 Ödeme Bekleniyor'; alert('Hata: '+e); });
  }
  </script>
  <?php endif; ?>
</body>
</html>