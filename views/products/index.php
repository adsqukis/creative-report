<?php
// This file contains: views/products/index.php
// Rendered when $view = 'products/index'
?>
<div class="flex items-center justify-between mb-4">
    <div></div>
    <a href="<?= APP_URL ?>/products/create" class="inline-flex items-center gap-1.5 bg-violet-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-violet-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Importance</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Budget/Bulan</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Request</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($products as $prod): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5">
                    <span class="font-medium text-gray-900"><?= htmlspecialchars($prod['name']) ?></span>
                    <?php if ($prod['code']): ?>
                    <span class="ml-2 text-xs text-gray-400 font-mono"><?= htmlspecialchars($prod['code']) ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3.5">
                    <?php $pc = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-gray-100 text-gray-500']; ?>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $pc[$prod['priority_level']] ?? '' ?>"><?= ucfirst($prod['priority_level']) ?></span>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-700"><?= $prod['business_importance'] ?>/100</td>
                <td class="px-5 py-3.5 text-sm text-gray-700"><?= $prod['monthly_budget'] > 0 ? 'Rp ' . number_format((float)$prod['monthly_budget'], 0, ',', '.') : '—' ?></td>
                <td class="px-5 py-3.5 text-sm font-medium text-gray-900"><?= $prod['total_requests'] ?></td>
                <td class="px-5 py-3.5 text-right">
                    <a href="<?= APP_URL ?>/products/<?= (int)$prod['id'] ?>/edit" class="text-xs text-violet-600 hover:underline">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
