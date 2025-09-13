<?php $title='Agent Durum - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
<?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>

<div class="animate-in slide-in-from-left-5 duration-500">
  <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-purple-600 mb-8 text-white">
    <div class="relative px-8 py-12 lg:px-12 lg:py-16">
      <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-4">
            <div class="p-4 bg-white/20 backdrop-blur-sm rounded-2xl">
              <i class="fa-solid fa-headset text-4xl"></i>
            </div>
            <div>
              <h1 class="text-4xl lg:text-5xl font-bold">Agent Durumları</h1>
              <p class="text-xl text-white/80 mt-2">Temsilci durumlarını gerçek zamanlı takip edin</p>
            </div>
          </div>

          <?php if ($isSuper): ?>
          <div class="flex gap-3">
            <form method="post" action="/agents/sync" style="display:inline;">
              <button type="submit" class="px-4 py-2 bg-white/20 rounded">Agentleri Güncelle</button>
            </form>
          </div>
          <?php endif; ?>
        </div>

        <?php
        $totalAgents = 0;
        $onlineAgents = 0;
        $activeAgents = 0;
        $ringingAgents = 0;

        if ($isSuper) {
          foreach (($agentsByGroup ?? []) as $groupName => $groupData) {
            $agents = $groupData['agents'] ?? [];
            $totalAgents += count($agents);
            foreach ($agents as $agent) {
              $status = strtolower($agent['status'] ?? '');
              if ($status === 'online' || $status === 'up') $onlineAgents++;
              if ($status === 'ring') $ringingAgents++;
              if ($agent['active'] ?? 1) $activeAgents++;
            }
          }
        } else {
          $groupKey = key($agentsByGroup ?? []);
          $agents = ($agentsByGroup[$groupKey]['agents'] ?? []) ?: [];
          $totalAgents = count($agents);
          foreach ($agents as $agent) {
            $status = strtolower($agent['status'] ?? '');
            if ($status === 'online' || $status === 'up') $onlineAgents++;
            if ($status === 'ring') $ringingAgents++;
            if ($agent['active'] ?? 1) $activeAgents++;
          }
        }
        ?>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $totalAgents; ?></div>
              <div class="text-sm opacity-80">Toplam Agent</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $onlineAgents; ?></div>
              <div class="text-sm opacity-80">Çevrimiçi</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $ringingAgents; ?></div>
              <div class="text-sm opacity-80">Çalıyor</div>
            </div>
          </div>
          <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6">
            <div class="text-right">
              <div class="text-2xl font-bold"><?php echo $activeAgents; ?></div>
              <div class="text-sm opacity-80">Aktif</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php if (!empty($error)): ?>
<div class="mb-6 p-4 bg-red-100 border">
  <span><?php echo htmlspecialchars($error); ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php if ($isSuper): ?>
    <?php foreach (($agentsByGroup ?? []) as $groupIndex => $groupData): ?>
      <div class="mb-8">
        <h3><?php echo htmlspecialchars($groupData['groupName'] ?? 'Grup'); ?></h3>
        <?php foreach (($groupData['agents'] ?? []) as $agentIndex => $a): ?>
          <div class="bg-white p-4 m-2 border rounded">
            <div>Exten: <?php echo htmlspecialchars($a['exten'] ?? ''); ?></div>
            <div>Login: <?php echo htmlspecialchars($a['user_login'] ?? ''); ?></div>
            <div>Status: <?php echo htmlspecialchars($a['status'] ?? ''); ?></div>
            <div>Active: <?php echo htmlspecialchars($a['active'] ?? ''); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <?php
    $agents = $agentsByGroup[key($agentsByGroup ?? [])]['agents'] ?? [];
    foreach ($agents as $agentIndex => $a):
    ?>
      <div class="bg-white p-4 m-2 border rounded">
        <div>Exten: <?php echo htmlspecialchars($a['exten'] ?? ''); ?></div>
        <div>Login: <?php echo htmlspecialchars($a['user_login'] ?? ''); ?></div>
        <div>Status: <?php echo htmlspecialchars($a['status'] ?? ''); ?></div>
        <div>Active: <?php echo htmlspecialchars($a['active'] ?? ''); ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require dirname(__DIR__).'/partials/footer.php'; ?>