<?php
$statusColors = [
    'draft'         => 'bg-gray-100 text-gray-600',
    'waiting_queue' => 'bg-amber-100 text-amber-700',
    'assigned'      => 'bg-blue-100 text-blue-700',
    'in_progress'   => 'bg-indigo-100 text-indigo-700',
    'revision'      => 'bg-orange-100 text-orange-700',
    'ready_review'  => 'bg-purple-100 text-purple-700',
    'approved'      => 'bg-teal-100 text-teal-700',
    'completed'     => 'bg-green-100 text-green-700',
    'cancelled'     => 'bg-gray-100 text-gray-400',
    'rejected'      => 'bg-red-100 text-red-700',
];
$prioColors = [
    'critical' => 'bg-red-100 text-red-700 font-semibold',
    'high'     => 'bg-orange-100 text-orange-700',
    'medium'   => 'bg-yellow-100 text-yellow-700',
    'low'      => 'bg-gray-100 text-gray-500',
];
$curStatus = $_GET['status'] ?? '';
$curPrio   = $_GET['priority'] ?? '';
$curPid    = (int)($_GET['product_id'] ?? 0);
$curDid    = (int)($_GET['department_id'] ?? 0);
$curQ      = $_GET['q'] ?? '';
?>

<!-- Toolbar -->
<div class="mb-4 space-y-2">
    <form method="GET" action="<?= APP_URL ?>/requests">
        <!-- Search row -->
        <div class="flex gap-2 mb-2">
            <input type="text" name="q" value="<?= htmlspecialchars($curQ) ?>"
                placeholder="Cari judul atau nomor..."
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-0 focus:outline-none focus:ring-2 focus:ring-violet-400">
            <button type="submit" class="bg-violet-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-violet-700 shrink-0">Filter</button>
            <a href="<?= APP_URL ?>/requests/create"
                class="inline-flex items-center gap-1 bg-violet-600 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-violet-700 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">New</span>
            </a>
        </div>
        <!-- Filters row -->
        <div class="flex flex-wrap gap-2">
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 flex-1 min-w-[140px]">
                <option value="">Semua Status</option>
                <?php foreach (['draft','waiting_queue','assigned','in_progress','revision','ready_review','approved','completed','cancelled','rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $curStatus === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="priority" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 flex-1 min-w-[130px]">
                <option value="">Semua Priority</option>
                <?php foreach (['critical','high','medium','low'] as $p): ?>
                <option value="<?= $p ?>" <?= $curPrio === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="product_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 flex-1 min-w-[130px]">
                <option value="0">Semua Produk</option>
                <?php foreach ($products as $prod): ?>
                <option value="<?= $prod['id'] ?>" <?= $curPid === (int)$prod['id'] ? 'selected' : '' ?>><?= htmlspecialchars($prod['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="department_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 flex-1 min-w-[140px]">
                <option value="0">Semua Departemen</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>" <?= $curDid === (int)$dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <?php if ($curStatus || $curPrio || $curPid || $curDid || $curQ): ?>
            <a href="<?= APP_URL ?>/requests" class="text-sm text-gray-400 hover:text-gray-600 px-2 py-2">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table / Cards -->
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <?php if (empty($requests)): ?>
    <div class="py-16 text-center text-sm text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Tidak ada request ditemukan.
    </div>
    <?php else: ?>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Score</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Request</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requester</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deadline</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($requests as $req): ?>
                <?php
                $dl   = new DateTime($req['deadline']);
                $now  = new DateTime();
                $diff = (int)$now->diff($dl)->days;
                $past = $dl < $now;
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-1.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                                <?= (int)$req['priority_score'] >= 70 ? 'bg-red-100 text-red-700' : ((int)$req['priority_score'] >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') ?>">
                                <?= $req['priority_score'] ?>
                            </div>
                            <?php if ($req['is_late']): ?>
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block" title="Overdue"></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="<?= APP_URL ?>/requests/<?= (int)$req['id'] ?>" class="font-medium text-gray-900 hover:text-violet-600 line-clamp-1 max-w-xs block">
                            <?= htmlspecialchars($req['title']) ?>
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($req['request_number']) ?> &middot; <?= htmlspecialchars(str_replace('_',' ',$req['asset_type'])) ?></p>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 text-xs"><?= htmlspecialchars($req['requester_name'] ?? '-') ?></td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucwords(str_replace('_',' ',$req['status'])) ?>
                        </span>
                        <?php if ((int)$req['revision_count'] > 0): ?>
                        <span class="ml-1 text-xs text-orange-500">R<?= $req['revision_count'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs <?= $prioColors[$req['priority']] ?? '' ?>">
                            <?= ucfirst($req['priority']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs <?= $past ? 'text-red-600 font-semibold' : ($diff <= 3 ? 'text-orange-600 font-medium' : 'text-gray-600') ?>">
                            <?= $dl->format('d M Y') ?>
                            <?php if ($past): ?><br><span class="text-red-500">Overdue</span>
                            <?php elseif ($diff <= 3): ?><br><span class="text-orange-500">H-<?= $diff ?></span><?php endif; ?>
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-xs text-gray-500"><?= htmlspecialchars($req['assigned_to'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden divide-y divide-gray-50">
        <?php foreach ($requests as $req): ?>
        <?php
        $dl   = new DateTime($req['deadline']);
        $now  = new DateTime();
        $diff = (int)$now->diff($dl)->days;
        $past = $dl < $now;
        $sc   = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-600';
        $pc   = $prioColors[$req['priority']] ?? 'bg-gray-100 text-gray-500';
        ?>
        <a href="<?= APP_URL ?>/requests/<?= (int)$req['id'] ?>" class="block px-4 py-3.5 hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-2 mb-1.5">
                <div class="w-7 h-7 rounded-md flex items-center justify-center text-xs font-bold shrink-0
                    <?= (int)$req['priority_score'] >= 70 ? 'bg-red-100 text-red-700' : ((int)$req['priority_score'] >= 40 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500') ?>">
                    <?= $req['priority_score'] ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 line-clamp-1"><?= htmlspecialchars($req['title']) ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars($req['request_number']) ?> · <?= htmlspecialchars(str_replace('_',' ',$req['asset_type'])) ?></p>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $sc ?> shrink-0">
                    <?= ucwords(str_replace('_',' ',$req['status'])) ?>
                </span>
            </div>
            <div class="flex items-center gap-2 flex-wrap pl-9">
                <span class="inline-flex px-1.5 py-0.5 rounded text-xs <?= $pc ?>"><?= ucfirst($req['priority']) ?></span>
                <?php if ($req['assigned_to']): ?>
                <span class="text-xs text-gray-400">→ <?= htmlspecialchars($req['assigned_to']) ?></span>
                <?php endif; ?>
                <span class="text-xs <?= $past ? 'text-red-600 font-semibold' : ($diff <= 3 ? 'text-orange-600' : 'text-gray-400') ?> ml-auto">
                    <?= $past ? 'Overdue · ' : ($diff <= 3 ? "H-{$diff} · " : '') ?><?= $dl->format('d M Y') ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="px-4 md:px-5 py-3 border-t border-gray-100 text-xs text-gray-400">
        <?php require APP_ROOT . '/views/components/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>