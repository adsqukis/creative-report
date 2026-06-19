<div class="flex items-center justify-between mb-4">
    <div></div>
    <a href="<?= APP_URL ?>/campaigns/create" class="inline-flex items-center gap-1.5 bg-violet-600 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-violet-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Add Campaign
    </a>
</div>
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <?php if (empty($campaigns)): ?>
    <p class="p-8 text-center text-sm text-gray-400">Belum ada campaign.</p>
    <?php else: ?>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Campaign</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requests</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($campaigns as $camp): ?>
            <?php $sc = ['planning'=>'bg-gray-100 text-gray-600','active'=>'bg-green-100 text-green-700','ended'=>'bg-gray-100 text-gray-400']; ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3.5 font-medium text-gray-900"><?= htmlspecialchars($camp['name']) ?></td>
                <td class="px-5 py-3.5 text-gray-600 text-xs"><?= htmlspecialchars($camp['product_name'] ?? '—') ?></td>
                <td class="px-5 py-3.5"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $sc[$camp['status']] ?? '' ?>"><?= ucfirst($camp['status']) ?></span></td>
                <td class="px-5 py-3.5 text-xs text-gray-500">
                    <?= $camp['start_date'] ? (new DateTime($camp['start_date']))->format('d M Y') : '—' ?>
                    <?= $camp['end_date'] ? ' — ' . (new DateTime($camp['end_date']))->format('d M Y') : '' ?>
                </td>
                <td class="px-5 py-3.5 text-sm font-medium"><?= $camp['total_requests'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
