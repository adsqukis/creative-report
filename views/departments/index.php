<?php
// This file contains: views/departments/index.php
// Rendered when $view = 'departments/index'
?>
<?php if (!empty($_SESSION['department_error'])): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4 text-sm text-red-700"><?= htmlspecialchars($_SESSION['department_error']) ?></div>
<?php unset($_SESSION['department_error']); endif; ?>
<div class="flex items-center justify-between mb-4">
    <div></div>
    <a href="<?= APP_URL ?>/departments/create" class="inline-flex items-center gap-1.5 bg-violet-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-violet-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Department
    </a>
</div>
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Department</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total User</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (empty($departments)): ?>
            <tr>
                <td colspan="3" class="px-5 py-6 text-center text-sm text-gray-400">Belum ada department. Klik "Add Department" untuk menambahkan.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($departments as $dept): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5 font-medium text-gray-900"><?= htmlspecialchars($dept['name']) ?></td>
                <td class="px-5 py-3.5 text-sm text-gray-700"><?= (int)$dept['total_users'] ?></td>
                <td class="px-5 py-3.5 text-right space-x-3">
                    <a href="<?= APP_URL ?>/departments/<?= (int)$dept['id'] ?>/edit" class="text-xs text-violet-600 hover:underline">Edit</a>
                    <form method="POST" action="<?= APP_URL ?>/departments/<?= (int)$dept['id'] ?>/delete" class="inline" onsubmit="return confirm('Hapus department ini?');">
                        <?= Csrf::input() ?>
                        <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
