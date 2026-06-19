<?php
class ProductController {
    public function index(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db       = Database::getInstance();
        $sql      = "SELECT p.*, COUNT(r.id) as total_requests FROM co_products p LEFT JOIN co_requests r ON p.id = r.product_id WHERE p.is_active = 1 GROUP BY p.id ORDER BY p.business_importance DESC, p.name";
        $products = $db->query($sql, []);
        $pageTitle = 'Products';
        $view      = 'products/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function create(): void {
        Auth::requireRole(['super_admin']);
        $error = null;
        $old   = [];
        $pageTitle = 'Add Product';
        $view      = 'products/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function store(): void {
        Auth::requireRole(['super_admin']);
        $db    = Database::getInstance();
        $name  = trim($_POST['name'] ?? '');
        $error = null;

        if ($name === '') {
            $error = 'Nama produk wajib diisi.';
            $old   = $_POST;
            $pageTitle = 'Add Product';
            $view  = 'products/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $prio = in_array($_POST['priority_level'] ?? '', ['critical','high','medium','low'], true) ? $_POST['priority_level'] : 'medium';
        $sql = "INSERT INTO co_products (name, code, priority_level, business_importance, monthly_budget, target_revenue) VALUES (?, ?, ?, ?, ?, ?)";
        $db->execute($sql, [
            $name,
            trim($_POST['code'] ?? ''),
            $prio,
            (int)($_POST['business_importance'] ?? 50),
            (float)($_POST['monthly_budget'] ?? 0),
            (float)($_POST['target_revenue'] ?? 0),
        ]);
        header('Location: ' . APP_URL . '/products');
        exit;
    }

    public function edit(string $id): void {
        Auth::requireRole(['super_admin']);
        $db      = Database::getInstance();
        $product = $db->row("SELECT * FROM co_products WHERE id = ? LIMIT 1", [(int)$id]);
        if (!$product) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }
        $error = null;
        $old   = $product;
        $pageTitle = 'Edit Product';
        $view      = 'products/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(string $id): void {
        Auth::requireRole(['super_admin']);
        $db   = Database::getInstance();
        $pid  = (int)$id;
        $prio = in_array($_POST['priority_level'] ?? '', ['critical','high','medium','low'], true) ? $_POST['priority_level'] : 'medium';
        $sql  = "UPDATE co_products SET name = ?, code = ?, priority_level = ?, business_importance = ?, monthly_budget = ?, target_revenue = ? WHERE id = ?";
        $db->execute($sql, [
            trim($_POST['name'] ?? ''),
            trim($_POST['code'] ?? ''),
            $prio,
            (int)($_POST['business_importance'] ?? 50),
            (float)($_POST['monthly_budget'] ?? 0),
            (float)($_POST['target_revenue'] ?? 0),
            $pid,
        ]);
        header('Location: ' . APP_URL . '/products');
        exit;
    }
}
