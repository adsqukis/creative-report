<?php
class AlertService {
    private static function isDuplicate(string $type, ?int $requestId, ?int $userId): bool {
        $db  = Database::getInstance();
        $sql = "SELECT id FROM co_alert_log WHERE alert_type = ? AND (request_id = ? OR (request_id IS NULL AND ? IS NULL)) AND (user_id = ? OR (user_id IS NULL AND ? IS NULL)) AND DATE(sent_at) = CURDATE() LIMIT 1";
        $row = $db->row($sql, [$type, $requestId, $requestId, $userId, $userId]);
        return $row !== null;
    }

    private static function logAlert(string $type, ?int $requestId, ?int $userId): void {
        $db  = Database::getInstance();
        $sql = "INSERT INTO co_alert_log (alert_type, request_id, user_id) VALUES (?, ?, ?)";
        $db->execute($sql, [$type, $requestId, $userId]);
    }

    public static function checkDeadlineAlerts(): int {
        $db   = Database::getInstance();
        $sent = 0;

        $sql1 = "SELECT r.id, r.request_number, r.title, r.deadline, r.priority, r.requester_id, GROUP_CONCAT(a.assignee_id) as assignee_ids FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 WHERE r.status NOT IN ('completed','cancelled','rejected') AND r.deadline >= CURDATE() AND DATEDIFF(r.deadline, CURDATE()) <= 3 GROUP BY r.id";
        $upcoming = $db->query($sql1, []);

        foreach ($upcoming as $req) {
            $dl   = new DateTime($req['deadline']);
            $diff = (int)(new DateTime('today'))->diff($dl)->days;

            if ($diff === 3) {
                $type = 'deadline_h3';
            } elseif ($diff === 1) {
                $type = 'deadline_h1';
            } elseif ($diff === 0) {
                $type = 'deadline_today';
            } else {
                continue;
            }

            $reqId   = (int)$req['id'];
            $title   = 'Deadline ' . ($diff === 0 ? 'hari ini' : 'H-' . $diff) . ': ' . $req['request_number'];
            $message = $req['title'] . ' — deadline ' . $dl->format('d M Y');

            if (!self::isDuplicate($type, $reqId, null)) {
                NotificationService::create((int)$req['requester_id'], $type, $title, $message, $reqId);
                self::logAlert($type, $reqId, null);

                $ids = array_filter(explode(',', $req['assignee_ids'] ?? ''));
                foreach ($ids as $aid) {
                    NotificationService::create((int)$aid, $type, $title, $message, $reqId);
                }
                $sent++;
            }
        }

        $sql2    = "SELECT r.id, r.request_number, r.title, r.deadline, r.requester_id FROM co_requests r WHERE r.status NOT IN ('completed','cancelled','rejected') AND r.deadline < CURDATE()";
        $overdue = $db->query($sql2, []);

        foreach ($overdue as $req) {
            $reqId = (int)$req['id'];
            if (!self::isDuplicate('overdue', $reqId, null)) {
                $title   = 'Overdue: ' . $req['request_number'];
                $message = $req['title'] . ' sudah melewati deadline ' . (new DateTime($req['deadline']))->format('d M Y');
                NotificationService::create(null, 'overdue', $title, $message, $reqId);
                self::logAlert('overdue', $reqId, null);
                $sent++;
            }
        }

        return $sent;
    }

    public static function checkUnassignedCritical(): int {
        $db   = Database::getInstance();
        $sent = 0;

        $sql  = "SELECT r.id, r.request_number, r.title FROM co_requests r LEFT JOIN co_request_assignments a ON r.id = a.request_id AND a.is_active = 1 WHERE r.priority = 'critical' AND r.status IN ('draft','waiting_queue') AND a.id IS NULL";
        $rows = $db->query($sql, []);

        foreach ($rows as $row) {
            $reqId = (int)$row['id'];
            if (!self::isDuplicate('critical_unassigned', $reqId, null)) {
                $title   = 'Critical belum diassign: ' . $row['request_number'];
                $message = $row['title'] . ' prioritas Critical belum ada assignee.';
                NotificationService::create(null, 'critical_unassigned', $title, $message, $reqId);
                self::logAlert('critical_unassigned', $reqId, null);
                $sent++;
            }
        }

        return $sent;
    }

    public static function checkHighRevision(): int {
        $db   = Database::getInstance();
        $sent = 0;

        $sql  = "SELECT id, request_number, title, revision_count FROM co_requests WHERE revision_count >= 3 AND status NOT IN ('completed','cancelled','rejected')";
        $rows = $db->query($sql, []);

        foreach ($rows as $row) {
            $reqId = (int)$row['id'];
            if (!self::isDuplicate('high_revision', $reqId, null)) {
                $title   = 'Revisi tinggi: ' . $row['request_number'];
                $message = $row['title'] . ' sudah ' . $row['revision_count'] . 'x revisi.';
                NotificationService::create(null, 'high_revision', $title, $message, $reqId);
                self::logAlert('high_revision', $reqId, null);
                $sent++;
            }
        }

        return $sent;
    }

    public static function checkDesignerOverload(): int {
        $sent      = 0;
        $overloaded = WorkloadService::getOverloadedUsers();

        foreach ($overloaded as $user) {
            $uid = (int)$user['id'];
            if (!self::isDuplicate('designer_overload', null, $uid)) {
                $title   = 'Overload: ' . $user['name'];
                $message = $user['name'] . ' workload ' . $user['workload_pct'] . '% (' . $user['active_tasks'] . ' task aktif).';
                NotificationService::create(null, 'designer_overload', $title, $message, null);
                self::logAlert('designer_overload', null, $uid);
                $sent++;
            }
        }

        return $sent;
    }

    public static function runAll(): array {
        return [
            'deadline_alerts'     => self::checkDeadlineAlerts(),
            'critical_unassigned' => self::checkUnassignedCritical(),
            'high_revision'       => self::checkHighRevision(),
            'overload_alerts'     => self::checkDesignerOverload(),
        ];
    }
}
