<?php
class AnalyticsController {
    public function index(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db = Database::getInstance();
        $ym = date('Y-m');

        $sql1 = "SELECT COUNT(*) FROM co_requests WHERE DATE_FORMAT(created_at,'%Y-%m') = ?";
        $totalMtd = $db->count($sql1, [$ym]);

        $sql2 = "SELECT COUNT(*) FROM co_requests WHERE status = 'completed' AND DATE_FORMAT(completed_at,'%Y-%m') = ?";
        $completedMtd = $db->count($sql2, [$ym]);

        $sql3 = "SELECT COUNT(*) FROM co_requests WHERE deadline < CURDATE() AND status NOT IN ('completed','cancelled','rejected')";
        $overdueNow = $db->count($sql3, []);

        $sql4 = "SELECT COUNT(*) FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected')";
        $activeNow = $db->count($sql4, []);

        $slaStats      = SlaService::getMonthlyStats();
        $completionPct = $totalMtd > 0 ? round(($completedMtd / $totalMtd) * 100, 1) : 0;

        $sql5      = "SELECT asset_type, COUNT(*) as cnt FROM co_requests WHERE DATE_FORMAT(created_at,'%Y-%m') = ? GROUP BY asset_type ORDER BY cnt DESC";
        $assetBreakdown = $db->query($sql5, [$ym]);

        $sql6      = "SELECT p.name, COUNT(r.id) as cnt FROM co_requests r JOIN co_products p ON r.product_id = p.id WHERE DATE_FORMAT(r.created_at,'%Y-%m') = ? GROUP BY p.id ORDER BY cnt DESC LIMIT 5";
        $topProducts = $db->query($sql6, [$ym]);

        $sql7      = "SELECT u.name, COUNT(r.id) as cnt FROM co_requests r JOIN co_users u ON r.requester_id = u.id WHERE DATE_FORMAT(r.created_at,'%Y-%m') = ? GROUP BY u.id ORDER BY cnt DESC LIMIT 5";
        $topRequesters = $db->query($sql7, [$ym]);

        $sql8      = "SELECT asset_type, COUNT(*) as cnt FROM co_requests WHERE DATE_FORMAT(created_at,'%Y-%m') = ? AND status = 'completed' GROUP BY asset_type";
        $completedByType = $db->query($sql8, [$ym]);
        $completedTypeMap = [];
        foreach ($completedByType as $row) {
            $completedTypeMap[$row['asset_type']] = (int)$row['cnt'];
        }

        $stats = [
            'total_mtd'       => $totalMtd,
            'completed_mtd'   => $completedMtd,
            'overdue_now'     => $overdueNow,
            'active_now'      => $activeNow,
            'completion_pct'  => $completionPct,
        ];

        $pageTitle = 'Analytics';
        $view      = 'analytics/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function sla(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db = Database::getInstance();

        $slaStats = SlaService::getMonthlyStats();

        $sql1 = "SELECT r.request_number, r.title, r.deadline, r.priority, s.late_by_hours, s.turnaround_hours, u.name as requester_name FROM co_requests r JOIN co_sla_tracking s ON r.id = s.request_id LEFT JOIN co_users u ON r.requester_id = u.id WHERE s.is_late = 1 ORDER BY s.late_by_hours DESC LIMIT 30";
        $lateRequests = $db->query($sql1, []);

        $sql2 = "SELECT r.priority, COUNT(*) as total, SUM(CASE WHEN s.is_late = 0 THEN 1 ELSE 0 END) as on_time FROM co_requests r JOIN co_sla_tracking s ON r.id = s.request_id WHERE r.status = 'completed' AND DATE_FORMAT(r.completed_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') GROUP BY r.priority";
        $byPriority = $db->query($sql2, []);

        $pageTitle = 'SLA Tracking';
        $view      = 'analytics/sla';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function scorecard(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db = Database::getInstance();
        $ym = date('Y-m');

        $sql1 = "SELECT k.*, u.name, u.role FROM co_designer_kpi_monthly k JOIN co_users u ON k.user_id = u.id WHERE k.year_month = ? ORDER BY k.creative_score DESC";
        $kpis = $db->query($sql1, [$ym]);

        $sql2 = "SELECT DISTINCT year_month FROM co_designer_kpi_monthly ORDER BY year_month DESC LIMIT 12";
        $months = $db->query($sql2, []);

        $pageTitle = 'Creative Scorecard';
        $view      = 'analytics/scorecard';
        require APP_ROOT . '/views/layouts/app.php';
    }
}
