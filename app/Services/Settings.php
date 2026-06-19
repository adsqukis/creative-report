<?php
class Settings {
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        $db  = Database::getInstance();
        $sql = "SELECT setting_value FROM co_settings WHERE setting_key = ? LIMIT 1";
        $row = $db->row($sql, [$key]);
        $val = ($row && $row['setting_value'] !== null) ? $row['setting_value'] : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, string $value): void {
        $db  = Database::getInstance();
        $sql = "INSERT INTO co_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $db->execute($sql, [$key, $value]);
        self::$cache[$key] = $value;
    }

    public static function all(): array {
        $db   = Database::getInstance();
        $sql  = "SELECT setting_key, setting_value FROM co_settings ORDER BY setting_key";
        $rows = $db->query($sql, []);
        $out  = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
        return $out;
    }
}
