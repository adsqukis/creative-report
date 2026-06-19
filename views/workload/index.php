<?php
function workloadColor(float $pct): string {
    if ($pct > 100) return 'bg-red-500';
    if ($pct > 80)  return 'bg-orange-400';
    if ($pct > 40)  return 'bg-green-400';
    return 'bg-gray-300';
}
function workloadBg(float $pct): string {
    if ($pct > 100) return 'border-red-200 bg-red-50';
    if ($pct > 80)  return 'border-orange-200 bg-orange-50';
    return 'border-gray-200 bg-white';
}
$prioColors = [
    'critical' => 'bg-red-100 text-red-700',
    'high'     => 'bg-orange-100 text-orange-700',
    'medium'   => 'bg-yellow-100 text-yellow-700',
    'low'      => 'bg-gray-100 text-gray-500',
];
?>

<!-- Summary bar -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Team Members</p>
        <p class="text-2xl font-bold text-gray-900"><?= count($teamWorkload) ?></p>
    </div>
    <div class="<?= $totalOverloaded > 0 ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200' ?> border rounded-xl p-4">
        <p class="text-xs <?= $totalOverloaded > 0 ? 'text-red-500' : 'text-gray-500' ?> mb-1">Overloaded</p>
        <p class="text-2xl font-bold <?= $totalOverloaded > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $totalOverloaded ?></p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Total Active Tasks</p>
        <p class="text-2xl font-bold text-gray-900"><?= $totalActive ?></p>
    </div>
    <div class="<?= $totalOverdue > 0 ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200' ?> border rounded-xl p-4">
        <p class="text-xs <?= $totalOverdue > 0 ? 'text-red-500' : 'text-gray-500' ?> mb-1">Overdue Tasks</p>
        <p class="text-2xl font-bold <?= $totalOverdue > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $totalOverdue ?></p>
    </div>
</div>

<?php if (empty($teamWorkload)): ?>
<div class="bg-white border border-gray-200 rounded-xl p-12 text-center text-sm text-gray-400">
    Belum ada Designer atau Video Editor aktif. Tambahkan user terlebih dahulu.
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ expanded: null }">
    <?php foreach ($teamWorkload as $member): ?>
    <?php
    $uid     = (int)$member['id'];
    $pct     = (float)$member['workload_pct'];
    $barColor = workloadColor($pct);
    $cardBg   = workloadBg($pct);
    $barWidth = min($pct, 100);
    ?>
    <div class="border rounded-xl overflow-hidden <?= $cardBg ?>">
        <!-- Card header -->
        <div class="p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full <?= $pct > 100 ? 'bg-red-200 text-red-700' : 'bg-violet-100 text-violet-700' ?> flex items-center justify-center font-bold text-sm shrink-0">
                    <?= strtoupper(substr($member['name'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-900 truncate"><?= htmlspecialchars($member['name']) ?></p>
                    <p class="text-xs text-gray-400 capitalize"><?= str_replace('_',' ',$member['role']) ?></p>
                </div>
                <?php if ($pct > 100): ?>
                <span class="text-xs bg-red-100 text-red-700 font-semibold px-2 py-0.5 rounded-full">Overload</span>
                <?php elseif ($pct > 80): ?>
                <span class="text-xs bg-orange-100 text-orange-700 font-medium px-2 py-0.5 rounded-full">Warning</span>
                <?php endif; ?>
            </div>

            <!-- Workload bar -->
            <div class="mb-3">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-gray-500">Workload</span>
                    <span class="font-bold <?= $pct > 100 ? 'text-red-700' : ($pct > 80 ? 'text-orange-600' : 'text-gray-700') ?>"><?= $pct ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full <?= $barColor ?> transition-all" style="width: <?= $barWidth ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-0.5"><?= $member['estimated_hours'] ?>h aktif / <?= (int)$member['capacity_hours'] ?>h kapasitas</p>
            </div>

            <!-- Stat grid -->
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="bg-white bg-opacity-60 rounded-lg p-2">
                    <p class="text-lg font-bold text-gray-800"><?= $member['active_tasks'] ?></p>
                    <p class="text-xs text-gray-400">Aktif</p>
                </div>
                <div class="<?= $member['overdue_tasks'] > 0 ? 'bg-red-100' : 'bg-white bg-opacity-60' ?> rounded-lg p-2">
                    <p class="text-lg font-bold <?= $member['overdue_tasks'] > 0 ? 'text-red-700' : 'text-gray-800' ?>"><?= $member['overdue_tasks'] ?></p>
                    <p class="text-xs text-gray-400">Overdue</p>
                </div>
                <div class="bg-white bg-opacity-60 rounded-lg p-2">
                    <p class="text-lg font-bold text-gray-800"><?= $member['tasks_today'] ?></p>
                    <p class="text-xs text-gray-400">Hari ini</p>
                </div>
                <div class="bg-white bg-opacity-60 rounded-lg p-2">
                    <p class="text-lg font-bold text-gray-800"><?= $member['completed_mtd'] ?></p>
                    <p class="text-xs text-gray-400">Done MTD</p>
                </div>
            </div>
        </div>

        <!-- Expandable task list -->
        <?php $memberTasks = $tasksByUser[$uid] ?? []; ?>
        <?php if (!empty($memberTasks)): ?>
        <div x-data="{ open: false }">
            <button @click="open = !open"
                class="w-full px-4 py-2 text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-t border-gray-100 text-left flex items-center justify-between transition-colors">
                <span>Lihat <?= count($memberTasks) ?> task</span>
                <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="border-t border-gray-100 divide-y divide-gray-50">
                <?php foreach ($memberTasks as $t): ?>
                <?php
                $tdl  = new DateTime($t['deadline']);
                $tnow = new DateTime();
                $tpast = $tdl < $tnow;
                ?>
                <div class="px-4 py-2.5 flex items-center gap-3">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium <?= $prioColors[$t['priority']] ?? 'bg-gray-100 text-gray-500' ?>"><?= ucfirst($t['priority']) ?></span>
                    <a href="<?= APP_URL ?>/requests/<?= (int)$t['id'] ?>" class="text-xs text-gray-700 hover:text-violet-600 flex-1 truncate"><?= htmlspecialchars($t['title']) ?></a>
                    <span class="text-xs <?= $tpast ? 'text-red-600 font-medium' : 'text-gray-400' ?> shrink-0"><?= $tdl->format('d M') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>