<?php $title=__('edit_group_title') . ' - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-indigo-600"></i> <?= __('edit_group') ?></h1>
    <a href="<?= \App\Helpers\Url::to('/groups') ?>" class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-700"><?= __('back') ?></a>
  </div>
  <?php if (!empty($error)): ?>
    <div class="mb-3 p-2 rounded bg-red-100 text-red-700"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (!empty($ok)): ?>
    <div class="mb-3 p-2 rounded bg-green-100 text-green-700"><?= htmlspecialchars($ok) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-4 bg-white dark:bg-slate-800 p-4 rounded-xl shadow">
    <div>
      <label class="block text-sm mb-1"><?= __('name') ?></label>
      <input name="name" required class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars($group['name']) ?>">
    </div>
    <?php $isSuper = isset($_SESSION['user']) && ($_SESSION['user']['role']??'')==='superadmin'; ?>
    <?php if ($isSuper): ?>
      <div>
        <label class="block text-sm mb-1"><?= __('margin') ?> %</label>
        <input type="number" step="0.01" name="margin" class="w-full border rounded p-2 bg-white dark:bg-slate-900" value="<?= htmlspecialchars((string)$group['margin']) ?>">
      </div>
      <div>
        <label class="block text-sm mb-1"><?= __('api_group_mapping') ?></label>
        <?php
          $apiGroups = $apiGroups ?? [];
          $selectedApi = $group['api_group_id'] ?? '';
        ?>
        <select name="api_group_id" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
          <option value=""><?= __('not_selected') ?></option>
          <?php foreach ($apiGroups as $ag): $gid=(int)($ag['id']??0); $gname=(string)($ag['name']??''); ?>
            <option value="<?= $gid ?>" <?= $gid===(int)$selectedApi?'selected':'' ?>>#<?= $gid ?> - <?= htmlspecialchars($gname) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($group['api_group_id'])): ?>
        <div class="text-xs text-slate-500 mt-1"><?= __('matched_info') ?>: <?= htmlspecialchars((string)($group['api_group_name'] ?? '')) ?> (#<?= (int)$group['api_group_id'] ?>)</div>
        <?php endif; ?>
      </div>
      
      <!-- Telegram Bildirimleri Bölümü -->
      <div class="border-t pt-4 mt-4">
        <h3 class="text-lg font-medium mb-3 flex items-center gap-2">
          <i class="fab fa-telegram text-blue-500"></i>
          Telegram Bildirimleri
        </h3>
        
        <div class="mb-3">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="telegram_enabled" value="1" class="rounded"
              <?= !empty($group['telegram_enabled']) ? 'checked' : '' ?>>
            <span class="text-sm">Telegram bildirimlerini aktifleştir</span>
          </label>
        </div>
        
        <div class="space-y-3">
          <div>
            <label class="block text-sm mb-1">
              <i class="fas fa-comments text-slate-500"></i>
              Telegram Chat ID
            </label>
            <input name="telegram_chat_id"
                   class="w-full border rounded p-2 bg-white dark:bg-slate-900 font-mono text-sm"
                   value="<?= htmlspecialchars($group['telegram_chat_id'] ?? '') ?>"
                   placeholder="-4931882446 (Negatif grup ID'si)">
            <div class="text-xs text-slate-500 mt-1">
              Bot'u gruba ekleyin ve grup chat ID'sini girin (- işareti ile başlar)
            </div>
          </div>
          
          <div>
            <label class="block text-sm mb-1">
              <i class="fas fa-language text-slate-500"></i>
              Telegram Bildirim Dili
            </label>
            <select name="telegram_language" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
              <?php
                $selectedLang = $group['telegram_language'] ?? 'TR';
                $languages = [
                  'TR' => '🇹🇷 Türkçe',
                  'EN' => '🇺🇸 English',
                  'RU' => '🇷🇺 Русский'
                ];
              ?>
              <?php foreach ($languages as $code => $name): ?>
                <option value="<?= $code ?>" <?= $selectedLang === $code ? 'selected' : '' ?>>
                  <?= htmlspecialchars($name) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="text-xs text-slate-500 mt-1">
              Telegram bildirimlerinin hangi dilde gönderileceğini seçin
            </div>
          </div>
        </div>
        
        <div class="text-xs text-blue-600 bg-blue-50 dark:bg-blue-900/20 p-2 rounded mt-3">
          <i class="fas fa-info-circle"></i>
          <strong>Bilgi:</strong> Telegram Chat ID'si girilerek bu gruba özel bildirimler seçilen dilde alınabilir.
          Boş bırakılırsa sistem varsayılan telegram ayarları kullanılır.
        </div>
      </div>
    <?php endif; ?>
    <div class="text-sm text-slate-500"><?= __('margin_calculation_info') ?></div>
    <button class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded p-2"><?= __('update') ?></button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>
