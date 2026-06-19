<?php
function scoreColor(float $score): string {
    if ($score >= 80) return 'text-green-700 bg-green-50';
    if ($score >= 60) return 'text-yellow-700 bg-yellow-50';
    return 'text-red-700 bg-red-50';
}
function scoreBar(float $score): string {
    if ($score >= 80) return 'bg-green-400';
    if ($score >= 60) return 'bg-yellow-400';
    return 'bg-red-400';
}
?>

<!-- Month selector -->
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-2">
        <form method="GET" action="<?= APP_URL ?>/analytics/scorecard">
            <select name="ym" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                <?php foreach ($months as $m): ?>
                <option value="<?= $m['year_month'] ?>" <?= ($m['year_month'] === ($ym ?? date('Y-m'))) ? 'selected' : '' ?>>
                    <?= date('F Y', strtotime($m['year_month'] . '-01')) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <p class="text-xs text-gray-400">KPI dihitung otomatis setiap malam pukul 23:55</p>
</div>

<?php if (empty($kpis)): ?>
<div class="bg-white border border-gray-200 rounded-xl p-12 text-center text-sm text-gray-400">
    Belum ada data KPI. Pastikan cron kpi_calculator.php sudah berjalan.
</div>
<?php else: ?>

<!-- Ranking cards -->
<div class="grid grid-cols-3 gap-3 mb-5">
    <?php foreach (array_slice($kpis, 0, 3) as $i => $kpi): ?>
    <div class="bg-white border <?= $i === 0 ? 'border-violet-200' : 'border-gray-200' ?> rounded-xl p-5 <?= $i === 0 ? 'ring-1 ring-violet-200' : '' ?>">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-full <?= $i === 0 ? 'bg-violet-100 text-violet-700' : 'bg-gray-100 text-gray-600' ?> flex items-center justify-center font-bold text-sm shrink-0">
                <?= $i + 1 ?>
            </div>
            <div>
                <p class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($kpi['name']) ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= str_replace('_',' ',$kpi['role']) ?></p>
            </div>
            <?php if ($i === 0): ?>
            <span class="ml-auto text-xs bg-violet-100 text-violet-700 font-semibold px-2 py-0.5 rounded-full">Top</span>
            <?php endif; ?>
        </div>
        <div class="space-y-2.5">
            <?php
            $scores = [
                'Creative Score' => (float)$kpi['creative_score'],
                'SLA'           => (float)$kpi['sla_score'],
                'Completion'    => (float)$kpi['completion_score'],
                'Revision'      => (float)$kpi['revision_score'],
            ];
            ?>
            <?php foreach ($scores as $label => $val): ?>
            <div>
                <div class="flex justify-between text-xs mb-0.5">
                    <span class="text-gray-500"><?= $label ?></span>
                    <span class="font-semibold <?= $val >= 80 ? 'text-green-700' : ($val >= 60 ? 'text-yellow-700' : 'text-red-700') ?>"><?= $val ?></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full <?= scoreBar($val) ?>" style="width: <?= min($val, 100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-2 gap-2 text-center text-xs text-gray-400">
            <div><span class="font-semibold text-gray-700 block text-sm"><?= $kpi['tasks_completed'] ?></span>Selesai</div>
            <div><span class="font-semibold text-gray-700 block text-sm"><?= $kpi['total_revisions'] ?></span>Total Rev</div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Full table -->
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Designer / Editor</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Creative</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SLA</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Completion</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Revision</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Selesai</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">On Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($kpis as $i => $kpi): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-xs font-bold text-gray-400"><?= $i + 1 ?></td>
                <td class="px-5 py-3">
                    <p class="font-medium text-sm text-gray-900"><?= htmlspecialchars($kpi['name']) ?></p>
                    <p class="text-xs text-gray-400 capitalize"><?= str_replace('_',' ',$kpi['role']) ?></p>
                </td>
                <?php foreach ([(float)$kpi['creative_score'], (float)$kpi['sla_score'], (float)$kpi['completion_score'], (float)$kpi['revision_score']] as $sc): ?>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold <?= scoreColor($sc) ?>"><?= $sc ?></span>
                </td>
                <?php endforeach; ?>
                <td class="px-4 py-3 text-center text-sm font-medium text-gray-700"><?= $kpi['tasks_completed'] ?></td>
                <td class="px-4 py-3 text-center text-sm font-medium <?= (int)$kpi['tasks_late'] > 0 ? 'text-red-600' : 'text-green-600' ?>"><?= $kpi['tasks_on_time'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
