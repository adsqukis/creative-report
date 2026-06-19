<?php
class RequestController {
    private array $validStatuses = [
        'draft','waiting_queue','assigned','in_progress','revision',
        'ready_review','approved','completed','cancelled','rejected',
    ];
    private array $assetTypes = [
        'image','video','carousel','reels','story','thumbnail','banner','landing_page_asset',
    ];

    public function index(): void {
        Auth::require();
        $db     = Database::getInstance();
        $status = trim($_GET['status'] ?? '');
        $prio   = trim($_GET['priority'] ?? '');
        $pid    = (int)($_GET['product_id'] ?? 0);
        $did    = (int)($_GET['department_id'] ?? 0);
        $q      = trim($_GET['q'] ?? '');

        $where  = ['1=1'];
        $params = [];

        if (Auth::hasRole(['requester'])) {
            $where[]  = 'r.requester_id = ?';
            $params[] = Auth::id();
        } elseif (Auth::hasRole(['designer', 'video_editor'])) {
            $where[]  = 'a.assignee_id = ?';
            $params[] = Auth::id();
        }

        if ($status && in_array($status, $this->validStatuses, true)) {
            $where[]  = 'r.status = ?';
            $params[] = $status;
        }
        if ($prio && in_array($prio, ['critical','high','medium','low'], true)) {
            $where[]  = 'r.priority = ?';
            $params[] = $prio;
        }
        if ($pid > 0) {
            $where[]  = 'r.product_id = ?';
            $params[] = $pid;
        }
        if ($did > 0) {
            $where[]  = 'r.department_id = ?';
            $params[] = $did;
        }
        if ($q !== '') {
            $where[]  = '(r.title LIKE ? OR r.request_number LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $ws  = implode(' AND ', $where);

        $countSql = "SELECT COUNT(DISTINCT r.id) FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 WHERE $ws";
        $total    = $db->count($countSql, $params);
        $paginator = new Paginator($total, 25);

        $sql = "SELECT r.id, r.request_number, r.title, r.status, r.priority, r.deadline, r.priority_score, r.revision_count, r.asset_type, r.is_late, r.created_at, u.name as requester_name, p.name as product_name, GROUP_CONCAT(ua.name SEPARATOR ', ') as assigned_to FROM co_requests r LEFT JOIN co_users u ON r.requester_id = u.id LEFT JOIN co_products p ON r.product_id = p.id LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 LEFT JOIN co_users ua ON a.assignee_id = ua.id WHERE $ws GROUP BY r.id ORDER BY r.priority_score DESC, r.deadline ASC LIMIT " . $paginator->perPage . " OFFSET " . $paginator->offset;
        $requests = $db->query($sql, $params);

        $sqlP    = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
        $products = $db->query($sqlP, []);

        $sqlDept     = "SELECT id, name FROM co_departments ORDER BY name";
        $departments = $db->query($sqlDept, []);

        $pageTitle = 'Requests';
        $view      = 'requests/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function create(): void {
        Auth::require();
        $db          = Database::getInstance();
        $sqlP        = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
        $products    = $db->query($sqlP, []);
        $sqlC        = "SELECT id, name, product_id FROM co_campaigns WHERE status = 'active' ORDER BY name";
        $campaigns   = $db->query($sqlC, []);
        $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
        $departments = $db->query($sqlD, []);
        $assetTypes  = $this->assetTypes;
        $error       = null;
        $old         = [];

        $pageTitle = 'New Request';
        $view      = 'requests/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function store(): void {
        Auth::require();
        $db        = Database::getInstance();
        $title     = trim($_POST['title'] ?? '');
        $deadline  = trim($_POST['deadline'] ?? '');
        $assetType = trim($_POST['asset_type'] ?? '');
        $priority  = in_array($_POST['priority'] ?? '', ['critical','high','medium','low'], true) ? $_POST['priority'] : 'medium';
        $effort    = in_array($_POST['estimated_effort'] ?? '', ['small','medium','large'], true) ? $_POST['estimated_effort'] : 'medium';
        $error     = null;

        if ($title === '') {
            $error = 'Judul request wajib diisi.';
        } elseif ($deadline === '') {
            $error = 'Deadline wajib diisi.';
        } elseif (!in_array($assetType, $this->assetTypes, true)) {
            $error = 'Jenis asset tidak valid.';
        }

        if ($error !== null) {
            $sqlP        = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
            $products    = $db->query($sqlP, []);
            $sqlC        = "SELECT id, name, product_id FROM co_campaigns WHERE status = 'active' ORDER BY name";
            $campaigns   = $db->query($sqlC, []);
            $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
            $departments = $db->query($sqlD, []);
            $assetTypes  = $this->assetTypes;
            $old         = $_POST;
            $pageTitle   = 'New Request';
            $view        = 'requests/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $year   = (int)date('Y');
        $sqlN   = "SELECT COUNT(*) FROM co_requests WHERE YEAR(created_at) = ?";
        $count  = $db->count($sqlN, [$year]);
        $reqNum = 'CR-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $productId    = (int)($_POST['product_id'] ?? 0) ?: null;
        $campaignId   = (int)($_POST['campaign_id'] ?? 0) ?: null;
        $departmentId = (int)($_POST['department_id'] ?? 0) ?: null;

        $sql1 = "INSERT INTO co_requests (request_number, requester_id, department_id, product_id, campaign_id, title, objective, brief, copywriting, reference_link, asset_type, priority, estimated_effort, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
        $db->execute($sql1, [
            $reqNum, Auth::id(), $departmentId, $productId, $campaignId,
            $title,
            trim($_POST['objective'] ?? ''),
            trim($_POST['brief'] ?? ''),
            trim($_POST['copywriting'] ?? ''),
            trim($_POST['reference_link'] ?? ''),
            $assetType, $priority, $effort, $deadline,
        ]);
        $rid = Database::getLastId();

        $sql2 = "INSERT INTO co_sla_tracking (request_id, requested_at) VALUES (?, NOW())";
        $db->execute($sql2, [$rid]);

        $sql3 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, NULL, 'draft', ?, 'Request dibuat')";
        $db->execute($sql3, [$rid, Auth::id()]);

        PriorityEngine::updateRequest($rid);

        // Notifikasi ke semua creative_manager dan super_admin
        $managers = $db->query("SELECT id FROM co_users WHERE role IN ('super_admin','creative_manager') AND is_active = 1", []);
        foreach ($managers as $mgr) {
            NotificationService::create(
                (int)$mgr['id'],
                'new_request',
                'Request Baru: ' . $reqNum,
                $title . ' — ' . $assetType . ' / ' . ucfirst($priority),
                (int)$rid
            );
        }

        header('Location: ' . APP_URL . '/requests/' . $rid);
        exit;
    }

    public function show(string $id): void {
        Auth::require();
        $db  = Database::getInstance();
        $rid = (int)$id;

        $sql1 = "SELECT r.*, u.name as requester_name, p.name as product_name, COALESCE(p.business_importance,50) as business_importance, c.name as campaign_name, d.name as department_name FROM co_requests r LEFT JOIN co_users u ON r.requester_id = u.id LEFT JOIN co_products p ON r.product_id = p.id LEFT JOIN co_campaigns c ON r.campaign_id = c.id LEFT JOIN co_departments d ON r.department_id = d.id WHERE r.id = ? LIMIT 1";
        $request = $db->row($sql1, [$rid]);

        if (!$request) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }
        if (Auth::hasRole(['requester']) && (int)$request['requester_id'] !== (int)Auth::id()) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            return;
        }

