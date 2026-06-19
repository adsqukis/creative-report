<div class="max-w-md">
    <?php if (!empty($_GET['saved'])): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-5 text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Profil berhasil disimpan.
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/profile/update" class="space-y-4">
        <?= Csrf::input() ?>

        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <div class="flex items-center gap-4 mb-2">
                <div class="w-14 h-14 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-bold text-xl">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                    <p class="text-sm text-gray-500 capitalize"><?= str_replace('_', ' ', $user['role'] ?? '') ?></p>
                    <?php if ($user['last_login_at']): ?>
                    <p class="text-xs text-gray-400">Login terakhir: <?= (new DateTime($user['last_login_at']))->format('d M Y H:i') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Nama</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Email</label>
                <input type="text" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h3 class="text-sm font-semibold text-gray-800">Ganti Password</h3>
            <p class="text-xs text-gray-400">Kosongkan jika tidak ingin mengganti password.</p>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Password Baru</label>
                <input type="password" name="password" minlength="8"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Min 8 karakter">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
                <input type="password" name="password_confirm" minlength="8"
                    class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Ulangi password baru">
            </div>
        </div>

        <button type="submit" class="bg-violet-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-violet-700">Simpan Perubahan</button>
    </form>
</div>