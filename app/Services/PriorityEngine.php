<?php
class PriorityEngine {
    private static array $priorityWeights = [
        'critical' => 100,
        'high'     => 75,
        'medium'   => 50,
        'low'      => 25,
    ];

    public static function calculate(array $req): int {
        $today    = new DateTime('today');
        $deadline = new DateTime($req['deadline']);
        $diff     = (int)$today->diff($deadline)->days;
        $isPast   = $deadline < $today;

        if ($isPast) {
            $deadlineScore = 100;
        } elseif ($diff === 0) {
            $deadlineScore = 90;
        } elseif ($diff === 1) {
            $deadlineScore = 75;
        } elseif ($diff === 2) {
            $deadlineScore = 60;
        } elseif ($diff === 3) {
            $deadlineScore = 45;
        } elseif ($diff <= 7) {
            $deadlineScore = 30;
        } else {
            $deadlineScore = 10;
        }

        $priorityWeight  = self::$priorityWeights[$req['priority']] ?? 50;
        $productImp      = (int)($req['business_importance'] ?? 50);
        $campaignImp     = (int)($req['campaign_importance'] ?? 50);

        $created   = new DateTime($req['created_at']);
        $daysSince = (int)(new DateTime())->diff($created)->days;
        $ageScore  = min(($daysSince / 30) * 100, 100);

        $revPenalty = min((int)($req['revision_count'] ?? 0) * 20, 100);

        $score = (int)(
            ($deadlineScore  * 0.35) +
            ($priorityWeight * 0.25) +
            ($productImp     * 0.20) +
            ($campaignImp    * 0.10) +
            ($ageScore       * 0.05) +
            ($revPenalty     * 0.05)
        );

        return min(max($score, 0), 100);
    }

    public static function updateRequest(int $requestId): void {
        $db  = Database::getInstance();
        $sql = "SELECT r.*, COALESCE(p.business_importance, 50) as business_importance, COALESCE(c.importance, 50) as campaign_importance FROM co_requests r LEFT JOIN co_products p ON r.product_id = p.id LEFT JOIN co_campaigns c ON r.campaign_id = c.id WHERE r.id = ? LIMIT 1";
        $req = $db->row($sql, [$requestId]);
        if (!$req) {
            return;
        }
        $score  = self::calculate($req);
        $isLate = ((new DateTime($req['deadline'])) < new DateTime('today')) ? 1 : 0;
        $sql2   = "UPDATE co_requests SET priority_score = ?, is_late = ? WHERE id = ?";
        $db->execute($sql2, [$score, $isLate, $requestId]);
    }

    public static function recalcAll(): void {
        $db   = Database::getInstance();
        $sql  = "SELECT id FROM co_requests WHERE status NOT IN ('completed','cancelled','rejected')";
        $rows = $db->query($sql, []);
        foreach ($rows as $row) {
            self::updateRequest((int)$row['id']);
        }
    }

    public static function getEffortHours(string $effort): int {
        $map = ['small' => 4, 'medium' => 8, 'large' => 16];
        return $map[$effort] ?? 8;
    }
}
