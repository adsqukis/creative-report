<?php
class WorkloadController {
    public function index(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db = Database::getInstance();

        $teamWorkload = WorkloadService::getTeamWorkload();

        $totalOverloaded = 0;
        $totalActive     = 0;
        $totalOverdue    = 0;
        foreach ($teamWorkload as $m) {
            if ($m['is_overloaded']) {
                $totalOverloaded++;
            }
            $totalActive  += $m['active_tasks'];
            $totalOverdue += $m['overdue_tasks'];
        }

        // Active task detail per assignee
        $tasksByUser = [];
        foreach ($teamWorkload as $m) {
            $uid  = (int)$m['id'];
            $sql  = "SELECT r.id, r.request_number, r.title, r.status, r.priority, r.deadline, r.estimated_effort FROM co_requests r JOIN co_request_assignments a ON r.id = a.request_id WHERE a.assignee_id = ? AND a.is_active = 1 AND r.status NOT IN ('completed','cancelled','rejected') ORDER BY r.deadline ASC LIMIT 20";
            $tasksByUser[$uid] = $db->query($sql, [$uid]);
        }

        $pageTitle = 'Workload Dashboard';
        $view      = 'workload/index';
        require APP_ROOT . '/views/layouts/app.php';
    }
}
