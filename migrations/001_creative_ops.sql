-- ============================================================
-- CREATIVE OPS CONTROL TOWER — Database Migration v1.0
-- Import via phpMyAdmin: pilih database dulu, lalu import file ini
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS co_departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    head_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT UNSIGNED NULL,
    lead_user_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','creative_manager','designer','video_editor','requester','viewer') NOT NULL DEFAULT 'requester',
    department_id INT UNSIGNED NULL,
    team_id INT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    capacity_hours_per_week DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    avatar VARCHAR(255) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_team_members (
    team_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NULL,
    priority_level ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
    business_importance INT NOT NULL DEFAULT 50,
    monthly_budget DECIMAL(15,2) NOT NULL DEFAULT 0,
    target_revenue DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    importance INT NOT NULL DEFAULT 50,
    status ENUM('planning','active','ended') NOT NULL DEFAULT 'planning',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(20) NOT NULL,
    requester_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NULL,
    product_id INT UNSIGNED NULL,
    campaign_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    objective TEXT NULL,
    brief TEXT NULL,
    copywriting TEXT NULL,
    reference_link VARCHAR(500) NULL,
    asset_type ENUM('image','video','carousel','reels','story','thumbnail','banner','landing_page_asset') NOT NULL,
    priority ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
    estimated_effort ENUM('small','medium','large') NOT NULL DEFAULT 'medium',
    deadline DATE NOT NULL,
    status ENUM('draft','waiting_queue','assigned','in_progress','revision','ready_review','approved','completed','cancelled','rejected') NOT NULL DEFAULT 'draft',
    priority_score INT NOT NULL DEFAULT 0,
    revision_count INT NOT NULL DEFAULT 0,
    is_late TINYINT(1) NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    cancelled_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_request_number (request_number),
    INDEX idx_status (status),
    INDEX idx_deadline (deadline),
    INDEX idx_priority_score (priority_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_attachments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    filetype VARCHAR(50) NULL,
    filesize INT NOT NULL DEFAULT 0,
    uploaded_by INT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_status_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    changed_by INT UNSIGNED NOT NULL,
    notes TEXT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    assignee_id INT UNSIGNED NOT NULL,
    assignee_role ENUM('designer','video_editor') NOT NULL,
    assigned_by INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_request (request_id),
    INDEX idx_assignee (assignee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_revisions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    revision_number INT NOT NULL,
    requester_comment TEXT NULL,
    designer_response TEXT NULL,
    requested_by INT UNSIGNED NULL,
    responded_by INT UNSIGNED NULL,
    status ENUM('pending','in_progress','resolved') NOT NULL DEFAULT 'pending',
    sla_hours INT NOT NULL DEFAULT 24,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    is_internal TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_request_files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    revision_number INT NOT NULL DEFAULT 0,
    file_type ENUM('draft','revision','final') NOT NULL DEFAULT 'draft',
    filename VARCHAR(255) NULL,
    filepath VARCHAR(500) NULL,
    gdrive_url VARCHAR(500) NULL,
    gdrive_file_id VARCHAR(100) NULL,
    thumbnail_url VARCHAR(500) NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_sla_rules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    priority ENUM('critical','high','medium','low') NOT NULL,
    estimated_effort ENUM('small','medium','large') NOT NULL,
    sla_hours INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_priority_effort (priority, estimated_effort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_sla_tracking (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_id INT UNSIGNED NOT NULL,
    sla_hours INT NULL,
    requested_at TIMESTAMP NULL,
    assigned_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    submitted_review_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    is_late TINYINT(1) NOT NULL DEFAULT 0,
    late_by_hours INT NOT NULL DEFAULT 0,
    turnaround_hours INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_workload_snapshots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    snapshot_date DATE NOT NULL,
    active_tasks INT NOT NULL DEFAULT 0,
    overdue_tasks INT NOT NULL DEFAULT 0,
    tasks_today INT NOT NULL DEFAULT 0,
    tasks_this_week INT NOT NULL DEFAULT 0,
    estimated_hours_active DECIMAL(8,2) NOT NULL DEFAULT 0,
    capacity_hours DECIMAL(8,2) NOT NULL DEFAULT 40,
    workload_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    utilization_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_user_date (user_id, snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_designer_kpi_monthly (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    year_month VARCHAR(7) NOT NULL,
    tasks_assigned INT NOT NULL DEFAULT 0,
    tasks_completed INT NOT NULL DEFAULT 0,
    tasks_on_time INT NOT NULL DEFAULT 0,
    tasks_late INT NOT NULL DEFAULT 0,
    total_revisions INT NOT NULL DEFAULT 0,
    completion_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    sla_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    revision_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    productivity_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    creative_score DECIMAL(5,2) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_user_month (user_id, year_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NULL,
    message TEXT NULL,
    request_id INT UNSIGNED NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    sent_wa TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_alert_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alert_type VARCHAR(50) NOT NULL,
    request_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dedup (alert_type, request_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_ai_insights (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    insight_type ENUM('bottleneck','performance','product','requester','forecast','priority_rec','team_ranking') NOT NULL,
    title VARCHAR(255) NULL,
    insight_text TEXT NOT NULL,
    data_snapshot TEXT NULL,
    recommendation TEXT NULL,
    severity ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_deterministic TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_type_expires (insight_type, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_ai_daily_briefings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    briefing_date DATE NOT NULL,
    total_tasks_today INT NOT NULL DEFAULT 0,
    critical_tasks INT NOT NULL DEFAULT 0,
    overdue_tasks INT NOT NULL DEFAULT 0,
    designer_overload_count INT NOT NULL DEFAULT 0,
    editor_overload_count INT NOT NULL DEFAULT 0,
    top_priorities TEXT NULL,
    briefing_text TEXT NULL,
    recommendations TEXT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_briefing_date (briefing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_ai_chat_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role ENUM('user','assistant') NOT NULL,
    message TEXT NOT NULL,
    data_context TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_ai_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type ENUM('daily','weekly','monthly') NOT NULL,
    period_label VARCHAR(20) NOT NULL,
    report_data TEXT NULL,
    narrative TEXT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_type_period (report_type, period_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS co_ai_chat_ratelimit (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    request_date DATE NOT NULL,
    request_count INT NOT NULL DEFAULT 1,
    UNIQUE KEY uq_user_date (user_id, request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEEDERS
-- ============================================================

INSERT IGNORE INTO co_sla_rules (priority, estimated_effort, sla_hours) VALUES
('critical', 'small',  24),
('critical', 'medium', 48),
('critical', 'large',  72),
('high',     'small',  48),
('high',     'medium', 72),
('high',     'large',  96),
('medium',   'small',  72),
('medium',   'medium', 96),
('medium',   'large',  120),
('low',      'small',  96),
('low',      'medium', 120),
('low',      'large',  168);

INSERT IGNORE INTO co_departments (name) VALUES ('Creative Division');

-- Default admin: email = admin@creative-ops.local | password = Admin@2026
-- Hash: password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost' => 10])
INSERT IGNORE INTO co_users (name, email, password, role, is_active, department_id) VALUES
('Super Admin', 'admin@creative-ops.local', '$2y$10$VeUVT7jW6c0kqnepWsGq/uGHEzu8MpjkBQNoqMosllHsqmIQBJRUm', 'super_admin', 1, 1);

-- WAJIB: Ganti password setelah login pertama via menu profile!
