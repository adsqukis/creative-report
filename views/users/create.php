<?php $old = $old ?? []; $isEdit = isset($old['id']); ?>
<div class="max-w-lg">
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-5 text-sm text-red-700"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="<?= $isEdit ? APP_URL . '/users/' . (int)$old['id'] . '/update' : APP_URL . '/users/store' ?>" class="space-y-4">
            <?= Csrf::input() ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
            </div>
            <?php if (!$isEdit): ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
            </div>
            <?php endif; ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Password <?= $isEdit ? '(kosongkan jika tidak ganti)' : '<span class="text-red-500">*</span>' ?></label>
                <input type="password" name="password" minlength="8"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Min 8 karakter" <?= !$isEdit ? 'required' : '' ?>>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Role</label>
                    <select name="role" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['super_admin','creative_manager','designer','video_editor','requester','viewer'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($old['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$r)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Capacity (jam/minggu)</label>
                    <input type="number" name="capacity_hours_per_week" value="<?= htmlspecialchars($old['capacity_hours_per_week'] ?? '40') ?>" min="1" max="60" step="1"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Department</label>
                    <select name="department_id" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= (int)($old['department_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isEdit): ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Status</label>
                    <select name="is_active" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <option value="1" <?= (int)($old['is_active'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int)($old['is_active'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-violet-700">
                    <?= $isEdit ? 'Simpan' : 'Tambah User' ?>
                </button>
                <a href="<?= APP_URL ?>/users" class="text-sm text-gray-500 hover:text-gray-700 py-2.5">Batal</a>
            </div>
        </form>
    </div>
</div>