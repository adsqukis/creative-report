<?php $old = $old ?? []; ?>
<div class="max-w-lg">
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="<?= APP_URL ?>/campaigns/store" class="space-y-4">
            <?= Csrf::input() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Nama Campaign <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Produk <span class="text-red-500">*</span></label>
                <select name="product_id" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($products as $prod): ?>
                    <option value="<?= $prod['id'] ?>" <?= (int)($old['product_id'] ?? 0) === (int)$prod['id'] ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Mulai</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($old['start_date'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Selesai</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($old['end_date'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Importance (1-100)</label>
                    <input type="number" name="importance" value="<?= (int)($old['importance'] ?? 50) ?>" min="1" max="100"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['planning'=>'Planning','active'=>'Active','ended'=>'Ended'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($old['status'] ?? 'planning') === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-violet-700">Simpan Campaign</button>
                <a href="<?= APP_URL ?>/campaigns" class="text-sm text-gray-500 hover:text-gray-700 py-2.5">Batal</a>
            </div>
        </form>
    </div>
</div>