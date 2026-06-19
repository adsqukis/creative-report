<?php
class DepartmentController {
    public function index(): void {
        Auth::requireRole(['super_admin']);
        $db = Database::getInstance();
        $sql = "SELECT d.*, COUNT(u.id) as total_users FROM co_departments d LEFT JOIN co_users u ON u.department_id = d.id GROUP BY d.id ORDER BY d.name";
        $departments = $db->query($sql, []);
        $pageTitle = 'Departments';
        $view      = 'departments/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function create(): void {
        Auth::requireRole(['super_admin']);
        $error = null;
        $old   = [];
        $pageTitle = 'Add Department';
        $view      = 'departments/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function store(): void {
        Auth::requireRole(['super_admin']);
        $db   = Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $error = null;

        if ($name === '') {
            $error = 'Nama department wajib diisi.';
            $old   = $_POST;
            $pageTitle = 'Add Department';
            $view  = 'departments/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $sql = "INSERT INTO co_departments (name) VALUES (?)";
        $db->execute($sql, [$name]);
        header('Location: ' . APP_URL . '/departments');
        exit;
    }

    public function edit(string $id): void {
        Auth::requireRole(['super_admin']);
        $db  = Database::getInstance();
        $dept = $db->row("SELECT * FROM co_departments WHERE id = ? LIMIT 1", [(int)$id]);
        if (!$dept) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }
        $error = null;
        $old   = $dept;
        $pageTitle = 'Edit Department';
        $view      = 'departments/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(string $id): void {
        Auth::requireRole(['super_admin']);
        $db   = Database::getInstance();
        $did  = (int)$id;
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $error = 'Nama department wajib diisi.';
            $old   = array_merge($_POST, ['id' => $did]);
            $pageTitle = 'Edit Department';
            $view  = 'departments/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $sql = "UPDATE co_departments SET name = ? WHERE id = ?";
        $db->execute($sql, [$name, $did]);
        header('Location: ' . APP_URL . '/departments');
        exit;
    }

    public function delete(string $id): void {
        Auth::requireRole(['super_admin']);
        $db  = Database::getInstance();
        $did = (int)$id;

        $inUse = $db->row("SELECT COUNT(*) as c FROM co_users WHERE department_id = ?", [$did]);
        if ($inUse && (int)$inUse['c'] > 0) {
            $_SESSION['department_error'] = 'Department tidak bisa dihapus karena masih dipakai oleh user.';
            header('Location: ' . APP_URL . '/departments');
            exit;
        }

        $db->execute("DELETE FROM co_departments WHERE id = ?", [$did]);
        header('Location: ' . APP_URL . '/departments');
        exit;
    }
}
