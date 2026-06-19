<?php $old = $old ?? []; $isEdit = isset($old['id']); ?>
<div class="max-w-lg">
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="<?= $isEdit ? APP_URL . '/products/' . (int)$old['id'] . '/update' : APP_URL . '/products/store' ?>" class="space-y-4">
            <?= Csrf::input() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Kode</label>
                    <input type="text" name="code" value="<?= htmlspecialchars($old['code'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" placeholder="Contoh: GNR">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Priority Level</label>
                    <select name="priority_level" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['critical'=>'Critical','high'=>'High','medium'=>'Medium','low'=>'Low'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($old['priority_level'] ?? 'medium') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Business Importance (1-100)</label>
                    <input type="number" name="business_importance" value="<?= (int)($old['business_importance'] ?? 50) ?>" min="1" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Monthly Budget (Rp)</label>
                    <input type="number" name="monthly_budget" value="<?= (float)($old['monthly_budget'] ?? 0) ?>" min="0" step="1000000"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Target Revenue (Rp)</label>
                    <input type="number" name="target_revenue" value="<?= (float)($old['target_revenue'] ?? 0) ?>" min="0" step="1000000"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-violet-700">
                    <?= $isEdit ? 'Simpan' : 'Tambah Produk' ?>
                </button>
                <a href="<?= APP_URL ?>/products" class="text-sm text-gray-500 hover:text-gray-700 py-2.5">Batal</a>
            </div>
        </form>
    </div>
</div>