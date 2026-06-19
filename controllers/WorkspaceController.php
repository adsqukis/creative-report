<?php
class WorkspaceController {
    public function index(): void {
        Auth::requireRole(['designer', 'video_editor']);
        $db  = Database::getInstance();
        $uid = Auth::id();

        $sql1 = "SELECT r.id, r.request_number, r.title, r.status, r.priority, r.deadline, r.priority_score, r.revision_count, r.is_late, r.asset_type, p.name as product_name FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 LEFT JOIN co_products p ON r.product_id = p.id WHERE a.assignee_id = ? AND r.status NOT IN ('completed','cancelled','rejected') ORDER BY r.priority_score DESC, r.deadline ASC";
        $myTasks = $db->query($sql1, [$uid]);

        $sql2 = "SELECT r.id, r.request_number, r.title, r.status, r.priority, r.deadline, r.revision_count, r.asset_type FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 WHERE a.assignee_id = ? AND r.status = 'revision' ORDER BY r.deadline ASC";
        $revisionTasks = $db->query($sql2, [$uid]);

        $totalActive   = 0;
        $totalOverdue  = 0;
        $today         = new DateTime('today');
        foreach ($myTasks as $t) {
            $totalActive++;
            if ((new DateTime($t['deadline'])) < $today) {
                $totalOverdue++;
            }
        }

        $pageTitle = 'My Workspace';
        $view      = 'workspace/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function startTask(string $id): void {
        Auth::requireRole(['designer', 'video_editor']);
        $db  = Database::getInstance();
        $rid = (int)$id;
        $uid = Auth::id();

        $sql1 = "SELECT r.status FROM co_requests r JOIN co_request_assignments a ON r.id = a.request_id WHERE r.id = ? AND a.assignee_id = ? AND a.is_active = 1 LIMIT 1";
        $req  = $db->row($sql1, [$rid, $uid]);

        if ($req && $req['status'] === 'assigned') {
            $sql2 = "UPDATE co_requests SET status = 'in_progress', updated_at = NOW() WHERE id = ?";
            $db->execute($sql2, [$rid]);
            $sql3 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, 'assigned', 'in_progress', ?, 'Started by designer')";
            $db->execute($sql3, [$rid, $uid]);
            $sql4 = "UPDATE co_sla_tracking SET started_at = COALESCE(started_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);

            // Notifikasi ke manager bahwa pengerjaan sudah dimulai
            $reqInfo  = $db->row("SELECT request_number, title FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
            $designer = $db->row("SELECT name FROM co_users WHERE id = ? LIMIT 1", [$uid]);
            if ($reqInfo) {
                $managers = $db->query("SELECT id FROM co_users WHERE role IN ('super_admin','creative_manager') AND is_active = 1", []);
                foreach ($managers as $mgr) {
                    NotificationService::create(
                        (int)$mgr['id'],
                        'in_progress',
                        'Mulai dikerjakan: ' . $reqInfo['request_number'],
                        $reqInfo['title'] . ' — oleh ' . ($designer['name'] ?? 'Designer'),
                        $rid
                    );
                }
            }
        }

        header('Location: ' . APP_URL . '/workspace');
        exit;
    }

    public function submitReview(string $id): void {
        Auth::requireRole(['designer', 'video_editor']);
        $db  = Database::getInstance();
        $rid = (int)$id;
        $uid = Auth::id();

        $sql1 = "SELECT r.status FROM co_requests r JOIN co_request_assignments a ON r.id = a.request_id WHERE r.id = ? AND a.assignee_id = ? AND a.is_active = 1 LIMIT 1";
        $req  = $db->row($sql1, [$rid, $uid]);

        if ($req && in_array($req['status'], ['in_progress','revision'], true)) {
            $sql2 = "UPDATE co_requests SET status = 'ready_review', updated_at = NOW() WHERE id = ?";
            $db->execute($sql2, [$rid]);
            $sql3 = "INSERT INTO co_request_status_log (request_id, from_status, to_status, changed_by, notes) VALUES (?, ?, 'ready_review', ?, 'Submitted for review')";
            $db->execute($sql3, [$rid, $req['status'], $uid]);
            $sql4 = "UPDATE co_sla_tracking SET submitted_review_at = COALESCE(submitted_review_at, NOW()) WHERE request_id = ?";
            $db->execute($sql4, [$rid]);

            // Notifikasi ke manager dan requester bahwa hasil siap direview
            $reqInfo  = $db->row("SELECT request_number, title, requester_id FROM co_requests WHERE id = ? LIMIT 1", [$rid]);
            $designer = $db->row("SELECT name FROM co_users WHERE id = ? LIMIT 1", [$uid]);
            if ($reqInfo) {
                $notifTitle = 'Siap direview: ' . $reqInfo['request_number'];
                $notifMsg   = $reqInfo['title'] . ' — oleh ' . ($designer['name'] ?? 'Designer');

                // Notify semua manager
                $managers = $db->query("SELECT id FROM co_users WHERE role IN ('super_admin','creative_manager') AND is_active = 1", []);
                foreach ($managers as $mgr) {
                    NotificationService::create((int)$mgr['id'], 'ready_review', $notifTitle, $notifMsg, $rid);
                }
                // Notify requester
                NotificationService::create((int)$reqInfo['requester_id'], 'ready_review', $notifTitle, $notifMsg, $rid);
            }
        }

        header('Location: ' . APP_URL . '/workspace');
        exit;
    }
}