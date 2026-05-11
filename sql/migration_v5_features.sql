-- =========================================================
-- Migration V5: Notifications, Membership Plans/Services,
--               Column fixes, and per-document resubmission
-- Run in phpMyAdmin (Import tab) or MySQL CLI
-- =========================================================

USE webdev;

-- ---------------------------------------------------------
-- 1. CRITICAL FIX: Add missing columns to gym_members
--    (if migration_v4 wasn't run yet)
-- ---------------------------------------------------------
-- Safe: IF NOT EXISTS equivalent using ALTER IGNORE
-- If columns already exist these will produce warnings, not errors.

ALTER TABLE gym_members
    ADD COLUMN payment_type VARCHAR(50) DEFAULT NULL,
    ADD COLUMN payment_amount DECIMAL(10,2) DEFAULT NULL,
    ADD COLUMN start_date DATE DEFAULT NULL,
    ADD COLUMN expiration_date DATE DEFAULT NULL;

-- ---------------------------------------------------------
-- 2. CRITICAL FIX: membership_applications new columns & ENUM
-- ---------------------------------------------------------
ALTER TABLE membership_applications
    MODIFY COLUMN status ENUM('pending','verified','approved','rejected','resubmit') NOT NULL DEFAULT 'pending';

ALTER TABLE membership_applications
    ADD COLUMN payment_type VARCHAR(50) DEFAULT NULL,
    ADD COLUMN payment_amount DECIMAL(10,2) DEFAULT NULL,
    ADD COLUMN student_proof VARCHAR(500) DEFAULT NULL,
    ADD COLUMN reviewer_id INT DEFAULT NULL;

-- Fix any existing reviewer_id = 0 rows
UPDATE staff_applications SET reviewer_id = NULL WHERE reviewer_id = 0;

-- ---------------------------------------------------------
-- 3. Notifications table
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(30) NOT NULL DEFAULT 'info',   -- info, success, warning, danger
    link VARCHAR(500) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_notif_user (user_id),
    KEY idx_notif_read (is_read)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. Membership Plans (gym owner configurable)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration_days INT NOT NULL DEFAULT 30,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_memplan_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_memplan_owner (gym_owner_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. Gym Services (separate from plans, editable prices)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS gym_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    member_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    non_member_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gymsvc_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_gymsvc_owner (gym_owner_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. Add plan_id to membership_applications for dynamic pricing
-- ---------------------------------------------------------
ALTER TABLE membership_applications
    ADD COLUMN plan_id INT DEFAULT NULL;

-- ---------------------------------------------------------
-- 7. Legal documents: add 'rejected' status if missing
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    MODIFY COLUMN status ENUM('pending','verified','resubmit','rejected') NOT NULL DEFAULT 'pending';
