<?php
class SlaService {
    public static function getSlaHours(string $priority, string $effort): int {
        $db  = Database::getInstance();
        $sql = "SELECT sla_hours FROM co_sla_rules WHERE priority = ? AND estimated_effort = ? LIMIT 1";
        $row = $db->row($sql, [$priority, $effort]);
        return $row ? (int)$row['sla_hours'] : 96;
    }

    public static function flagOverdueSla(): int {
        $db      = Database::getInstance();
        $updated = 0;

        $sql = "SELECT s.id, s.request_id, s.assigned_at, s.completed_at, r.priority, r.estimated_effort FROM co_sla_tracking s JOIN co_requests r ON s.request_id = r.id WHERE r.status NOT IN ('cancelled','rejected') AND s.completed_at IS NULL AND s.assigned_at IS NOT NULL";
        $rows = $db->query($sql, []);

        foreach ($rows as $row) {
            $slaHours   = self::getSlaHours($row['priority'], $row['estimated_effort']);
            $assignedAt = new DateTime($row['assigned_at']);
            $deadline   = clone $assignedAt;
            $deadline->modify('+' . $slaHours . ' hours');
            $now        = new DateTime();

            if ($now > $deadline) {
                $lateBy  = (int)(($now->getTimestamp() - $deadline->getTimestamp()) / 3600);
                $sql2    = "UPDATE co_sla_tracking SET is_late = 1, late_by_hours = ?, sla_hours = ? WHERE id = ?";
                $db->execute($sql2, [$lateBy, $slaHours, (int)$row['id']]);
                $updated++;
            }
        }

        return $updated;
    }

    public static function updateTurnaround(int $requestId): void {
        $db  = Database::getInstance();
        $sql = "SELECT requested_at, completed_at FROM co_sla_tracking WHERE request_id = ? LIMIT 1";
        $row = $db->row($sql, [$requestId]);
        if (!$row || empty($row['requested_at']) || empty($row['completed_at'])) {
            return;
        }
        $start  = new DateTime($row['requested_at']);
        $end    = new DateTime($row['completed_at']);
        $hours  = (int)(($end->getTimestamp() - $start->getTimestamp()) / 3600);
        $sql2   = "UPDATE co_sla_tracking SET turnaround_hours = ? WHERE request_id = ?";
        $db->execute($sql2, [$hours, $requestId]);
    }

    public static function getMonthlyStats(): array {
        $db  = Database::getInstance();
        $ym  = date('Y-m');

        $sql1     = "SELECT COUNT(*) FROM co_sla_tracking WHERE completed_at IS NOT NULL AND DATE_FORMAT(completed_at,'%Y-%m') = ?";
        $total    = $db->count($sql1, [$ym]);

        $sql2     = "SELECT COUNT(*) FROM co_sla_tracking WHERE is_late = 0 AND completed_at IS NOT NULL AND DATE_FORMAT(completed_at,'%Y-%m') = ?";
        $onTime   = $db->count($sql2, [$ym]);

        $sql3     = "SELECT AVG(turnaround_hours) FROM co_sla_tracking WHERE turnaround_hours IS NOT NULL AND DATE_FORMAT(completed_at,'%Y-%m') = ?";
        $row3     = $db->row($sql3, [$ym]);
        $avgHours = $row3 ? round((float)array_values($row3)[0], 1) : 0;

        $achievementPct = $total > 0 ? round(($onTime / $total) * 100, 1) : 0;
        $latePct        = $total > 0 ? round((($total - $onTime) / $total) * 100, 1) : 0;

        return [
            'total_completed'   => $total,
            'on_time'           => $onTime,
            'late'              => $total - $onTime,
            'achievement_pct'   => $achievementPct,
            'late_pct'          => $latePct,
            'avg_turnaround_h'  => $avgHours,
        ];
    }
}
