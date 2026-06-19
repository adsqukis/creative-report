<?php
class UserController {
    public function index(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db    = Database::getInstance();
        $sql   = "SELECT u.*, d.name as department_name FROM co_users u LEFT JOIN co_departments d ON u.department_id = d.id ORDER BY u.role, u.name";
        $users = $db->query($sql, []);

        $pageTitle = 'Users';
        $view      = 'users/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function create(): void {
        Auth::requireRole(['super_admin']);
        $db          = Database::getInstance();
        $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
        $departments = $db->query($sqlD, []);
        $error       = null;
        $old         = [];

        $pageTitle = 'Add User';
        $view      = 'users/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function store(): void {
        Auth::requireRole(['super_admin']);
        $db       = Database::getInstance();
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? '');
        $validRoles = ['super_admin','creative_manager','designer','video_editor','requester','viewer'];
        $error    = null;

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Nama, email, dan password wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } elseif (!in_array($role, $validRoles, true)) {
            $error = 'Role tidak valid.';
        } elseif (strlen($password) < 8) {
            $error = 'Password minimal 8 karakter.';
        }

        if ($error !== null) {
            $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
            $departments = $db->query($sqlD, []);
            $old         = $_POST;
            $pageTitle   = 'Add User';
            $view        = 'users/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $existing = $db->row("SELECT id FROM co_users WHERE email = ? LIMIT 1", [$email]);
        if ($existing) {
            $error = 'Email sudah digunakan.';
            $sqlD  = "SELECT id, name FROM co_departments ORDER BY name";
            $departments = $db->query($sqlD, []);
            $old   = $_POST;
            $pageTitle = 'Add User';
            $view  = 'users/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $hash         = password_hash($password, PASSWORD_BCRYPT);
        $departmentId = (int)($_POST['department_id'] ?? 0) ?: null;
        $capacity     = (float)($_POST['capacity_hours_per_week'] ?? 40);

        $sql = "INSERT INTO co_users (name, email, password, role, department_id, capacity_hours_per_week, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $db->execute($sql, [$name, $email, $hash, $role, $departmentId, $capacity]);

        header('Location: ' . APP_URL . '/users');
        exit;
    }

    public function edit(string $id): void {
        Auth::requireRole(['super_admin']);
        $db   = Database::getInstance();
        $uid  = (int)$id;
        $user = $db->row("SELECT * FROM co_users WHERE id = ? LIMIT 1", [$uid]);
        if (!$user) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }
        $sqlD        = "SELECT id, name FROM co_departments ORDER BY name";
        $departments = $db->query($sqlD, []);
        $error       = null;
        $old         = $user;

        $pageTitle = 'Edit User';
        $view      = 'users/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(string $id): void {
        Auth::requireRole(['super_admin']);
        $db       = Database::getInstance();
        $uid      = (int)$id;
        $name     = trim($_POST['name'] ?? '');
        $role     = trim($_POST['role'] ?? '');
        $isActive = (int)($_POST['is_active'] ?? 1);
        $capacity = (float)($_POST['capacity_hours_per_week'] ?? 40);
        $deptId   = (int)($_POST['department_id'] ?? 0) ?: null;

        $sql = "UPDATE co_users SET name = ?, role = ?, is_active = ?, capacity_hours_per_week = ?, department_id = ? WHERE id = ?";
        $db->execute($sql, [$name, $role, $isActive, $capacity, $deptId, $uid]);

        if (!empty($_POST['password']) && strlen($_POST['password']) >= 8) {
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql2 = "UPDATE co_users SET password = ? WHERE id = ?";
            $db->execute($sql2, [$hash, $uid]);
        }

        header('Location: ' . APP_URL . '/users');
        exit;
    }
}
