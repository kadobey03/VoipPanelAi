<?php $title='Site Ayarları - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-cogs text-orange-600"></i> Site Ayarları</h1>
  </div>
  <form method="post" class="bg-white/80 dark:bg-slate-800 rounded-xl shadow p-6">
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Site Başlığı</label>
        <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? 'PapaM VoIP Panel') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Açıklama</label>
        <input type="text" name="site_description" value="<?= htmlspecialchars($settings['site_description'] ?? 'VoIP çağrı yönetimi ve raporlama sistemi') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Anahtar Kelimeler</label>
        <input type="text" name="site_keywords" value="<?= htmlspecialchars($settings['site_keywords'] ?? 'voip, çağrı, panel, rapor') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">SEO Görsel URL</label>
        <input type="text" name="seo_image" value="<?= htmlspecialchars($settings['seo_image'] ?? '/assets/images/seo-image.png') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">API Anahtarı</label>
        <input type="text" name="api_key" value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">API URL</label>
        <input type="text" name="api_url" value="<?= htmlspecialchars($settings['api_url'] ?? 'https://panel.momvoip.com/') ?>" class="w-full border rounded p-2 bg-white dark:bg-slate-900">
      </div>
    </div>
    <button type="submit" name="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Kaydet</button>
  </form>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>