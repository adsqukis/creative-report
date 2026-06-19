<?php
class CampaignController {
    public function index(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db        = Database::getInstance();
        $sql       = "SELECT c.*, p.name as product_name, COUNT(r.id) as total_requests FROM co_campaigns c LEFT JOIN co_products p ON c.product_id = p.id LEFT JOIN co_requests r ON c.id = r.campaign_id GROUP BY c.id ORDER BY c.status, c.start_date DESC";
        $campaigns = $db->query($sql, []);
        $pageTitle = 'Campaigns';
        $view      = 'campaigns/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function create(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db       = Database::getInstance();
        $sqlP     = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
        $products = $db->query($sqlP, []);
        $error    = null;
        $old      = [];
        $pageTitle = 'Add Campaign';
        $view      = 'campaigns/create';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function store(): void {
        Auth::requireRole(['super_admin', 'creative_manager']);
        $db    = Database::getInstance();
        $name  = trim($_POST['name'] ?? '');
        $pid   = (int)($_POST['product_id'] ?? 0);
        $error = null;

        if ($name === '' || $pid < 1) {
            $error = 'Nama campaign dan produk wajib diisi.';
            $sqlP  = "SELECT id, name FROM co_products WHERE is_active = 1 ORDER BY name";
            $products = $db->query($sqlP, []);
            $old   = $_POST;
            $pageTitle = 'Add Campaign';
            $view  = 'campaigns/create';
            require APP_ROOT . '/views/layouts/app.php';
            return;
        }

        $status = in_array($_POST['status'] ?? '', ['planning','active','ended'], true) ? $_POST['status'] : 'planning';
        $sql = "INSERT INTO co_campaigns (product_id, name, start_date, end_date, importance, status) VALUES (?, ?, ?, ?, ?, ?)";
        $db->execute($sql, [
            $pid, $name,
            $_POST['start_date'] ?: null,
            $_POST['end_date'] ?: null,
            (int)($_POST['importance'] ?? 50),
            $status,
        ]);
        header('Location: ' . APP_URL . '/campaigns');
        exit;
    }
}
