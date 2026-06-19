<?php
class WorkloadService {
    private static array $effortHours = [
        'small'  => 4,
        'medium' => 8,
        'large'  => 16,
    ];

    public static function getUserMetrics(int $userId): array {
        $db  = Database::getInstance();

        $sql1 = "SELECT r.estimated_effort, r.deadline, r.status FROM co_requests r JOIN co_request_assignments a ON r.id = a.request_id WHERE a.assignee_id = ? AND a.is_active = 1 AND r.status NOT IN ('completed','cancelled','rejected')";
        $tasks = $db->query($sql1, [$userId]);

        $sql2 = "SELECT capacity_hours_per_week FROM co_users WHERE id = ? LIMIT 1";
        $user  = $db->row($sql2, [$userId]);
        $capacity = $user ? (float)$user['capacity_hours_per_week'] : 40;

        $activeTasks    = count($tasks);
        $estimatedHours = 0;
        $overdueCount   = 0;
        $todayCount     = 0;
        $weekCount      = 0;
        $today          = new DateTime('today');
        $nextWeek       = (new DateTime('today'))->modify('+7 days');

        foreach ($tasks as $t) {
            $estimatedHours += self::$effortHours[$t['estimated_effort']] ?? 8;
            $dl              = new DateTime($t['deadline']);
            if ($dl < $today) {
                $overdueCount++;
            }
            if ($dl->format('Y-m-d') === $today->format('Y-m-d')) {
                $todayCount++;
            }
            if ($dl <= $nextWeek) {
                $weekCount++;
            }
        }

        $workloadPct   = $capacity > 0 ? round(($estimatedHours / $capacity) * 100, 1) : 0;

        $sqlComp  = "SELECT COUNT(*) FROM co_requests r JOIN co_request_assignments a ON r.id = a.request_id WHERE a.assignee_id = ? AND r.status = 'completed' AND DATE_FORMAT(r.completed_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')";
        $completedMtd = $db->count($sqlComp, [$userId]);

        return [
            'user_id'          => $userId,
            'active_tasks'     => $activeTasks,
            'overdue_tasks'    => $overdueCount,
            'tasks_today'      => $todayCount,
            'tasks_this_week'  => $weekCount,
            'estimated_hours'  => $estimatedHours,
            'capacity_hours'   => $capacity,
            'workload_pct'     => $workloadPct,
            'completed_mtd'    => $completedMtd,
            'is_overloaded'    => $workloadPct > 100,
            'is_warning'       => $workloadPct > 80 && $workloadPct <= 100,
        ];
    }

    public static function getTeamWorkload(): array {
        $db   = Database::getInstance();
        $sql  = "SELECT id, name, role, capacity_hours_per_week FROM co_users WHERE role IN ('designer','video_editor') AND is_active = 1 ORDER BY role, name";
        $team = $db->query($sql, []);

        $result = [];
        foreach ($team as $member) {
            $metrics  = self::getUserMetrics((int)$member['id']);
            $result[] = array_merge($member, $metrics);
        }
        return $result;
    }

    public static function takeSnapshot(): void {
        $db      = Database::getInstance();
        $today   = date('Y-m-d');
        $sql     = "SELECT id FROM co_users WHERE role IN ('designer','video_editor') AND is_active = 1";
        $members = $db->query($sql, []);

        foreach ($members as $m) {
            $uid = (int)$m['id'];
            $met = self::getUserMetrics($uid);

            $sql2 = "INSERT INTO co_workload_snapshots (user_id, snapshot_date, active_tasks, overdue_tasks, tasks_today, tasks_this_week, estimated_hours_active, capacity_hours, workload_pct) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE active_tasks = VALUES(active_tasks), overdue_tasks = VALUES(overdue_tasks), tasks_today = VALUES(tasks_today), tasks_this_week = VALUES(tasks_this_week), estimated_hours_active = VALUES(estimated_hours_active), workload_pct = VALUES(workload_pct)";
            $db->execute($sql2, [
                $uid,
                $today,
                $met['active_tasks'],
                $met['overdue_tasks'],
                $met['tasks_today'],
                $met['tasks_this_week'],
                $met['estimated_hours'],
                $met['capacity_hours'],
                $met['workload_pct'],
            ]);
        }
    }

    public static function getOverloadedUsers(): array {
        $team = self::getTeamWorkload();
        return array_filter($team, function($m) {
            return $m['is_overloaded'];
        });
    }
}
