<?php $old = $old ?? []; ?>

<div class="max-w-3xl">
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-5 text-sm text-red-700 flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/requests/store" class="space-y-5">
        <?= Csrf::input() ?>
        <!-- Title -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Detail Request</h3>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Judul Request <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Contoh: Banner Harbolnas Generos Milk" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Jenis Asset <span class="text-red-500">*</span></label>
                    <select name="asset_type" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
                        <option value="">-- Pilih --</option>
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
                        <?php foreach (['critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($old['priority'] ?? 'medium') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Estimated Effort</label>
                    <select name="estimated_effort" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                        <?php foreach (['small' => 'Small (~4 jam)', 'medium' => 'Medium (~8 jam)', 'large' => 'Large (~16 jam)'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($old['estimated_effort'] ?? 'medium') === $val ? 'selected' : '' ?>><?= $label ?></option>
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
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Divisi/Department</label>
                <select name="department_id" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= (int)($old['department_id'] ?? 0) === (int)$dept['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Objective</label>
                <input type="text" name="objective" value="<?= htmlspecialchars($old['objective'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Contoh: Meningkatkan awareness Generos Milk di TikTok">
            </div>
        </div>

        <!-- Brief -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800">Brief & Copy</h3>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Brief</label>
                <textarea name="brief" rows="4"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"
                    placeholder="Jelaskan detail kebutuhan desain/video..."><?= htmlspecialchars($old['brief'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Copywriting</label>
                <textarea name="copywriting" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"
                    placeholder="Teks yang harus ada di dalam asset..."><?= htmlspecialchars($old['copywriting'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Reference Link</label>
                <input type="url" name="reference_link" value="<?= htmlspecialchars($old['reference_link'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="https://drive.google.com/...">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button type="submit"
                class="bg-violet-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-violet-700 transition-colors">
                Simpan Request
            </button>
            <a href="<?= APP_URL ?>/requests" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
        </div>
    </form>
</div>