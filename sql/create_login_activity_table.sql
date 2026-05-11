-- ============================================
-- LOGIN/LOGOUT ACTIVITY TRACKING
-- Security Feature: Logging and Monitoring
-- ============================================
-- This table tracks all user login and logout activities
-- Helps with security auditing and compliance
-- ============================================

CREATE TABLE IF NOT EXISTS login_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    activity_type ENUM('login_success', 'login_failed', 'logout', 'otp_sent', 'otp_failed') NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    session_id VARCHAR(255) NULL,
    failure_reason VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at),
    
    CONSTRAINT fk_login_activity_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE QUERIES FOR VIEWING ACTIVITY
-- ============================================

-- View all login activities
-- SELECT * FROM login_activity ORDER BY created_at DESC LIMIT 50;

-- View failed login attempts
-- SELECT * FROM login_activity WHERE activity_type = 'login_failed' ORDER BY created_at DESC;

-- View activities for specific user
-- SELECT * FROM login_activity WHERE user_id = 1 ORDER BY created_at DESC;

-- Count login attempts by email (detect brute force)
-- SELECT email, COUNT(*) as attempts 
-- FROM login_activity 
-- WHERE activity_type = 'login_failed' 
-- AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
-- GROUP BY email 
-- HAVING attempts > 3;

-- View currently active sessions
-- SELECT la.*, u.fullname, u.role
-- FROM login_activity la
-- JOIN users u ON u.id = la.user_id
-- WHERE la.activity_type = 'login_success'
-- AND la.user_id NOT IN (
--     SELECT user_id FROM login_activity 
--     WHERE activity_type = 'logout' 
--     AND created_at > la.created_at
-- )
-- ORDER BY la.created_at DESC;

-- ============================================
-- DONE! Now update AuthController to log activities
-- ============================================