        $sql2 = "SELECT l.*, u.name as changed_by_name FROM co_request_status_log l LEFT JOIN co_users u ON l.changed_by = u.id WHERE l.request_id = ? ORDER BY l.changed_at ASC";
        $statusLog = $db->query($sql2, [$rid]);

        $sql3 = "SELECT cm.*, u.name as user_name, u.role as user_role FROM co_request_comments cm LEFT JOIN co_users u ON cm.user_id = u.id WHERE cm.request_id = ? ORDER BY cm.created_at ASC";
        $comments = $db->query($sql3, [$rid]);

        $sql4 = "SELECT rv.*, u.name as requested_by_name, u2.name as responded_by_name FROM co_request_revisions rv LEFT JOIN co_users u ON rv.requested_by = u.id LEFT JOIN co_users u2 ON rv.responded_by = u2.id WHERE rv.request_id = ? ORDER BY rv.revision_number ASC";
        $revisions = $db->query($sql4, [$rid]);

        $sql5 = "SELECT a.*, u.name as assignee_name, u.role as assignee_role FROM co_request_assignments a LEFT JOIN co_users u ON a.assignee_id = u.id WHERE a.request_id = ? AND a.is_active = 1";
        $assignments = $db->query($sql5, [$rid]);

        $sql6 = "SELECT f.*, u.name as uploaded_by_name FROM co_request_files f LEFT JOIN co_users u ON f.uploaded_by = u.id WHERE f.request_id = ? ORDER BY f.uploaded_at DESC";
        $files = $db->query($sql6, [$rid]);

