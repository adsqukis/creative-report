<!-- Executive Command Center -->
<div class="space-y-5">
    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="bg-white border border-gray-200 rounded-xl p-4 md:col-span-1">
            <p class="text-xs text-gray-500 mb-1.5">Total Open</p>
            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_open'] ?></p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-500 mb-1.5">Critical Open</p>
            <p class="text-2xl font-bold text-red-700"><?= $stats['critical_open'] ?></p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-xs text-red-600 mb-1.5">Overdue</p>
            <p class="text-2xl font-bold text-red-800"><?= $stats['overdue'] ?></p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
            <p class="text-xs text-amber-600 mb-1.5">Today's Deadline</p>
            <p class="text-2xl font-bold text-amber-700"><?= $stats['today_deadline'] ?></p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs text-orange-500 mb-1.5">Urgent Unassigned</p>
            <p class="text-2xl font-bold text-orange-700"><?= $stats['unassigned_urgent'] ?></p>
        </div>
    </div>

    <!-- Team Workload -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Team Active Tasks</h3>
        </div>
        <?php if (empty($teamWorkload)): ?>
        <p class="p-5 text-sm text-gray-400">Belum ada data workload.</p>
        <?php else: ?>
        <div class="divide-y divide-gray-50">
            <?php foreach ($teamWorkload as $tw): ?>
            <div class="flex items-center gap-4 px-5 py-3.5">
                <div class="w-7 h-7 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-semibold text-xs shrink-0">
                    <?= strtoupper(substr($tw['name'], 0, 1)) ?>
                </div>
                <p class="text-sm font-medium text-gray-800 flex-1"><?= htmlspecialchars($tw['name']) ?></p>
                <span class="text-sm font-semibold <?= (int)$tw['task_count'] > 5 ? 'text-red-600' : 'text-gray-700' ?>">
                    <?= $tw['task_count'] ?> tasks
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick links -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="<?= APP_URL ?>/workload" class="bg-white border border-gray-200 rounded-xl p-4 hover:border-violet-300 hover:bg-violet-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-violet-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
            <p class="text-sm font-medium text-gray-800">Workload Board</p>
            <p class="text-xs text-gray-400">Lihat kapasitas tim</p>
        </a>
        <a href="<?= APP_URL ?>/ai/insights" class="bg-white border border-gray-200 rounded-xl p-4 hover:border-violet-300 hover:bg-violet-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-violet-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <p class="text-sm font-medium text-gray-800">AI Insights</p>
            <p class="text-xs text-gray-400">Analisis otomatis</p>
        </a>
        <a href="<?= APP_URL ?>/ai/briefing" class="bg-white border border-gray-200 rounded-xl p-4 hover:border-violet-300 hover:bg-violet-50 transition-colors group">
            <svg class="w-5 h-5 text-gray-400 group-hover:text-violet-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            <p class="text-sm font-medium text-gray-800">Daily Briefing</p>
            <p class="text-xs text-gray-400">Ringkasan hari ini</p>
        </a>
    </div>
</div>