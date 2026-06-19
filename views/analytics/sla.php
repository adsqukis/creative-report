<?php
$prioColors = [
    'critical' => 'text-red-700 bg-red-50',
    'high'     => 'text-orange-700 bg-orange-50',
    'medium'   => 'text-yellow-700 bg-yellow-50',
    'low'      => 'text-gray-600 bg-gray-50',
];
?>

<!-- SLA Achievement hero -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">SLA Achievement MTD</p>
        <div class="flex items-end gap-3">
            <p class="text-5xl font-black <?= $slaStats['achievement_pct'] >= 80 ? 'text-green-600' : ($slaStats['achievement_pct'] >= 60 ? 'text-yellow-600' : 'text-red-600') ?>">
                <?= $slaStats['achievement_pct'] ?>%
            </p>
            <p class="text-sm text-gray-400 mb-1"><?= $slaStats['on_time'] ?> / <?= $slaStats['total_completed'] ?> request</p>
        </div>
        <div class="mt-3 w-full bg-gray-100 rounded-full h-3">
            <div class="h-3 rounded-full <?= $slaStats['achievement_pct'] >= 80 ? 'bg-green-500' : ($slaStats['achievement_pct'] >= 60 ? 'bg-yellow-400' : 'bg-red-500') ?> transition-all"
                style="width: <?= $slaStats['achievement_pct'] ?>%"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1">Target: 80%</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 mb-1">Avg Turnaround</p>
        <p class="text-3xl font-bold text-gray-900"><?= $slaStats['avg_turnaround_h'] ?>h</p>
        <p class="text-xs text-gray-400 mt-1">Dari request dibuat hingga selesai</p>
    </div>
    <div class="<?= $slaStats['late'] > 0 ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200' ?> border rounded-xl p-5">
        <p class="text-xs <?= $slaStats['late'] > 0 ? 'text-red-500' : 'text-gray-500' ?> mb-1">Terlambat MTD</p>
        <p class="text-3xl font-bold <?= $slaStats['late'] > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $slaStats['late'] ?></p>
        <p class="text-xs text-gray-400 mt-1"><?= $slaStats['late_pct'] ?>% dari total selesai</p>
    </div>
</div>

<!-- By Priority -->
<?php if (!empty($byPriority)): ?>
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">SLA per Priority (MTD)</h3>
    <div class="space-y-3">
        <?php foreach ($byPriority as $row): ?>
        <?php $pct = $row['total'] > 0 ? round(($row['on_time'] / $row['total']) * 100, 1) : 0; ?>
        <div class="flex items-center gap-3">
            <span class="w-20 text-xs font-semibold px-2 py-0.5 rounded-full text-center <?= $prioColors[$row['priority']] ?? '' ?>">
                <?= ucfirst($row['priority']) ?>
            </span>
            <div class="flex-1 bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full <?= $pct >= 80 ? 'bg-green-500' : 'bg-red-400' ?>" style="width: <?= $pct ?>%"></div>
            </div>
            <span class="text-xs font-semibold <?= $pct >= 80 ? 'text-green-700' : 'text-red-700' ?> w-12 text-right"><?= $pct ?>%</span>
            <span class="text-xs text-gray-400 w-16 text-right"><?= $row['on_time'] ?>/<?= $row['total'] ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Late requests table -->
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Terlambat</h3>
    </div>
    <?php if (empty($lateRequests)): ?>
    <div class="p-8 text-center text-sm text-green-600 font-medium">Tidak ada request terlambat!</div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Request</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Terlambat</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Turnaround</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($lateRequests as $req): ?>
                <?php $pc = $prioColors[$req['priority']] ?? 'bg-gray-50 text-gray-600'; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900 text-xs"><?= htmlspecialchars($req['request_number']) ?></p>
                        <p class="text-xs text-gray-500 truncate max-w-xs"><?= htmlspecialchars($req['title']) ?></p>
                    </td>
                    <td class="px-5 py-3"><span class="text-xs px-1.5 py-0.5 rounded font-medium <?= $pc ?>"><?= ucfirst($req['priority']) ?></span></td>
                    <td class="px-5 py-3 text-xs text-gray-600"><?= (new DateTime($req['deadline']))->format('d M Y') ?></td>
                    <td class="px-5 py-3 text-xs font-semibold text-red-700"><?= number_format((int)$req['late_by_hours']) ?>h</td>
                    <td class="px-5 py-3 text-xs text-gray-600"><?= $req['turnaround_hours'] ? number_format((int)$req['turnaround_hours']) . 'h' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>