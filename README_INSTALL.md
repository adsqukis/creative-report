# CREATIVE OPS — PANDUAN INSTALL

## Langkah 1: Upload
Upload seluruh folder `creative-ops/` ke `public_html/creative-ops/` via Hostinger File Manager.

## Langkah 2: Setup Database
1. Buat database baru di Hostinger hPanel → Databases
2. Buka phpMyAdmin → pilih database tersebut
3. Import file: `migrations/001_creative_ops.sql`

## Langkah 3: Konfigurasi

Edit `config/app.php`:
```php
define('APP_URL', 'https://yourdomain.com/creative-ops');
```

Edit `config/database.php`:
```php
return [
    'host' => 'localhost',
    'name' => 'nama_database_kamu',
    'user' => 'user_database_kamu',
    'pass' => 'password_database_kamu',
];
```

Edit `config/ai.php`:
```php
'api_key' => 'sk-your-deepseek-api-key',
```

## Langkah 4: Verifikasi
Akses: `https://yourdomain.com/creative-ops/install.php`
Pastikan semua cek hijau.
**Hapus `install.php` setelah berhasil.**

## Langkah 5: Login
URL: `https://yourdomain.com/creative-ops/login`
```
Email   : admin@creative-ops.local
Password: Admin@2026
```
**Ganti password segera setelah login pertama.**

## Langkah 6: Setup Cron (Hostinger cPanel)
Tambahkan di cPanel → Cron Jobs:
```
*/30 * * * *  php /home/username/public_html/creative-ops/cron/sla_check.php
0 * * * *     php /home/username/public_html/creative-ops/cron/alerts.php
0 6 * * *     php /home/username/public_html/creative-ops/cron/ai_daily_briefing.php
0 10,15 * * * php /home/username/public_html/creative-ops/cron/ai_insights.php
30 23 * * *   php /home/username/public_html/creative-ops/cron/workload_snapshot.php
```
Ganti `username` dengan username Hostinger kamu.

---

## Status Phase 1 (File ini)
- [x] Auth (login, logout, session)
- [x] Dashboard Overview (stats real dari DB)
- [x] Executive Command Center
- [x] Database schema lengkap (20 tabel)
- [x] Layout sidebar Tailwind + AlpineJS
- [ ] Request Management → Phase 2
- [ ] Assignment System → Phase 2
- [ ] Workload Dashboard → Phase 3
- [ ] AI Module → Phase 4
