<?php
class ApiController {
    private function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function notifications(): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $uid   = (int)$_SESSION['user_id'];
        $db    = Database::getInstance();
        $sql1  = "SELECT COUNT(*) FROM co_notifications WHERE user_id = ? AND is_read = 0";
        $count = $db->count($sql1, [$uid]);
        $sql2  = "SELECT id, type, title, message, request_id, is_read, created_at FROM co_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
        $items = $db->query($sql2, [$uid]);
        $this->json(['unread' => $count, 'items' => $items]);
    }

    public function markRead(): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        NotificationService::markAllRead((int)$_SESSION['user_id']);
        $this->json(['ok' => true]);
    }

    public function workload(): void {
        if (empty($_SESSION['user_id'])) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        $data = WorkloadService::getTeamWorkload();
        $this->json($data);
    }

    public function aiChat(): void {
        $this->json(['error' => 'AI Chat coming in Phase 4'], 503);
    }
}