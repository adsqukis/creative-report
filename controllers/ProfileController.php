<?php
class ProfileController {
    public function index(): void {
        Auth::require();
        $db        = Database::getInstance();
        $sql       = "SELECT id, name, email, role, capacity_hours_per_week, last_login_at FROM co_users WHERE id = ? LIMIT 1";
        $user      = $db->row($sql, [Auth::id()]);
        $error     = null;
        $success   = null;
        if (!empty($_GET['saved'])) {
            $success = 'Profil berhasil disimpan.';
        }
        $pageTitle = 'My Profile';
        $view      = 'profile/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(): void {
        Auth::require();
        $db   = Database::getInstance();
        $uid  = (int)Auth::id();
        $name = trim($_POST['name'] ?? '');

        if ($name !== '') {
            $sql = "UPDATE co_users SET name = ? WHERE id = ?";
            $db->execute($sql, [$name, $uid]);
            $_SESSION['auth_user']['name'] = $name;
        }

        $pw  = $_POST['password'] ?? '';
        $pw2 = $_POST['password_confirm'] ?? '';
        if ($pw !== '' && $pw === $pw2 && strlen($pw) >= 8) {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            $sql2 = "UPDATE co_users SET password = ? WHERE id = ?";
            $db->execute($sql2, [$hash, $uid]);
        }

        header('Location: ' . APP_URL . '/profile?saved=1');
        exit;
    }
}