        $sla = $db->row("SELECT * FROM co_sla_tracking WHERE request_id = ? LIMIT 1", [$rid]);

        $sqlDs    = "SELECT id, name, role FROM co_users WHERE role IN ('designer','video_editor') AND is_active = 1 ORDER BY name";
        $designers = $db->query($sqlDs, []);

        $pageTitle = $request['request_number'];
        $view      = 'requests/show';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function updateStatus(string $id): void {
        Auth::require();
        $db        = Database::getInstance();
        $rid       = (int)$id;
        $newStatus = trim($_POST['status'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        // ── Role check: hanya super_admin & creative_manager boleh ubah status ──
        // (designer/video_editor tetap bisa via tombol cepat mereka,
        //  tapi dropdown "Change Status" di bawah ini khusus untuk dua role ini)
        if (!Auth::hasRole(['super_admin', 'creative_manager'])) {
            // Izinkan designer/video_editor hanya untuk perubahan status terbatas
            // yang sudah ada (tombol Start Working & Submit Review).
            // Jika mereka POST ke endpoint ini dengan status lain, tolak.
            $allowedForCreative = ['in_progress', 'ready_review'];
            if (!in_array($newStatus, $allowedForCreative, true) ||
                !Auth::hasRole(['designer', 'video_editor'])) {
                $_SESSION['status_error'] = 'Anda tidak memiliki izin untuk mengubah status request.';
                header('Location: ' . APP_URL . '/requests/' . $rid);
                exit;
            }
        }

        if (!in_array($newStatus, $this->validStatuses, true)) {
            header('Location: ' . APP_URL . '/requests/' . $rid);
            exit;
        }

        $sql1    = "SELECT status, requester_id FROM co_requests WHERE id = ? LIMIT 1";
        $request = $db->row($sql1, [$rid]);
        if (!$request) {
            header('Location: ' . APP_URL . '/requests');
            exit;
        }
        if (Auth::hasRole(['requester']) && (int)$request['requester_id'] !== (int)Auth::id()) {
            header('Location: ' . APP_URL . '/requests');
            exit;
        }

        $oldStatus = $request['status'];

        // ── Update status utama ──
        $sql2 = "UPDATE co_requests SET status = ?, updated_at = NOW() WHERE id = ?";
        $db->execute($sql2, [$newStatus, $rid]);

        // ── completed_at: set saat completed, null saat revert dari completed ──
        if ($newStatus === 'completed') {
            $sqlCompleted = "UPDATE co_requests SET completed_at = NOW() WHERE id = ? AND completed_at IS NULL";
            $db->execute($sqlCompleted, [$rid]);
        } elseif ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $sqlRevert = "UPDATE co_requests SET completed_at = NULL WHERE id = ?";
            $db->execute($sqlRevert, [$rid]);
        }

        // ── Activity log (status log) ──
        $sql3 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, ?, ?, ?, ?)";
        $db->execute($sql3, [$rid, $oldStatus, $newStatus, Auth::id(), $notes]);

        // ── SLA tracking ──
        if ($newStatus === 'in_progress') {
            $sql4 = "UPDATE co_sla_tracking SET started_at = COALESCE(started_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);
        } elseif ($newStatus === 'ready_review') {
            $sql4 = "UPDATE co_sla_tracking SET submitted_review_at = COALESCE(submitted_review_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);
        } elseif ($newStatus === 'approved') {
            $sql4 = "UPDATE co_sla_tracking SET approved_at = COALESCE(approved_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);
        } elseif ($newStatus === 'completed') {
            $sql4 = "UPDATE co_sla_tracking SET completed_at = COALESCE(completed_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);
        } elseif ($newStatus === 'waiting_queue') {
            $sql4 = "UPDATE co_sla_tracking SET requested_at = COALESCE(requested_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);
        }

        PriorityEngine::updateRequest($rid);

        // Flash message sukses
        $statusLabel = ucwords(str_replace('_', ' ', $newStatus));
        $_SESSION['status_success'] = "Status berhasil diubah ke \"{$statusLabel}\".";

        // Notifikasi berdasarkan status baru
        $reqInfo = $db->row("SELECT request_number, title, requester_id FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
        if ($reqInfo) {
            $notifTitle = $reqInfo['request_number'] . ' → ' . ucwords(str_replace('_', ' ', $newStatus));
            $notifMsg   = $reqInfo['title'];

            if (in_array($newStatus, ['completed', 'approved', 'cancelled'], true)) {
                // Notify requester
                NotificationService::create((int)$reqInfo['requester_id'], 'status_change', $notifTitle, $notifMsg, $rid);
            }
            if ($newStatus === 'waiting_queue') {
                // Notify semua manager ada request masuk antrian
                $managers = $db->query("SELECT id FROM co_users WHERE role IN ('super_admin','creative_manager') AND is_active = 1", []);
                foreach ($managers as $mgr) {
                    NotificationService::create((int)$mgr['id'], 'status_change', $notifTitle, $notifMsg, $rid);
                }
            }
        }

        header('Location: ' . APP_URL . '/requests/' . $rid);
        exit;
    }

    public function assign(string $id): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db           = Database::getInstance();
        $rid          = (int)$id;
        $assigneeId   = (int)($_POST['assignee_id'] ?? 0);
        $assigneeRole = trim($_POST['assignee_role'] ?? '');

        if ($assigneeId < 1 || !in_array($assigneeRole, ['designer','video_editor'], true)) {
            header('Location: ' . APP_URL . '/requests/' . $rid);
            exit;
        }

        $sql1 = "UPDATE co_request_assignments SET is_active = 0 WHERE request_id = ? AND assignee_role = ?";
        $db->execute($sql1, [$rid, $assigneeRole]);

        $sql2 = "INSERT INTO co_request_assignments (request_id, assignee_id, assignee_role, assigned_by) VALUES (?, ?, ?, ?)";
        $db->execute($sql2, [$rid, $assigneeId, $assigneeRole, Auth::id()]);

        $sql3    = "SELECT status FROM co_requests WHERE id = ? LIMIT 1";
        $req     = $db->row($sql3, [$rid]);
        $oldSt   = $req['status'] ?? 'draft';
        if (in_array($oldSt, ['draft','waiting_queue'], true)) {
            $sql4 = "UPDATE co_requests SET status = 'assigned', updated_at = NOW() WHERE id = ?";
            $db->execute($sql4, [$rid]);
            $sql5 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, ?, 'assigned', ?, 'Assigned by manager')";
            $db->execute($sql5, [$rid, $oldSt, Auth::id()]);
        }

