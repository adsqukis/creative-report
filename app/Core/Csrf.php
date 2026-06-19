<?php
class Csrf {
    private static string $key = '_co_csrf';

    public static function token(): string {
        if (empty($_SESSION[self::$key])) {
            $_SESSION[self::$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$key];
    }

    public static function input(): string {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    public static function verify(): bool {
        $posted = $_POST['_csrf_token'] ?? '';
        $stored = $_SESSION[self::$key] ?? '';
        if (empty($stored) || empty($posted)) {
            return false;
        }
        // Tidak di-rotate agar AJAX (multi-request) bisa pakai token yang sama
        return hash_equals($stored, $posted);
    }

    public static function verifyOrFail(): void {
        if (!self::verify()) {
            http_response_code(403);
            require APP_ROOT . '/views/errors/csrf.php';
            exit;
        }
    }
}
