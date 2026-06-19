<?php $maxAsset = !empty($assetBreakdown) ? max(array_column($assetBreakdown, 'cnt')) : 1; ?>

<!-- Top stats -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Total Request MTD</p>
        <p class="text-2xl font-bold text-gray-900"><?= $stats['total_mtd'] ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Selesai MTD</p>
        <p class="text-2xl font-bold text-gray-900"><?= $stats['completed_mtd'] ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Completion Rate</p>
        <p class="text-2xl font-bold <?= $stats['completion_pct'] >= 80 ? 'text-green-700' : ($stats['completion_pct'] >= 60 ? 'text-yellow-600' : 'text-red-700') ?>">
            <?= $stats['completion_pct'] ?>%
        </p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">SLA Achievement</p>
        <p class="text-2xl font-bold <?= $slaStats['achievement_pct'] >= 80 ? 'text-green-700' : 'text-red-700' ?>">
            <?= $slaStats['achievement_pct'] ?>%
        </p>
    </div>
    <div class="bg-<?= $stats['overdue_now'] > 0 ? 'red-50 border-red-100' : 'white border-gray-200' ?> border rounded-xl p-4">
        <p class="text-xs <?= $stats['overdue_now'] > 0 ? 'text-red-500' : 'text-gray-500' ?> mb-1">Overdue Sekarang</p>
        <p class="text-2xl font-bold <?= $stats['overdue_now'] > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $stats['overdue_now'] ?></p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Asset type breakdown -->
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Produksi per Asset Type (MTD)</h3>
        <?php if (empty($assetBreakdown)): ?>
        <p class="text-sm text-gray-400">Belum ada data.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($assetBreakdown as $row): ?>
            <?php $pct = $maxAsset > 0 ? round(($row['cnt'] / $maxAsset) * 100) : 0; ?>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700"><?= ucwords(str_replace('_',' ',$row['asset_type'])) ?></span>
                    <span class="text-gray-500 font-semibold"><?= $row['cnt'] ?></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-violet-500 transition-all" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top products + requesters -->
    <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Top Products</h3>
            <?php if (empty($topProducts)): ?>
            <p class="text-xs text-gray-400">Belum ada data.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($topProducts as $i => $row): ?>
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded flex items-center justify-center text-xs font-bold <?= $i === 0 ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-500' ?>"><?= $i+1 ?></span>
                    <span class="flex-1 text-xs text-gray-700 truncate"><?= htmlspecialchars($row['name']) ?></span>
                    <span class="text-xs font-semibold text-gray-700"><?= $row['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Top Requesters</h3>
            <?php if (empty($topRequesters)): ?>
            <p class="text-xs text-gray-400">Belum ada data.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($topRequesters as $i => $row): ?>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-full bg-violet-100 flex items-center justify-center text-xs font-bold text-violet-700 shrink-0">
                        <?= strtoupper(substr($row['name'], 0, 1)) ?>
                    </div>
                    <span class="flex-1 text-xs text-gray-700 truncate"><?= htmlspecialchars($row['name']) ?></span>
                    <span class="text-xs font-semibold text-gray-700"><?= $row['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SLA summary -->
<div class="mt-4 bg-white border border-gray-200 rounded-xl p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">SLA Summary MTD</h3>
        <a href="<?= APP_URL ?>/analytics/sla" class="text-xs text-violet-600 hover:underline">Detail SLA &rarr;</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div>
            <p class="text-2xl font-bold text-gray-900"><?= $slaStats['total_completed'] ?></p>
            <p class="text-xs text-gray-400">Total Selesai</p>
        </div>
        <div>
            <p class="text-2xl font-bold text-green-700"><?= $slaStats['on_time'] ?></p>
            <p class="text-xs text-gray-400">Tepat Waktu</p>
        </div>
        <div>
            <p class="text-2xl font-bold <?= $slaStats['late'] > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $slaStats['late'] ?></p>
            <p class="text-xs text-gray-400">Terlambat</p>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900"><?= $slaStats['avg_turnaround_h'] ?>h</p>
            <p class="text-xs text-gray-400">Avg Turnaround</p>
        </div>
    </div>
</div>