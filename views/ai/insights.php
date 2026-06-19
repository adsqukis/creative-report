<?php
$sevColors = [
    'critical' => 'border-red-200 bg-red-50',
    'warning'  => 'border-orange-200 bg-orange-50',
    'info'     => 'border-gray-200 bg-white',
];
$sevBadge = [
    'critical' => 'bg-red-100 text-red-700 font-bold',
    'warning'  => 'bg-orange-100 text-orange-700 font-semibold',
    'info'     => 'bg-gray-100 text-gray-600',
];
$typeIcons = [
    'bottleneck'   => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    'performance'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'product'      => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    'priority_rec' => 'M13 10V3L4 14h7v7l9-11h-7z',
    'forecast'     => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
    'team_ranking' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
];
$typeLabels = [
    'bottleneck'   => 'Bottleneck',
    'performance'  => 'Performance',
    'product'      => 'Product',
    'priority_rec' => 'Priority',
    'forecast'     => 'Forecast',
    'team_ranking' => 'Team Ranking',
];
?>

<div class="flex items-center justify-between mb-4">
    <div>
        <p class="text-xs text-gray-400">
            <?= $liveCount ?> active insight &middot;
            Diperbarui oleh cron 2x sehari (10:00 &amp; 15:00) &middot;
            <?php $cfg = require APP_ROOT . '/config/ai.php'; ?>
            Provider: <span class="font-semibold"><?= htmlspecialchars($cfg['provider'] ?? 'null') ?></span>
            <?php if (($cfg['provider'] ?? 'null') === 'null' || empty($cfg['api_key']) || $cfg['api_key'] === 'sk-your-deepseek-api-key-here'): ?>
            <span class="text-orange-500">(NullProvider — isi API key di config/ai.php)</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (empty($byType)): ?>
<div class="bg-white border border-gray-200 rounded-xl p-12 text-center text-sm text-gray-400">
    <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
    Belum ada insight. Tunggu cron ai_insights.php berjalan, atau jalankan manual:<br>
    <code class="text-xs bg-gray-100 px-2 py-1 rounded mt-2 inline-block">php /path/to/creative-ops/cron/ai_insights.php</code>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php foreach ($byType as $type => $ins): ?>
    <?php $sev = $ins['severity'] ?? 'info'; ?>
    <div class="border rounded-xl p-5 <?= $sevColors[$sev] ?? 'bg-white border-gray-200' ?>">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $typeIcons[$type] ?? 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' ?>"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                    <span class="text-xs font-semibold text-gray-700"><?= htmlspecialchars($typeLabels[$type] ?? ucfirst($type)) ?></span>
                    <span class="text-xs px-1.5 py-0.5 rounded-full <?= $sevBadge[$sev] ?? 'bg-gray-100 text-gray-600' ?>"><?= ucfirst($sev) ?></span>
                    <?php if ($ins['is_deterministic']): ?>
                    <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">Deterministic</span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-400"><?= (new DateTime($ins['generated_at']))->format('d M H:i') ?></p>
            </div>
        </div>
        <p class="text-sm font-medium text-gray-800 mb-2"><?= htmlspecialchars($ins['insight_text']) ?></p>
        <?php if (!empty($ins['recommendation'])): ?>
        <div class="bg-white bg-opacity-60 rounded-lg p-3 border border-white">
            <p class="text-xs text-gray-500 font-semibold mb-0.5">Rekomendasi</p>
            <p class="text-xs text-gray-700"><?= htmlspecialchars($ins['recommendation']) ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>