<?php
class NotificationService {
    public static function create(
        ?int   $userId,
        string $type,
        string $title,
        string $message,
        ?int   $requestId = null
    ): void {
        $db  = Database::getInstance();
        $sql = "INSERT INTO co_notifications (user_id, type, title, message, request_id) VALUES (?, ?, ?, ?, ?)";
        $db->execute($sql, [$userId, $type, $title, $message, $requestId]);
    }

    public static function sendWa(string $message, string $target): bool {
        $cfg = require APP_ROOT . '/config/fonnte.php';
        if (empty($cfg['enabled']) || empty($cfg['token'])) {
            return false;
        }

        $ch = curl_init($cfg['endpoint']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)$cfg['timeout']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $cfg['token']]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'target'  => $target,
            'message' => $message,
        ]);
        $result = curl_exec($ch);
        $err    = curl_errno($ch);
        curl_close($ch);
        return $err === 0 && !empty($result);
    }

    public static function sendWaGroup(string $message): bool {
        $cfg = require APP_ROOT . '/config/fonnte.php';
        if (empty($cfg['group_id'])) {
            return false;
        }
        return self::sendWa($message, $cfg['group_id']);
    }

    public static function getUnreadCount(int $userId): int {
        $db  = Database::getInstance();
        $sql = "SELECT COUNT(*) FROM co_notifications WHERE user_id = ? AND is_read = 0";
        return $db->count($sql, [$userId]);
    }

    public static function markAllRead(int $userId): void {
        $db  = Database::getInstance();
        $sql = "UPDATE co_notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        $db->execute($sql, [$userId]);
    }
}
