<!-- views/users/index.php placeholder - write this file separately -->
<?php
// This file is views/users/index.php
$roleColors = [
    'super_admin'      => 'bg-violet-100 text-violet-700',
    'creative_manager' => 'bg-blue-100 text-blue-700',
    'designer'         => 'bg-indigo-100 text-indigo-700',
    'video_editor'     => 'bg-purple-100 text-purple-700',
    'requester'        => 'bg-teal-100 text-teal-700',
    'viewer'           => 'bg-gray-100 text-gray-500',
];
?>
<div class="flex items-center justify-between mb-4">
    <div></div>
    <?php if (Auth::hasRole(['super_admin'])): ?>
    <a href="<?= APP_URL ?>/users/create" class="inline-flex items-center gap-1.5 bg-violet-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-violet-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add User
    </a>
    <?php endif; ?>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dept</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <?php if (Auth::hasRole(['super_admin'])): ?>
                <th class="px-5 py-3"></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 text-xs font-bold shrink-0">
                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                        </div>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($u['name']) ?></span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-gray-500 text-xs"><?= htmlspecialchars($u['email']) ?></td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $roleColors[$u['role']] ?? 'bg-gray-100 text-gray-600' ?>">
                        <?= ucwords(str_replace('_',' ',$u['role'])) ?>
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500"><?= htmlspecialchars($u['department_name'] ?? '—') ?></td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' ?>">
                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </td>
                <?php if (Auth::hasRole(['super_admin'])): ?>
                <td class="px-5 py-3.5 text-right">
                    <a href="<?= APP_URL ?>/users/<?= (int)$u['id'] ?>/edit" class="text-xs text-violet-600 hover:underline">Edit</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>