        $sql6 = "UPDATE co_sla_tracking SET assigned_at = COALESCE(assigned_at, NOW()) WHERE request_id = ?";
        $db->execute($sql6, [$rid]);

        // Notifikasi ke designer/video editor yang baru di-assign
        $reqInfo = $db->row("SELECT request_number, title FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
        if ($reqInfo) {
            NotificationService::create(
                $assigneeId,
                'assigned',
                'Tugas Baru: ' . $reqInfo['request_number'],
                $reqInfo['title'] . ' telah di-assign kepada Anda.',
                $rid
            );
        }

        PriorityEngine::updateRequest($rid);
        header('Location: ' . APP_URL . '/requests/' . $rid);
        exit;
    }

    public function addComment(string $id): void {
        Auth::require();
        $db       = Database::getInstance();
        $rid      = (int)$id;
        $comment  = trim($_POST['comment'] ?? '');
        $internal = Auth::hasRole(['designer','video_editor','creative_manager','super_admin']) ? (int)($_POST['is_internal'] ?? 0) : 0;

        if ($comment !== '') {
            $sql = "INSERT INTO co_request_comments (request_id, user_id, comment, is_internal) VALUES (?, ?, ?, ?)";
            $db->execute($sql, [$rid, Auth::id(), $comment, $internal]);

            // Notifikasi: jika bukan internal, beritahu pihak lain
            if (!$internal) {
                $req = $db->row("SELECT request_number, title, requester_id FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
                if ($req) {
                    $commenter = $db->row("SELECT name FROM co_users WHERE id = ? LIMIT 1", [Auth::id()]);
                    $senderName = $commenter['name'] ?? 'Seseorang';
                    $msgPreview = mb_strlen($comment) > 80 ? mb_substr($comment, 0, 80) . '...' : $comment;
                    $notifTitle = 'Komentar baru: ' . $req['request_number'];
                    $notifMsg   = $senderName . ': ' . $msgPreview;

                    // Jika pengirim adalah requester → notify manager & assignee
                    if (Auth::hasRole(['requester'])) {
                        $managers = $db->query("SELECT id FROM co_users WHERE role IN ('super_admin','creative_manager') AND is_active = 1", []);
                        foreach ($managers as $mgr) {
                            if ((int)$mgr['id'] !== (int)Auth::id()) {
                                NotificationService::create((int)$mgr['id'], 'comment', $notifTitle, $notifMsg, $rid);
                            }
                        }
                        $assignees = $db->query("SELECT assignee_id FROM co_request_assignments WHERE request_id = ? AND is_active = 1", [$rid]);
                        foreach ($assignees as $a) {
                            NotificationService::create((int)$a['assignee_id'], 'comment', $notifTitle, $notifMsg, $rid);
                        }
                    } else {
                        // Jika pengirim adalah tim kreatif → notify requester
                        if ((int)$req['requester_id'] !== (int)Auth::id()) {
                            NotificationService::create((int)$req['requester_id'], 'comment', $notifTitle, $notifMsg, $rid);
                        }
                    }
                }
            }
        }

        header('Location: ' . APP_URL . '/requests/' . $rid . '#comments');
        exit;
    }

    public function addRevision(string $id): void {
        Auth::require();
        $db      = Database::getInstance();
        $rid     = (int)$id;
        $comment = trim($_POST['revision_comment'] ?? '');

        if ($comment === '') {
            header('Location: ' . APP_URL . '/requests/' . $rid);
            exit;
        }

        $sql1 = "SELECT revision_count, status FROM co_requests WHERE id = ? LIMIT 1";
        $req  = $db->row($sql1, [$rid]);
        if (!$req) {
            header('Location: ' . APP_URL . '/requests');
            exit;
        }

        $revNum = (int)$req['revision_count'] + 1;
        $sql2   = "INSERT INTO co_request_revisions (request_id, revision_number, requester_comment, requested_by, status) VALUES (?, ?, ?, ?, 'pending')";
        $db->execute($sql2, [$rid, $revNum, $comment, Auth::id()]);

        $sql3 = "UPDATE co_requests SET revision_count = ?, status = 'revision', updated_at = NOW() WHERE id = ?";
        $db->execute($sql3, [$revNum, $rid]);

        $sql4 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, ?, 'revision', ?, ?)";
        $db->execute($sql4, [$rid, $req['status'], Auth::id(), 'Revision #' . $revNum]);

        PriorityEngine::updateRequest($rid);

        // Notifikasi ke assignee aktif bahwa ada permintaan revisi
        $reqInfo   = $db->row("SELECT request_number, title FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
        $assignees = $db->query("SELECT assignee_id FROM co_request_assignments WHERE request_id = ? AND is_active = 1", [$rid]);
        if ($reqInfo) {
            foreach ($assignees as $a) {
                NotificationService::create(
                    (int)$a['assignee_id'],
                    'revision',
                    'Permintaan Revisi #' . $revNum . ': ' . $reqInfo['request_number'],
                    $reqInfo['title'] . ' — ' . mb_substr($comment, 0, 100),
                    $rid
                );
            }
        }

        header('Location: ' . APP_URL . '/requests/' . $rid . '#revisions');
        exit;
    }

    public function edit(string $id): void {
        Auth::requireRole(['super_admin', 'creative_manager', 'requester']);
        $db      = Database::getInstance();
        $rid     = (int)$id;
        $sql1    = "SELECT * FROM co_requests WHERE id = ? LIMIT 1";
        $request = $db->row($sql1, [$rid]);

        if (!$request) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }
        if (Auth::hasRole(['requester']) && ((int)$request['requester_id'] !== (int)Auth::id() || $request['status'] !== 'draft')) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            return;
        }

