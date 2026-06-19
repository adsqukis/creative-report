<?php
$prioColors = [
    'critical' => 'border-red-200 bg-red-50',
    'high'     => 'border-orange-200 bg-orange-50',
    'medium'   => 'bg-white border-gray-200',
    'low'      => 'bg-white border-gray-200',
];
$prioBadge = [
    'critical' => 'bg-red-100 text-red-700 font-semibold',
    'high'     => 'bg-orange-100 text-orange-700',
    'medium'   => 'bg-yellow-100 text-yellow-700',
    'low'      => 'bg-gray-100 text-gray-500',
];
$statusColors = [
    'assigned'    => 'bg-blue-100 text-blue-700',
    'in_progress' => 'bg-indigo-100 text-indigo-700',
    'revision'    => 'bg-orange-100 text-orange-700',
    'ready_review' => 'bg-purple-100 text-purple-700',
];
?>

<!-- Stats bar -->
<div class="flex items-center gap-4 mb-5">
    <div class="bg-white border border-gray-200 rounded-xl px-4 py-3">
        <span class="text-xs text-gray-500">Active Tasks</span>
        <span class="text-lg font-bold text-gray-900 ml-2"><?= $totalActive ?></span>
    </div>
    <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3">
        <span class="text-xs text-red-500">Overdue</span>
        <span class="text-lg font-bold text-red-700 ml-2"><?= $totalOverdue ?></span>
    </div>
    <div class="bg-orange-50 border border-orange-100 rounded-xl px-4 py-3">
        <span class="text-xs text-orange-500">Needs Revision</span>
        <span class="text-lg font-bold text-orange-700 ml-2"><?= count($revisionTasks) ?></span>
    </div>
</div>

<?php if (!empty($revisionTasks)): ?>
<!-- Revision queue banner -->
<div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
    <p class="text-sm font-semibold text-orange-800 mb-2">Revision Queue (<?= count($revisionTasks) ?>)</p>
    <div class="space-y-1">
        <?php foreach ($revisionTasks as $rv): ?>
        <div class="flex items-center justify-between text-sm">
            <a href="<?= APP_URL ?>/requests/<?= (int)$rv['id'] ?>" class="text-orange-700 hover:underline font-medium"><?= htmlspecialchars($rv['title']) ?></a>
            <span class="text-xs text-orange-500"><?= (new DateTime($rv['deadline']))->format('d M') ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Task cards -->
<div class="mb-3">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Priority Queue — My Tasks</h3>
    <?php if (empty($myTasks)): ?>
    <div class="bg-white border border-gray-200 rounded-xl py-16 text-center text-sm text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Tidak ada task aktif. Tunggu assignment dari Creative Manager.
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($myTasks as $task): ?>
        <?php
        $dl   = new DateTime($task['deadline']);
        $now  = new DateTime();
        $diff = (int)$now->diff($dl)->days;
        $past = $dl < $now;
        $cardBg = $prioColors[$task['priority']] ?? 'bg-white border-gray-200';
        ?>
        <div class="border rounded-xl p-4 <?= $cardBg ?> flex items-start gap-4">
            <!-- Score bubble -->
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold shrink-0
                <?= (int)$task['priority_score'] >= 70 ? 'bg-red-200 text-red-800' : ((int)$task['priority_score'] >= 40 ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-600') ?>">
                <?= $task['priority_score'] ?>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                    <span class="text-xs font-mono text-gray-400"><?= htmlspecialchars($task['request_number']) ?></span>
                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs <?= $prioBadge[$task['priority']] ?? '' ?>"><?= ucfirst($task['priority']) ?></span>
                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs <?= $statusColors[$task['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= ucwords(str_replace('_',' ',$task['status'])) ?></span>
                    <?php if ((int)$task['revision_count'] > 0): ?>
                    <span class="text-xs text-orange-500">Rev #<?= $task['revision_count'] ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= APP_URL ?>/requests/<?= (int)$task['id'] ?>" class="text-sm font-semibold text-gray-900 hover:text-violet-600 line-clamp-1 block">
                    <?= htmlspecialchars($task['title']) ?>
                </a>
                <p class="text-xs text-gray-500 mt-0.5">
                    <?= ucwords(str_replace('_',' ',$task['asset_type'])) ?>
                    <?php if ($task['product_name']): ?> &middot; <?= htmlspecialchars($task['product_name']) ?><?php endif; ?>
                </p>
            </div>

            <!-- Deadline + action -->
            <div class="text-right shrink-0">
                <p class="text-xs font-semibold <?= $past ? 'text-red-600' : ($diff <= 3 ? 'text-orange-600' : 'text-gray-600') ?> mb-2">
                    <?= $past ? 'Overdue' : ($diff === 0 ? 'Hari ini' : 'H-' . $diff) ?>
                    <br><span class="font-normal text-gray-400"><?= $dl->format('d M') ?></span>
                </p>
                <?php if ($task['status'] === 'assigned'): ?>
                <form method="POST" action="<?= APP_URL ?>/workspace/<?= (int)$task['id'] ?>/start"><?= Csrf::input() ?>
                    <button class="bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-700">Mulai</button>
                </form>
                <?php elseif (in_array($task['status'], ['in_progress','revision'], true)): ?>
                <form method="POST" action="<?= APP_URL ?>/workspace/<?= (int)$task['id'] ?>/submit"><?= Csrf::input() ?>
                    <button class="bg-purple-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-purple-700">Submit</button>
                </form>
                <?php else: ?>
                <a href="<?= APP_URL ?>/requests/<?= (int)$task['id'] ?>" class="text-xs text-violet-600 hover:underline">Lihat</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>