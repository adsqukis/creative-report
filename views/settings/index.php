<div class="max-w-xl">
    <?php if (!empty($_GET['saved'])): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-5 text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Settings berhasil disimpan.
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/settings/update" class="space-y-4">
        <?= Csrf::input() ?>

        <!-- App -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800">App</h3>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">App Name</label>
                <input type="text" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'Creative Ops') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
            </div>
        </div>

        <!-- WhatsApp / Fonnte -->
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">WhatsApp — Fonnte</h3>
                <label class="flex items-center gap-2 cursor-pointer">
                    <span class="text-xs text-gray-500">Aktif</span>
                    <div class="relative">
                        <input type="checkbox" name="wa_enabled" value="1" class="sr-only peer" <?= ($settings['wa_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer-checked:bg-violet-500 transition-colors"></div>
                        <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Fonnte API Token</label>
                <input type="text" name="fonnte_token" value="<?= htmlspecialchars($settings['fonnte_token'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Dapatkan dari app.fonnte.com">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Group ID (untuk notif tim)</label>
                <input type="text" name="fonnte_group_id" value="<?= htmlspecialchars($settings['fonnte_group_id'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="xxx@g.us">
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">
                    Dapatkan token dari <a href="https://app.fonnte.com/dashboard" target="_blank" class="text-violet-600 hover:underline">app.fonnte.com</a>.
                    Group ID didapat dari kontak grup di dashboard Fonnte (format: angka@g.us).
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-violet-700">Simpan Settings</button>
        </div>
    </form>
</div>