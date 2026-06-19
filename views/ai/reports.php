<div class="max-w-2xl">
    <?php if (empty($reports)): ?>
    <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm font-medium text-gray-600 mb-1">AI Reports — Coming in Phase 5</p>
        <p class="text-xs text-gray-400">Daily, Weekly, Monthly reports akan dibuat otomatis dan tersedia dalam format PDF dan Excel.</p>
    </div>
    <?php else: ?>
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Generated Reports</h3>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($reports as $rpt): ?>
            <div class="px-5 py-4 flex items-center gap-3">
                <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800"><?= ucfirst($rpt['report_type']) ?> Report — <?= htmlspecialchars($rpt['period_label']) ?></p>
                    <p class="text-xs text-gray-400"><?= (new DateTime($rpt['generated_at']))->format('d M Y H:i') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Info card -->
    <div class="mt-4 bg-violet-50 border border-violet-100 rounded-xl p-4">
        <p class="text-xs font-semibold text-violet-700 mb-2">Cara Generate Report Manual</p>
        <div class="space-y-1.5">
            <?php foreach ([
                'AI Insights'      => 'php cron/ai_insights.php',
                'Daily Briefing'   => 'php cron/ai_daily_briefing.php',
                'SLA Check'        => 'php cron/sla_check.php',
                'Workload Snapshot' => 'php cron/workload_snapshot.php',
                'KPI Calculator'   => 'php cron/kpi_calculator.php',
            ] as $label => $cmd): ?>
            <div class="flex items-center gap-2">
                <span class="text-xs text-violet-600 w-36"><?= $label ?></span>
                <code class="text-xs bg-white border border-violet-100 px-2 py-0.5 rounded text-gray-700 flex-1"><?= $cmd ?></code>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
