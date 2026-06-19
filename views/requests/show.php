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
    'critical' => 'bg-red-100 text-red-700',
    'high'     => 'bg-orange-100 text-orange-700',
    'medium'   => 'bg-yellow-100 text-yellow-700',
    'low'      => 'bg-gray-100 text-gray-500',
];
$sc  = $statusColors[$request['status']] ?? 'bg-gray-100 text-gray-600';
$pc  = $prioColors[$request['priority']] ?? 'bg-gray-100 text-gray-600';
$uid = (int)Auth::id();
$role = Auth::role();
?>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['status_success'])): ?>
<div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-2.5 mb-4 text-xs text-green-700">
    <svg class="w-3.5 h-3.5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <?= htmlspecialchars($_SESSION['status_success']) ?>
</div>
<?php unset($_SESSION['status_success']); endif; ?>

<?php if (!empty($_SESSION['status_error'])): ?>
<div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5 mb-4 text-xs text-red-700">
    <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    <?= htmlspecialchars($_SESSION['status_error']) ?>
</div>
<?php unset($_SESSION['status_error']); endif; ?>

<!-- Header -->
<div class="flex flex-wrap items-start gap-4 mb-5">
    <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-1.5">
            <span class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($request['request_number']) ?></span>
            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $sc ?>">
                <?= ucwords(str_replace('_',' ',$request['status'])) ?>
            </span>
            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium <?= $pc ?>">
                <?= ucfirst($request['priority']) ?>
            </span>
            <?php if ($request['is_late']): ?>
            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700">Overdue</span>
            <?php endif; ?>
        </div>
        <h2 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($request['title']) ?></h2>
    </div>

    <!-- Quick status actions -->
    <?php if (in_array($role, ['super_admin','creative_manager'], true)): ?>
    <div class="flex items-center gap-2 flex-wrap">

        <!-- ── Change Status Dropdown ── -->
        <?php
        $changeableStatuses = [
            'draft'         => 'Draft',
            'waiting_queue' => 'Waiting Queue',
            'assigned'      => 'Assigned',
            'in_progress'   => 'In Progress',
            'ready_review'  => 'Ready Review',
            'approved'      => 'Approved',
            'completed'     => 'Completed',
            'cancelled'     => 'Cancelled',
        ];
        ?>
        <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/status"
              class="flex items-center gap-1.5">
            <?= Csrf::input() ?>
            <select name="status"
                    class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-violet-400 cursor-pointer">
                <?php foreach ($changeableStatuses as $val => $label): ?>
                <option value="<?= $val ?>" <?= $request['status'] === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                    class="bg-violet-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-violet-700 whitespace-nowrap">
                Update Status
            </button>
        </form>

        <a href="<?= APP_URL ?>/requests/<?= $request['id'] ?>/edit"
           class="border border-gray-200 text-gray-600 text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-gray-50">
            Edit
        </a>
    </div>
    <?php endif; ?>

    <?php if (in_array($role, ['designer','video_editor'], true)): ?>
    <div class="flex items-center gap-2">
        <?php if ($request['status'] === 'assigned'): ?>
        <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/status">
            <?= Csrf::input() ?>
            <input type="hidden" name="status" value="in_progress">
            <button class="bg-indigo-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-700">Start Working</button>
        </form>
        <?php elseif (in_array($request['status'], ['in_progress','revision'], true)): ?>
        <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/status">
            <?= Csrf::input() ?>
            <input type="hidden" name="status" value="ready_review">
            <button class="bg-purple-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-purple-700">Submit Review</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($request['status'] === 'draft' && (int)$request['requester_id'] === $uid): ?>
    <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/status">
        <?= Csrf::input() ?>
        <input type="hidden" name="status" value="waiting_queue">
        <button class="bg-amber-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-600">Submit to Queue</button>
    </form>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Left: main content -->
    <div class="col-span-2 space-y-4">
        <!-- Details -->
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Detail</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><span class="text-gray-400 text-xs">Asset Type</span><br><span class="font-medium text-gray-800"><?= ucwords(str_replace('_',' ',$request['asset_type'])) ?></span></div>
                <div><span class="text-gray-400 text-xs">Effort</span><br><span class="font-medium text-gray-800"><?= ucfirst($request['estimated_effort']) ?></span></div>
                <div><span class="text-gray-400 text-xs">Deadline</span><br>
                    <?php $dl = new DateTime($request['deadline']); $past = $dl < new DateTime(); ?>
                    <span class="font-medium <?= $past ? 'text-red-600' : 'text-gray-800' ?>"><?= $dl->format('d M Y') ?></span>
                </div>
                <div><span class="text-gray-400 text-xs">Priority Score</span><br><span class="font-bold text-gray-800"><?= $request['priority_score'] ?>/100</span></div>
                <?php if ($request['product_name']): ?>
                <div><span class="text-gray-400 text-xs">Product</span><br><span class="font-medium text-gray-800"><?= htmlspecialchars($request['product_name']) ?></span></div>
                <?php endif; ?>
                <?php if ($request['campaign_name']): ?>
                <div><span class="text-gray-400 text-xs">Campaign</span><br><span class="font-medium text-gray-800"><?= htmlspecialchars($request['campaign_name']) ?></span></div>
                <?php endif; ?>
                <div><span class="text-gray-400 text-xs">Requester</span><br><span class="font-medium text-gray-800"><?= htmlspecialchars($request['requester_name']) ?></span></div>
                <div><span class="text-gray-400 text-xs">Revisi</span><br><span class="font-medium <?= (int)$request['revision_count'] > 2 ? 'text-orange-600' : 'text-gray-800' ?>"><?= $request['revision_count'] ?>x</span></div>
            </div>

            <?php if ($request['objective']): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Objective</p>
                <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($request['objective'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($request['brief']): ?>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Brief</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($request['brief']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($request['copywriting']): ?>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Copywriting</p>
                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= htmlspecialchars($request['copywriting']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($request['reference_link']): ?>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Reference</p>
                <a href="<?= htmlspecialchars($request['reference_link']) ?>" target="_blank" class="text-sm text-violet-600 hover:underline break-all"><?= htmlspecialchars($request['reference_link']) ?></a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Files -->
        <div class="bg-white border border-gray-200 rounded-xl p-5" id="files">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Files</h3>
            <?php if (!empty($_SESSION['upload_error'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3 text-xs text-red-700"><?= htmlspecialchars($_SESSION['upload_error']) ?></div>
            <?php unset($_SESSION['upload_error']); endif; ?>

            <?php if (!empty($_SESSION['upload_success'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3 text-xs text-green-700 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?= htmlspecialchars($_SESSION['upload_success']) ?>
            </div>
            <?php unset($_SESSION['upload_success']); endif; ?>

            <?php if (!empty($files)): ?>
            <div class="space-y-2 mb-4">
                <?php foreach ($files as $f): ?>
                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-3">
                    <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center text-violet-700 text-xs font-bold shrink-0">
                        <?= $f['gdrive_url'] ? 'GD' : strtoupper(pathinfo($f['filename'] ?? 'f', PATHINFO_EXTENSION)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-800 truncate"><?= htmlspecialchars($f['filename'] ?? 'Google Drive') ?></p>
                        <p class="text-xs text-gray-400"><?= ucfirst($f['file_type']) ?> &middot; Rev#<?= $f['revision_number'] ?> &middot; <?= htmlspecialchars($f['uploaded_by_name'] ?? '') ?></p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <?php if ($f['gdrive_url']): ?>
                        <a href="<?= htmlspecialchars($f['gdrive_url']) ?>" target="_blank" class="text-xs text-violet-600 hover:underline">Buka GDrive</a>
                        <?php elseif ($f['filepath']): ?>
                        <a href="<?= APP_URL ?>/files/<?= (int)$f['id'] ?>/download" class="text-xs text-violet-600 hover:underline">Download</a>
                        <?php endif; ?>
                        <?php if (in_array($role, ['super_admin','creative_manager','designer','video_editor'], true)): ?>
                        <form method="POST" action="<?= APP_URL ?>/files/<?= (int)$f['id'] ?>/delete" class="inline">
                            <?= Csrf::input() ?>
                            <button class="text-xs text-red-400 hover:text-red-600" onclick="return confirm('Hapus file ini?')">Hapus</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-xs text-gray-400 mb-4">Belum ada file diunggah.</p>
            <?php endif; ?>

            <?php if (in_array($role, ['designer','video_editor','creative_manager','super_admin'], true)): ?>
            <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/files" enctype="multipart/form-data" class="space-y-3 pt-3 border-t border-gray-100">
                <?= Csrf::input() ?>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe File</label>
                        <select name="file_type" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-violet-400">
                            <option value="draft">Draft</option>
                            <option value="revision">Revision</option>
                            <option value="final">Final</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. Revisi</label>
                        <input type="number" name="revision_number" value="<?= $request['revision_count'] ?>" min="0"
                            class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-violet-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Upload File (max 50MB)</label>
                    <input type="file" name="upload_file" accept="image/*,video/mp4,application/pdf,.zip,.pptx,.docx"
                        class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">atau Google Drive URL</label>
                    <input type="url" name="gdrive_url" placeholder="https://drive.google.com/..."
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-violet-400">
                </div>
                <button type="submit" class="w-full bg-violet-600 text-white text-xs font-semibold py-2 rounded-lg hover:bg-violet-700">Upload File</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Comments -->
        <div class="bg-white border border-gray-200 rounded-xl p-5" id="comments">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Comments</h3>

            <?php if (empty($comments)): ?>
            <p class="text-sm text-gray-400 mb-4">Belum ada komentar.</p>
            <?php else: ?>
            <div class="space-y-3 mb-4">
                <?php foreach ($comments as $cm): ?>
                <?php if ($cm['is_internal'] && !in_array($role, ['super_admin','creative_manager','designer','video_editor'], true)) continue; ?>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-violet-100 flex items-center justify-center text-violet-700 font-semibold text-xs shrink-0 mt-0.5">
                        <?= strtoupper(substr($cm['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-xs font-semibold text-gray-800"><?= htmlspecialchars($cm['user_name'] ?? '') ?></span>
                            <?php if ($cm['is_internal']): ?>
                            <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Internal</span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-400"><?= (new DateTime($cm['created_at']))->format('d M H:i') ?></span>
                        </div>
                        <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($cm['comment'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/comment" class="space-y-2">
                <?= Csrf::input() ?>
                <textarea name="comment" rows="2" placeholder="Tulis komentar..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"></textarea>
                <div class="flex items-center justify-between">
                    <?php if (in_array($role, ['super_admin','creative_manager','designer','video_editor'], true)): ?>
                    <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                        <input type="checkbox" name="is_internal" value="1" class="rounded">
                        Internal note (tidak terlihat requester)
                    </label>
                    <?php else: ?><span></span><?php endif; ?>
                    <button type="submit" class="bg-gray-800 text-white text-xs font-semibold px-4 py-1.5 rounded-lg hover:bg-gray-900">Kirim</button>
                </div>
            </form>
        </div>

        <!-- Revisions -->
        <?php if (!empty($revisions)): ?>
        <div class="bg-white border border-gray-200 rounded-xl p-5" id="revisions">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Revision History</h3>
            <div class="space-y-3">
                <?php foreach ($revisions as $rv): ?>
                <div class="border border-orange-100 bg-orange-50 rounded-lg p-3.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xs font-bold text-orange-700">Revision #<?= $rv['revision_number'] ?></span>
                        <span class="text-xs text-gray-400"><?= (new DateTime($rv['requested_at']))->format('d M Y H:i') ?></span>
                        <span class="ml-auto text-xs px-1.5 py-0.5 rounded <?= $rv['status'] === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' ?>">
                            <?= ucfirst($rv['status']) ?>
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 mb-1"><span class="font-medium">Requester:</span> <?= nl2br(htmlspecialchars($rv['requester_comment'] ?? '')) ?></p>
                    <?php if ($rv['designer_response']): ?>
                    <p class="text-sm text-gray-600 mt-2 pt-2 border-t border-orange-200"><span class="font-medium">Response:</span> <?= nl2br(htmlspecialchars($rv['designer_response'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add revision (requester or manager on ready_review) -->
        <?php if (in_array($request['status'], ['ready_review'], true) && in_array($role, ['requester','super_admin','creative_manager'], true)): ?>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Request Revision</h3>
            <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/revision" class="space-y-2">
                <?= Csrf::input() ?>
                <textarea name="revision_comment" rows="3" placeholder="Jelaskan detail revisi yang dibutuhkan..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none"></textarea>
                <button type="submit" class="bg-orange-500 text-white text-xs font-semibold px-4 py-1.5 rounded-lg hover:bg-orange-600">Request Revision</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: sidebar -->
    <div class="space-y-4">
        <!-- Assignments -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Assigned To</h3>
            <?php if (empty($assignments)): ?>
            <p class="text-xs text-gray-400">Belum ada assignment.</p>
            <?php else: ?>
            <div class="space-y-2 mb-3">
                <?php foreach ($assignments as $a): ?>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold shrink-0">
                        <?= strtoupper(substr($a['assignee_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-800"><?= htmlspecialchars($a['assignee_name'] ?? '') ?></p>
                        <p class="text-xs text-gray-400 capitalize"><?= str_replace('_',' ',$a['assignee_role']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (in_array($role, ['super_admin','creative_manager'], true)): ?>
            <form method="POST" action="<?= APP_URL ?>/requests/<?= $request['id'] ?>/assign" class="space-y-2 pt-3 border-t border-gray-100">
                <?= Csrf::input() ?>
                <select name="assignee_id" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-violet-400">
                    <option value="">-- Pilih orang --</option>
                    <?php foreach ($designers as $ds): ?>
                    <option value="<?= $ds['id'] ?>"><?= htmlspecialchars($ds['name']) ?> (<?= str_replace('_',' ',$ds['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select name="assignee_role" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-violet-400">
                    <option value="designer">Designer</option>
                    <option value="video_editor">Video Editor</option>
                </select>
                <button type="submit" class="w-full bg-violet-600 text-white text-xs font-semibold py-1.5 rounded-lg hover:bg-violet-700">Assign</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- SLA -->
        <?php if ($sla): ?>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">SLA Timeline</h3>
            <?php
            $slaSteps = [
                'Requested'     => $sla['requested_at'],
                'Assigned'      => $sla['assigned_at'],
                'Started'       => $sla['started_at'],
                'Submitted'     => $sla['submitted_review_at'],
                'Approved'      => $sla['approved_at'],
                'Completed'     => $sla['completed_at'],
            ];
            ?>
            <div class="space-y-1.5">
                <?php foreach ($slaSteps as $label => $ts): ?>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full shrink-0 <?= $ts ? 'bg-green-400' : 'bg-gray-200' ?>"></div>
                    <span class="text-xs text-gray-500"><?= $label ?></span>
                    <span class="text-xs text-gray-400 ml-auto">
                        <?= $ts ? (new DateTime($ts))->format('d/m H:i') : '—' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($sla['is_late']): ?>
            <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-red-600 font-semibold">Overdue <?= $sla['late_by_hours'] ?> jam</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Activity log -->
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Activity Log</h3>
            <?php if (empty($statusLog)): ?>
            <p class="text-xs text-gray-400">Belum ada aktivitas.</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach (array_reverse($statusLog) as $log): ?>
                <div class="text-xs">
                    <span class="font-medium text-gray-700"><?= htmlspecialchars($log['changed_by_name'] ?? 'System') ?></span>
                    <span class="text-gray-400"> ubah ke </span>
                    <span class="font-medium text-gray-700"><?= ucwords(str_replace('_',' ',$log['to_status'])) ?></span>
                    <br>
                    <span class="text-gray-400"><?= (new DateTime($log['changed_at']))->format('d M H:i') ?></span>
                    <?php if ($log['notes']): ?>
                    <br><span class="text-gray-500 italic"><?= htmlspecialchars($log['notes']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>