-- Email Verification Database Schema
-- Run these queries in your MySQL database to enable email verification

-- 1. Add is_verified column to users table if it doesn't exist
ALTER TABLE users 
ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER password,
ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER is_verified,
ADD COLUMN login_attempts INT DEFAULT 0 AFTER is_active,
ADD COLUMN last_login_at DATETIME NULL AFTER login_attempts,
ADD COLUMN last_login_ip VARCHAR(45) NULL AFTER last_login_at;

-- 2. Create email_verifications table
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_token (user_id, token),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create user_logs table (if not exists)
CREATE TABLE IF NOT EXISTS user_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Set existing users as verified (optional - remove if you want to verify all)
-- UPDATE users SET is_verified = 1 WHERE is_verified = 0;
