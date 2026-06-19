<?php
class AuthController {
    public function showLogin(): void {
        if (!empty($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/dashboard');
            exit;
        }
        $pageTitle = 'Login';
        $error = null;
        require APP_ROOT . '/views/auth/login.php';
    }

    public function login(): void {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $error    = null;

        if (empty($email) || empty($password)) {
            $error = 'Email dan password wajib diisi.';
        } else {
            $db  = Database::getInstance();
            $sql = "SELECT id, name, email, password, role, is_active FROM co_users WHERE email = ? LIMIT 1";
            $user = $db->row($sql, [$email]);

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Email atau password salah.';
            } elseif (!(int)$user['is_active']) {
                $error = 'Akun tidak aktif. Hubungi administrator.';
            } else {
                Auth::login($user);
                $sql2 = "UPDATE co_users SET last_login_at = NOW() WHERE id = ?";
                $db->execute($sql2, [(int)$user['id']]);
                header('Location: ' . APP_URL . '/dashboard');
                exit;
            }
        }

        $pageTitle = 'Login';
        require APP_ROOT . '/views/auth/login.php';
    }

    public function logout(): void {
        Auth::logout();
        header('Location: ' . APP_URL . '/login');
        exit;
    }
}
