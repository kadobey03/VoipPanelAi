<?php $title=__('site_settings').' - '.__('papam_voip_panel'); require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-cogs text-orange-600"></i> <?= __('site_settings') ?></h1>
  </div>
  <form method="post" class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-6">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('site_title_label') ?></label>
        <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? __('papam_voip_panel')) ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('description_label') ?></label>
        <input type="text" name="site_description" value="<?= htmlspecialchars($settings['site_description'] ?? 'VoIP çağrı yönetimi ve raporlama sistemi') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('keywords_label') ?></label>
        <input type="text" name="site_keywords" value="<?= htmlspecialchars($settings['site_keywords'] ?? 'voip, çağrı, panel, rapor') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('seo_image_url') ?></label>
        <input type="text" name="seo_image" value="<?= htmlspecialchars($settings['seo_image'] ?? '/assets/images/seo-image.png') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('api_key') ?></label>
        <input type="text" name="api_key" value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1"><?= __('api_url') ?></label>
        <input type="text" name="api_url" value="<?= htmlspecialchars($settings['api_url'] ?? 'https://panel.momvoip.com/') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
    </div>
    <button type="submit" name="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= __('save') ?></button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>