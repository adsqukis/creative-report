<?php $old = $old ?? []; $isEdit = isset($old['id']); ?>
<div class="max-w-lg">
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="<?= $isEdit ? APP_URL . '/departments/' . (int)$old['id'] . '/update' : APP_URL . '/departments/store' ?>" class="space-y-4">
            <?= Csrf::input() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Nama Department <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" placeholder="Contoh: Marketing, Creative, Product" required>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-violet-700">
                    <?= $isEdit ? 'Simpan' : 'Tambah Department' ?>
                </button>
                <a href="<?= APP_URL ?>/departments" class="text-sm text-gray-500 hover:text-gray-700 py-2.5">Batal</a>
            </div>
        </form>
    </div>
</div>
