<?php $old = $old ?? $request ?? []; ?>

<div class="max-w-3xl">
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-5 text-sm text-red-700">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/requests/<?= (int)$old['id'] ?>/update" class="space-y-5">
        <?= Csrf::input() ?>
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800">Edit Request <span class="text-gray-400 font-normal"><?= htmlspecialchars($old['request_number'] ?? '') ?></span></h3>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Judul Request <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Jenis Asset <span class="text-red-500">*</span></label>
                    <select name="asset_type" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
                        <?php foreach ($assetTypes as $at): ?>
                        <option value="<?= $at ?>" <?= ($old['asset_type'] ?? '') === $at ? 'selected' : '' ?>>
                            <?= ucwords(str_replace('_',' ',$at)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Deadline <span class="text-red-500">*</span></label>
                    <input type="date" name="deadline" value="<?= htmlspecialchars($old['deadline'] ?? '') ?>"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Priority</label>
                    <select name="priority" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['critical'=>'Critical','high'=>'High','medium'=>'Medium','low'=>'Low'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($old['priority'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Estimated Effort</label>
                    <select name="estimated_effort" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['small'=>'Small (~4 jam)','medium'=>'Medium (~8 jam)','large'=>'Large (~16 jam)'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($old['estimated_effort'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Produk</label>
                    <select name="product_id" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $prod): ?>
                        <option value="<?= $prod['id'] ?>" <?= (int)($old['product_id'] ?? 0) === (int)$prod['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prod['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Campaign</label>
                    <select name="campaign_id" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <option value="">-- Pilih Campaign --</option>
                        <?php foreach ($campaigns as $camp): ?>
                        <option value="<?= $camp['id'] ?>" <?= (int)($old['campaign_id'] ?? 0) === (int)$camp['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($camp['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Objective</label>
                <input type="text" name="objective" value="<?= htmlspecialchars($old['objective'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800">Brief & Copy</h3>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Brief</label>
                <textarea name="brief" rows="4"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"><?= htmlspecialchars($old['brief'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Copywriting</label>
                <textarea name="copywriting" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"><?= htmlspecialchars($old['copywriting'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Reference Link</label>
                <input type="url" name="reference_link" value="<?= htmlspecialchars($old['reference_link'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="https://...">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-violet-700">Simpan Perubahan</button>
            <a href="<?= APP_URL ?>/requests/<?= (int)($old['id'] ?? 0) ?>" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
        </div>
    </form>
</div>