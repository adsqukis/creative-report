<?php
class SettingsController {
    public function index(): void {
        Auth::requireRole(['super_admin']);
        $settings  = Settings::all();
        $pageTitle = 'Settings';
        $view      = 'settings/index';
        require APP_ROOT . '/views/layouts/app.php';
    }

    public function update(): void {
        Auth::requireRole(['super_admin']);

        $textFields = ['fonnte_token', 'fonnte_group_id', 'app_name'];
        foreach ($textFields as $key) {
            if (isset($_POST[$key])) {
                Settings::set($key, trim($_POST[$key]));
            }
        }
        Settings::set('wa_enabled', isset($_POST['wa_enabled']) ? '1' : '0');

        // Sync to fonnte config cache
        header('Location: ' . APP_URL . '/settings?saved=1');
        exit;
    }
}