        $sqlP        = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
        $products    = $db->query($sqlP, []);
        $sqlC        = "SELECT id, name, product_id FROM co_campaigns WHERE status = 'active' ORDER BY name";
        $campaigns   = $db->query($sqlC, []);
        $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
        $departments = $db->query($sqlD, []);
        $assetTypes  = $this->assetTypes;
        $old         = $request;
        $error       = null;

        $pageTitle = 'Edit ' . $request['request_number'];
        $view      = 'requests/edit';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(string $id): void {
        Auth::requireRole(['super_admin', 'creative_manager', 'requester']);
        $db      = Database::getInstance();
        $rid     = (int)$id;
        $sql1    = "SELECT status, requester_id FROM co_requests WHERE id = ? LIMIT 1";
        $request = $db->row($sql1, [$rid]);

        if (!$request) {
            header('Location: ' . APP_URL . '/requests');
            exit;
        }
        if (Auth::hasRole(['requester']) && ((int)$request['requester_id'] !== (int)Auth::id() || $request['status'] !== 'draft')) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/403.php';
            return;
        }

        $title     = trim($_POST['title'] ?? '');
        $deadline  = trim($_POST['deadline'] ?? '');
        $assetType = trim($_POST['asset_type'] ?? '');
        $priority  = in_array($_POST['priority'] ?? '', ['critical','high','medium','low'], true) ? $_POST['priority'] : 'medium';
        $effort    = in_array($_POST['estimated_effort'] ?? '', ['small','medium','large'], true) ? $_POST['estimated_effort'] : 'medium';

        if ($title === '' || $deadline === '' || !in_array($assetType, $this->assetTypes, true)) {
            header('Location: ' . APP_URL . '/requests/' . $rid . '/edit');
            exit;
        }

        $productId    = (int)($_POST['product_id'] ?? 0) ?: null;
        $campaignId   = (int)($_POST['campaign_id'] ?? 0) ?: null;
        $departmentId = (int)($_POST['department_id'] ?? 0) ?: null;

        $sql2 = "UPDATE co_requests SET title = ?, objective = ?, brief = ?, copywriting = ?, reference_link = ?, asset_type = ?, priority = ?, estimated_effort = ?, deadline = ?, product_id = ?, campaign_id = ?, department_id = ?, updated_at = NOW() WHERE id = ?";
        $db->execute($sql2, [
            $title,
            trim($_POST['objective'] ?? ''),
            trim($_POST['brief'] ?? ''),
            trim($_POST['copywriting'] ?? ''),
            trim($_POST['reference_link'] ?? ''),
            $assetType, $priority, $effort, $deadline,
            $productId, $campaignId, $departmentId, $rid,
        ]);

        PriorityEngine::updateRequest($rid);
        header('Location: ' . APP_URL . '/requests/' . $rid);
        exit;
    }
}