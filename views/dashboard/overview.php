<?php
$statusColors = [
    'draft'        => 'bg-gray-100 text-gray-600',
    'waiting_queue'=> 'bg-amber-100 text-amber-700',
    'assigned'     => 'bg-blue-100 text-blue-700',
    'in_progress'  => 'bg-indigo-100 text-indigo-700',
    'revision'     => 'bg-orange-100 text-orange-700',
    'ready_review' => 'bg-purple-100 text-purple-700',
    'approved'     => 'bg-teal-100 text-teal-700',
    'completed'    => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
    'rejected'     => 'bg-red-100 text-red-700',
];
$prioColors = [
    'critical' => 'bg-red-100 text-red-700',
    'high'     => 'bg-orange-100 text-orange-700',
    'medium'   => 'bg-yellow-100 text-yellow-700',
    'low'      => 'bg-gray-100 text-gray-600',
];
?>

<!-- Stats -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1.5">Active Requests</p>
        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['active']) ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1.5">Completed MTD</p>
        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['completed_mtd']) ?></p>
    </div>
    <div class="bg-red-50 border border-red-100 rounded-xl p-4">
        <p class="text-xs text-red-500 mb-1.5">Overdue</p>
        <p class="text-2xl font-bold text-red-700"><?= number_format($stats['overdue']) ?></p>
    </div>
    <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
        <p class="text-xs text-orange-500 mb-1.5">Critical</p>
        <p class="text-2xl font-bold text-orange-700"><?= number_format($stats['critical']) ?></p>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 col-span-2 sm:col-span-1">
        <p class="text-xs text-amber-600 mb-1.5">In Queue</p>
        <p class="text-2xl font-bold text-amber-700"><?= number_format($stats['in_queue']) ?></p>
    </div>
</div>

<!-- Recent Requests -->
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-4 md:px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Recent Requests</h3>
        <a href="<?= APP_URL ?>/requests" class="text-xs font-medium text-violet-600 hover:text-violet-700">Lihat semua &rarr;</a>
    </div>

    <?php if (empty($recentRequests)): ?>
    <div class="py-12 text-center text-sm text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Belum ada request. <a href="<?= APP_URL ?>/requests/create" class="text-violet-600 hover:underline">Buat yang pertama</a>.
    </div>
    <?php else: ?>

    <!-- Desktop table (hidden on mobile) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Request</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Requester</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Priority</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Deadline</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($recentRequests as $req): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="<?= APP_URL ?>/requests/<?= (int)$req['id'] ?>" class="font-medium text-gray-900 hover:text-violet-600 line-clamp-1">
                            <?= htmlspecialchars($req['title']) ?>
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($req['request_number']) ?> &middot; <?= htmlspecialchars(str_replace('_', ' ', $req['asset_type'])) ?></p>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600"><?= htmlspecialchars($req['requester_name'] ?? '-') ?></td>
                    <td class="px-5 py-3.5">
                        <?php $sc = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600'; ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium <?= $sc ?>">
                            <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php $pc = $prioColors[$req['priority']] ?? 'bg-gray-100 text-gray-600'; ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium <?= $pc ?>">
                            <?= ucfirst($req['priority']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <?php
                        $dl   = new DateTime($req['deadline']);
                        $now  = new DateTime();
                        $diff = (int)$now->diff($dl)->days;
                        $past = $dl < $now;
                        ?>
                        <span class="text-xs <?= $past ? 'text-red-600 font-semibold' : ($diff <= 3 ? 'text-orange-600 font-medium' : 'text-gray-600') ?>">
                            <?= $dl->format('d M Y') ?>
                            <?php if ($past): ?>
                            <span class="block text-red-500">Overdue</span>
                            <?php elseif ($diff <= 3): ?>
                            <span class="block text-orange-500">H-<?= $diff ?></span>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile card list (shown on mobile only) -->
    <div class="md:hidden divide-y divide-gray-50">
        <?php foreach ($recentRequests as $req): ?>
        <?php
        $sc   = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600';
        $pc   = $prioColors[$req['priority']] ?? 'bg-gray-100 text-gray-600';
        $dl   = new DateTime($req['deadline']);
        $now  = new DateTime();
        $diff = (int)$now->diff($dl)->days;
        $past = $dl < $now;
        ?>
        <a href="<?= APP_URL ?>/requests/<?= (int)$req['id'] ?>" class="block px-4 py-3.5 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-sm font-medium text-gray-900 line-clamp-1 flex-1"><?= htmlspecialchars($req['title']) ?></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium <?= $sc ?> shrink-0">
                    <?= ucwords(str_replace('_', ' ', $req['status'])) ?>
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-gray-400"><?= htmlspecialchars($req['request_number']) ?></span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium <?= $pc ?>"><?= ucfirst($req['priority']) ?></span>
                <span class="text-xs <?= $past ? 'text-red-600 font-semibold' : ($diff <= 3 ? 'text-orange-600' : 'text-gray-400') ?> ml-auto">
                    <?= $past ? 'Overdue · ' : '' ?><?= $dl->format('d M Y') ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>