-- =========================================================
-- Nutrify – Complete Database Setup
-- Run this file in phpMyAdmin (Import tab) or MySQL CLI.
-- It will create the database and ALL tables needed.
-- =========================================================

CREATE DATABASE IF NOT EXISTS webdev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webdev;

-- ---------------------------------------------------------
-- 1. Users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL DEFAULT '',
    lastname VARCHAR(50) NOT NULL DEFAULT '',
    middle_initial VARCHAR(5) NULL DEFAULT NULL,
    age TINYINT UNSIGNED NULL DEFAULT NULL,
    height_cm DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL,
    weight_kg DECIMAL(5,2) UNSIGNED NULL DEFAULT NULL,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'customer',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Add `role` column if the table already existed without it
-- (safe to run multiple times – silently ignored if column exists)
ALTER TABLE users ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT 'customer' AFTER password;
-- If the column already exists, the above will produce a warning that can be ignored.

-- ---------------------------------------------------------
-- 2. Email verifications
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_email_verification_token (token),
    KEY idx_email_verifications_user_id (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. OTP codes
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_otp_codes_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_otp_codes_user_id (user_id),
    KEY idx_otp_codes_expires (expires_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. Legal documents (gym owner applications)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS legal_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cert_registration VARCHAR(500) DEFAULT NULL,
    mayors_permit VARCHAR(500) DEFAULT NULL,
    business_name_cert VARCHAR(500) DEFAULT NULL,
    fire_safety_cert VARCHAR(500) DEFAULT NULL,
    status ENUM('pending','verified','resubmit') NOT NULL DEFAULT 'pending',
    admin_feedback TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_legal_docs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_legal_docs_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. Staff applications
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_type ENUM('maintenance','trainer') NOT NULL,
    medical_certificate VARCHAR(500) DEFAULT NULL,
    resume VARCHAR(500) DEFAULT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    feedback TEXT DEFAULT NULL,
    reviewer_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_staff_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_staff_app_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. Employees (hired staff)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position VARCHAR(50) NOT NULL,
    hired_by INT DEFAULT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    hired_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_employees_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. Financial records (budget & expenses)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS financial_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    record_type ENUM('budget','expense') NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    category VARCHAR(100) DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fin_records_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_fin_records_owner (gym_owner_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. Suppliers
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    contact_info VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 9. Gym equipment catalogue
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS gym_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    description TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equip_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    KEY idx_equip_supplier (supplier_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 10. Gym inventory (purchased equipment)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS gym_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    equipment_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inv_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_inv_equip FOREIGN KEY (equipment_id) REFERENCES gym_equipment(id) ON DELETE CASCADE,
    KEY idx_inv_owner (gym_owner_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 11. Membership applications
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS membership_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_initial VARCHAR(5) DEFAULT NULL,
    phone_number VARCHAR(20) NOT NULL,
    preferred_trainer_id INT DEFAULT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_feedback TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_memapp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_memapp_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 12. Gym members (approved memberships)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS gym_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_id INT NOT NULL,
    membership_code VARCHAR(30) NOT NULL UNIQUE,
    assigned_trainer_id INT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_gymmem_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_gymmem_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 13. Attendance log
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    membership_code VARCHAR(30) NOT NULL,
    check_in DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attend_member FOREIGN KEY (member_id) REFERENCES gym_members(id) ON DELETE CASCADE,
    KEY idx_attend_member (member_id)
) ENGINE=InnoDB;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Seed: Admin user (Administrative Officer)
-- Email: admin@nutrify.com | Password: Admin@1234
INSERT INTO users (firstname, lastname, fullname, email, password, role, is_verified)
VALUES ('Admin', 'Officer', 'Admin Officer', 'admin@nutrify.com',
        '$2y$10$ox.YO5IpKu0a7ZffPX.yzO72sVRajQnJLxHmVZ7LSCQjgGMp0aJ4a',
        'admin', 1)
ON DUPLICATE KEY UPDATE role = 'admin';

-- Seed: Sample suppliers
INSERT INTO suppliers (name, contact_info) VALUES
    ('FitGear Philippines',   'fitgear@example.com'),
    ('IronWorks Equipment',   'ironworks@example.com'),
    ('GymTech Supplies',      'gymtech@example.com')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Seed: Sample gym equipment
INSERT INTO gym_equipment (supplier_id, name, category, price, description) VALUES
    (1, 'Olympic Barbell',      'Weights',     3500.00, '20kg Olympic barbell, chrome finish'),
    (1, 'Rubber Dumbbells 5kg', 'Weights',     1200.00, 'Pair of 5kg rubber hex dumbbells'),
    (1, 'Weight Plates 20kg',   'Weights',     2800.00, 'Pair of 20kg rubber bumper plates'),
    (2, 'Flat Bench Press',     'Benches',     8500.00, 'Commercial flat bench press with rack'),
    (2, 'Adjustable Bench',     'Benches',     6500.00, 'FID adjustable bench'),
    (2, 'Squat Rack',           'Racks',      15000.00, 'Heavy-duty squat rack with safety bars'),
    (3, 'Treadmill Pro X500',   'Cardio',     45000.00, 'Commercial treadmill with LCD display'),
    (3, 'Stationary Bike',      'Cardio',     18000.00, 'Indoor cycling bike, adjustable resistance'),
    (3, 'Rowing Machine',       'Cardio',     22000.00, 'Air resistance rowing machine')
ON DUPLICATE KEY UPDATE name = VALUES(name);
