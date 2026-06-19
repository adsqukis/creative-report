<?php
class DashboardController {
    public function overview(): void {
        Auth::require();
        $db   = Database::getInstance();
        $user = Auth::user();

        $sql1 = "SELECT COUNT(*) FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected')";
        $totalActive = $db->count($sql1, []);

        $sql2 = "SELECT COUNT(*) FROM co_requests WHERE status = 'completed' AND DATE_FORMAT(completed_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')";
        $completedMtd = $db->count($sql2, []);

        $sql3 = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $overdueCount = $db->count($sql3, []);

        $sql4 = "SELECT COUNT(*) FROM co_requests WHERE priority = 'critical' AND status NOT IN ('completed','cancelled','rejected')";
        $criticalCount = $db->count($sql4, []);

        $sql5 = "SELECT COUNT(*) FROM co_requests WHERE status = 'waiting_queue'";
        $inQueue = $db->count($sql5, []);

        $sql6 = "SELECT r.id, r.request_number, r.title, r.status, r.priority, r.deadline, r.asset_type, u.name as requester_name, p.name as product_name FROM co_requests r LEFT JOIN co_users u ON r.requester_id = u.id LEFT JOIN co_products p ON r.product_id = p.id ORDER BY r.created_at DESC LIMIT 10";
        $recentRequests = $db->query($sql6, []);

        $stats = [
            'active'        => $totalActive,
            'completed_mtd' => $completedMtd,
            'overdue'       => $overdueCount,
            'critical'      => $criticalCount,
            'in_queue'      => $inQueue,
        ];

        $pageTitle = 'Overview';
        $view      = 'dashboard/overview';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function executive(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db = Database::getInstance();

        $sql1 = "SELECT COUNT(*) FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected')";
        $totalOpen = $db->count($sql1, []);

        $sql2 = "SELECT COUNT(*) FROM co_requests WHERE priority = 'critical' AND status NOT IN ('completed','cancelled','rejected')";
        $criticalOpen = $db->count($sql2, []);

        $sql3 = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $overdue = $db->count($sql3, []);

        $sql4 = "SELECT COUNT(*) FROM co_requests WHERE deadline = CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $todayDeadline = $db->count($sql4, []);

        $sql5 = "SELECT COUNT(*) FROM co_requests WHERE status IN ('waiting_queue','draft') AND priority IN ('critical','high') AND assigned_at IS NULL";
        $unassignedUrgent = $db->count($sql5, []);

        $sql6 = "SELECT u.name, COUNT(r.id) as task_count FROM co_users u LEFT JOIN co_request_assignments a ON u.id = a.assignee_id AND a.is_active = 1 LEFT JOIN co_requests r ON a.request_id = r.id AND r.status NOT IN ('completed','cancelled','rejected') WHERE u.role IN ('designer','video_editor') AND u.is_active = 1 GROUP BY u.id, u.name ORDER BY task_count DESC";
        $teamWorkload = $db->query($sql6, []);

        $stats = [
            'total_open'       => $totalOpen,
            'critical_open'    => $criticalOpen,
            'overdue'          => $overdue,
            'today_deadline'   => $todayDeadline,
            'unassigned_urgent' => $unassignedUrgent,
        ];

        $pageTitle = 'Executive Command Center';
        $view      = 'dashboard/executive';
        require APP_ROOT . '/views/layouts/app.php';
    }
}
