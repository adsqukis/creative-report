<!-- Today briefing -->
<?php if ($briefing): ?>
<div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Briefing Hari Ini</h3>
            <p class="text-xs text-gray-400"><?= (new DateTime($briefing['generated_at']))->format('d M Y, H:i') ?> WIB</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if ($briefing['overdue_tasks'] > 0): ?>
            <span class="text-xs bg-red-100 text-red-700 font-semibold px-2.5 py-1 rounded-full"><?= $briefing['overdue_tasks'] ?> Overdue</span>
            <?php endif; ?>
            <?php if ($briefing['critical_tasks'] > 0): ?>
            <span class="text-xs bg-orange-100 text-orange-700 font-semibold px-2.5 py-1 rounded-full"><?= $briefing['critical_tasks'] ?> Critical</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-5 gap-3 mb-5">
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <p class="text-xl font-bold text-gray-900"><?= $briefing['total_tasks_today'] ?></p>
            <p class="text-xs text-gray-500">Deadline hari ini</p>
        </div>
        <div class="bg-<?= $briefing['critical_tasks'] > 0 ? 'red' : 'gray' ?>-50 rounded-lg p-3 text-center">
            <p class="text-xl font-bold <?= $briefing['critical_tasks'] > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $briefing['critical_tasks'] ?></p>
            <p class="text-xs text-gray-500">Critical</p>
        </div>
        <div class="bg-<?= $briefing['overdue_tasks'] > 0 ? 'red' : 'gray' ?>-50 rounded-lg p-3 text-center">
            <p class="text-xl font-bold <?= $briefing['overdue_tasks'] > 0 ? 'text-red-700' : 'text-gray-900' ?>"><?= $briefing['overdue_tasks'] ?></p>
            <p class="text-xs text-gray-500">Overdue</p>
        </div>
        <div class="bg-<?= $briefing['designer_overload_count'] > 0 ? 'orange' : 'gray' ?>-50 rounded-lg p-3 text-center">
            <p class="text-xl font-bold <?= $briefing['designer_overload_count'] > 0 ? 'text-orange-700' : 'text-gray-900' ?>"><?= $briefing['designer_overload_count'] ?></p>
            <p class="text-xs text-gray-500">Designer OL</p>
        </div>
        <div class="bg-<?= $briefing['editor_overload_count'] > 0 ? 'orange' : 'gray' ?>-50 rounded-lg p-3 text-center">
            <p class="text-xl font-bold <?= $briefing['editor_overload_count'] > 0 ? 'text-orange-700' : 'text-gray-900' ?>"><?= $briefing['editor_overload_count'] ?></p>
            <p class="text-xs text-gray-500">Editor OL</p>
        </div>
    </div>

    <!-- Top priorities -->
    <?php
    $tops = [];
    if (!empty($briefing['top_priorities'])) {
        $decoded = json_decode($briefing['top_priorities'], true);
        if (is_array($decoded)) {
            $tops = $decoded;
        }
    }
    ?>
    <?php if (!empty($tops)): ?>
    <div class="mb-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Top Priority Tasks</p>
        <div class="space-y-1.5">
            <?php foreach ($tops as $t): ?>
            <?php $prioC = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-gray-100 text-gray-500']; ?>
            <div class="flex items-center gap-2 text-sm">
                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium <?= $prioC[$t['priority']] ?? 'bg-gray-100 text-gray-600' ?>"><?= ucfirst($t['priority']) ?></span>
                <a href="<?= APP_URL ?>/requests/<?= (int)($t['id'] ?? 0) ?>" class="text-gray-700 hover:text-violet-600 flex-1 truncate text-xs"><?= htmlspecialchars($t['title'] ?? '') ?></a>
                <span class="text-xs text-gray-400 shrink-0"><?= !empty($t['deadline']) ? (new DateTime($t['deadline']))->format('d M') : '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- AI Recommendation -->
    <?php if (!empty($briefing['recommendations'])): ?>
    <div class="bg-violet-50 border border-violet-100 rounded-lg p-4">
        <p class="text-xs font-semibold text-violet-700 mb-1">AI Recommendation</p>
        <p class="text-sm text-violet-800"><?= htmlspecialchars($briefing['recommendations']) ?></p>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="bg-white border border-gray-200 rounded-xl p-12 text-center mb-4">
    <p class="text-sm text-gray-400 mb-3">Briefing hari ini belum dibuat.</p>
    <p class="text-xs text-gray-400">Cron ai_daily_briefing.php berjalan setiap hari pukul 06:00.<br>Untuk membuat manual: <code class="bg-gray-100 px-1.5 py-0.5 rounded">php cron/ai_daily_briefing.php</code></p>
</div>
<?php endif; ?>

<!-- History -->
<?php if (!empty($history)): ?>
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">7 Hari Terakhir</h3>
    </div>
    <div class="divide-y divide-gray-50">
        <?php foreach ($history as $h): ?>
        <?php if ($h['briefing_date'] === date('Y-m-d')) continue; ?>
        <div class="px-5 py-3.5 flex items-center gap-4">
            <div class="w-14 text-xs font-medium text-gray-600"><?= (new DateTime($h['briefing_date']))->format('d M') ?></div>
            <div class="flex items-center gap-3 flex-1">
                <?php if ((int)$h['overdue_tasks'] > 0): ?>
                <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded"><?= $h['overdue_tasks'] ?> overdue</span>
                <?php endif; ?>
                <?php if ((int)$h['critical_tasks'] > 0): ?>
                <span class="text-xs bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded"><?= $h['critical_tasks'] ?> critical</span>
                <?php endif; ?>
                <span class="text-xs text-gray-400"><?= $h['total_tasks_today'] ?> deadline tasks</span>
            </div>
            <?php if ((int)$h['designer_overload_count'] + (int)$h['editor_overload_count'] > 0): ?>
            <span class="text-xs text-orange-500"><?= (int)$h['designer_overload_count'] + (int)$h['editor_overload_count'] ?> overload</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
