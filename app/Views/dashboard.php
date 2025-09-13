<?php $title='Dashboard - PapaM VoIP Panel'; require __DIR__.'/partials/header.php'; ?>

  <?php $isSuper = isset($_SESSION['user']) && (($_SESSION['user']['role'] ?? '')==='superadmin'); ?>

  <section class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <?php if ($isSuper): ?>
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-indigo-600/10 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-wallet"></i></div>
          <div>
            <div class="text-sm text-slate-500">Ana Bakiye (API)</div>
            <div class="text-2xl font-semibold"><span id="balance"><?= isset($balanceValue) && $balanceValue!==null ? htmlspecialchars(number_format((float)$balanceValue,2)) : '...' ?></span></div>
          </div>
        </div>
      </div>
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-blue-600/10 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-layer-group"></i></div>
          <div>
            <div class="text-sm text-slate-500">Gruplar Toplam Bakiye</div>
            <div class="text-2xl font-semibold"><?= htmlspecialchars(number_format((float)($groupsTotal ?? 0),2)) ?></div>
          </div>
        </div>
      </div>
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-amber-600/10 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-scale-balanced"></i></div>
          <div>
            <div class="text-sm text-slate-500">Fark (Ana - Gruplar)</div>
            <div class="text-2xl font-semibold"><?= isset($diff) && $diff!==null ? htmlspecialchars(number_format((float)$diff,2)) : '...' ?></div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-emerald-600/10 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-piggy-bank"></i></div>
          <div>
            <div class="text-sm text-slate-500">Grup Bakiyesi</div>
            <div class="text-2xl font-semibold"><?= htmlspecialchars(number_format((float)($ownGroupBalance ?? 0),2)) ?></div>
          </div>
        </div>
      </div>
      <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5 block">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-blue-600/10 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-layer-group"></i></div>
          <div>
            <div class="text-sm text-slate-500">Grubum</div>
            <div class="text-lg font-medium">Görüntüle</div>
          </div>
        </div>
      </a>
      <?php endif; ?>
      <div class="p-5 rounded-xl bg-white/80 dark:bg-slate-800 shadow hover:shadow-lg transition transform hover:-translate-y-0.5">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-lg bg-rose-600/10 text-rose-600 flex items-center justify-center"><i class="fa-solid fa-sack-dollar"></i></div>
          <div>
            <?php if ($isSuper): ?>
              <div class="text-sm text-slate-500">Haftalık Kâr</div>
              <div class="text-2xl font-semibold"><?= htmlspecialchars(number_format((float)($weeklyProfit ?? 0),2)) ?></div>
            <?php else: ?>
              <div class="text-sm text-slate-500">Bu Hafta Harcama</div>
              <div class="text-2xl font-semibold"><?= htmlspecialchars(number_format((float)($weeklyRevenue ?? 0),2)) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <a href="<?= \App\Helpers\Url::to('/users') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-users text-indigo-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Kullanıcılar</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/reports') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-chart-line text-emerald-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Raporlar</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/agents') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-headset text-rose-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Agent Durum</div>
    </a>
    <a href="<?= \App\Helpers\Url::to('/numbers') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-address-book text-amber-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Dış Numaralar</div>
    </a>
    <?php if ($isSuper): ?>
    <a href="<?= \App\Helpers\Url::to('/balance') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-wallet text-fuchsia-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Ana Bakiye</div>
    </a>
    <?php endif; ?>
    <a href="<?= \App\Helpers\Url::to('/logout') ?>" class="group rounded-lg p-4 bg-white/70 dark:bg-slate-800 shadow hover:shadow-xl transition flex items-center gap-3">
      <i class="fa-solid fa-right-from-bracket text-slate-600 group-hover:animate-bounce"></i>
      <div class="font-medium">Çıkış</div>
    </a>
  </section>

  <section class="mt-6 grid md:grid-cols-2 gap-4">
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <h3 class="text-sm text-slate-500 mb-2"><?php echo $isSuper ? 'Gelir/Maliyet (7 Gün)' : 'Harcama (7 Gün)'; ?></h3>
      <canvas id="trendLine" height="140"></canvas>
    </div>
    <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-4">
      <h3 class="text-sm text-slate-500 mb-2">Günlük Çağrı Adedi (7 Gün)</h3>
      <canvas id="callsBar" height="140"></canvas>
    </div>
  </section>

  <script src="<?= \App\Helpers\Url::to('/public/assets/js/chart.min.js') ?>"></script>
  <script>if(typeof Chart==='undefined'){var s=document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/chart.js';document.head.appendChild(s);}</script>
  <script>
    (function(){
      function draw(){
        if (typeof Chart==='undefined') { return setTimeout(draw,200); }
        var labels = <?= json_encode($chartLabels ?? []) ?>;
        var revenue = <?= json_encode($chartRevenue ?? []) ?>;
        var cost = <?= json_encode($chartCost ?? []) ?>;
        var calls = <?= json_encode($chartCalls ?? []) ?>;
        var lctx = document.getElementById('trendLine').getContext('2d');
        var datasets = [];
        <?php if ($isSuper): ?>
          datasets.push({label:'Gelir', data:revenue, borderColor:'rgba(16,185,129,1)', backgroundColor:'rgba(16,185,129,0.2)', tension:.25});
          datasets.push({label:'Maliyet', data:cost, borderColor:'rgba(239,68,68,1)', backgroundColor:'rgba(239,68,68,0.2)', tension:.25});
        <?php else: ?>
          datasets.push({label:'Harcama', data:revenue, borderColor:'rgba(59,130,246,1)', backgroundColor:'rgba(59,130,246,0.2)', tension:.25});
        <?php endif; ?>
        new Chart(lctx, { type:'line', data:{ labels:labels, datasets:datasets }, options:{ responsive:true, plugins:{legend:{position:'bottom'}}, scales:{y:{beginAtZero:true}} } });
        var bctx = document.getElementById('callsBar').getContext('2d');
        new Chart(bctx, { type:'bar', data:{ labels:labels, datasets:[{label:'Çağrılar', data:calls, backgroundColor:'rgba(99,102,241,0.5)'}] }, options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} } });
      }
      draw();
    })();
  </script>
  <script src="<?= \App\Helpers\Url::to('/public/assets/js/dashboard.js') ?>"></script>

<?php require __DIR__.'/partials/footer.php'; ?>

