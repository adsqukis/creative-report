-- ============================================================
-- CREATIVE OPS — Migration 002
-- co_settings table + performance indexes
--
-- NOTE: "ADD INDEX IF NOT EXISTS" (MariaDB-only syntax) was removed.
-- Plain MySQL (used by Railway's MySQL plugin) doesn't support it.
-- This file is meant to run exactly once per database — the migrate.php
-- runner tracks that via a co_schema_migrations table, so it's safe
-- without the IF NOT EXISTS guard.
-- ============================================================

CREATE TABLE IF NOT EXISTS co_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO co_settings (setting_key, setting_value) VALUES
('fonnte_token',    ''),
('fonnte_group_id', ''),
('wa_enabled',      '0'),
('app_name',        'Creative Ops');

-- Performance indexes
ALTER TABLE co_requests
    ADD INDEX idx_requester      (requester_id),
    ADD INDEX idx_product        (product_id),
    ADD INDEX idx_campaign       (campaign_id),
    ADD INDEX idx_created_month  (created_at),
    ADD INDEX idx_completed      (completed_at),
    ADD INDEX idx_is_late        (is_late);

ALTER TABLE co_request_status_log
    ADD INDEX idx_changed_at (changed_at);

ALTER TABLE co_notifications
    ADD INDEX idx_created (created_at);

ALTER TABLE co_workload_snapshots
    ADD INDEX idx_date (snapshot_date);

ALTER TABLE co_designer_kpi_monthly
    ADD INDEX idx_year_month (year_month);
