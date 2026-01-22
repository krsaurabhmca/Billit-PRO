-- ============================================================================
-- EMAIL AND LOGGING EXTENSION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: smtp_settings
-- Purpose: Store SMTP configuration for sending emails
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS smtp_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    host VARCHAR(255) NOT NULL,
    port INT(5) NOT NULL DEFAULT 587,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    encryption ENUM('tls', 'ssl', 'none') NOT NULL DEFAULT 'tls',
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default placeholder settings
INSERT INTO smtp_settings (host, port, username, password, from_email, from_name, status)
SELECT 'smtp.gmail.com', 587, 'your-email@gmail.com', '', 'noreply@billit.com', 'Billit Notification', 'active'
WHERE NOT EXISTS (SELECT * FROM smtp_settings);

-- ----------------------------------------------------------------------------
-- Table: access_logs
-- Purpose: Track user login/logout and critical actions
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS access_logs (
    log_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11),
    username VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: password_resets
-- Purpose: Store password reset tokens
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
