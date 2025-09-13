<?php $title='Ödeme Yöntemleri - PapaM VoIP Panel'; require dirname(__DIR__).'/partials/header.php'; ?>
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-money-bill-transfer text-emerald-600"></i> Ödeme Yöntemleri</h1>
    <a href="<?= \App\Helpers\Url::to('/payment-methods/create') ?>" class="px-3 py-2 rounded bg-indigo-600 text-white">Yeni Yöntem</a>
  </div>
  <div class="bg-white/80 dark:bg-slate-800 rounded-xl shadow overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/40"><tr class="border-b border-slate-200 dark:border-slate-700 text-left"><th class="p-2">Ad</th><th class="p-2">Tip</th><th class="p-2">Komisyon</th><th class="p-2">Durum</th><th class="p-2">İşlem</th></tr></thead>
      <tbody>
        <?php foreach (($items ?? []) as $pm): ?>
        <tr class="border-b border-slate-100 dark:border-slate-700/60">
          <td class="p-2"><?= htmlspecialchars($pm['name']) ?></td>
          <td class="p-2"><?= htmlspecialchars($pm['method_type']) ?></td>
          <td class="p-2"><?= number_format((float)$pm['fee_percent'],2) ?>% + <?= number_format((float)$pm['fee_fixed'],2) ?></td>
          <td class="p-2"><?= ((int)$pm['active']===1)?'Aktif':'Pasif' ?></td>
          <td class="p-2 space-x-2">
            <a class="inline-flex items-center gap-1 text-indigo-600 hover:underline" href="<?= \App\Helpers\Url::to('/payment-methods/edit') ?>?id=<?= (int)$pm['id'] ?>"><i class="fa-regular fa-pen-to-square"></i> Düzenle</a>
            <form method="post" action="<?= \App\Helpers\Url::to('/payment-methods/delete') ?>" style="display:inline" onsubmit="return confirm('Silinsin mi?')">
              <input type="hidden" name="id" value="<?= (int)$pm['id'] ?>">
              <button class="inline-flex items-center gap-1 text-rose-600"><i class="fa-regular fa-trash-can"></i> Sil</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require dirname(__DIR__).'/partials/footer.php'; ?>

