CREATE DATABASE IF NOT EXISTS webdev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webdev;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL DEFAULT '',
    lastname VARCHAR(50) NOT NULL DEFAULT '',
    middle_initial VARCHAR(5) NULL DEFAULT NULL,
    age TINYINT UNSIGNED NULL DEFAULT NULL,
    height_cm DECIMAL(5, 2) UNSIGNED NULL DEFAULT NULL,
    weight_kg DECIMAL(5, 2) UNSIGNED NULL DEFAULT NULL,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    UNIQUE KEY uniq_email_verification_token (token),
    KEY idx_email_verifications_user_id (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_otp_codes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    KEY idx_otp_codes_user_id (user_id),
    KEY idx_otp_codes_expires (expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gym_owner_id INT NULL,
    application_type ENUM('maintenance','trainer') NOT NULL,
    medical_certificate VARCHAR(500) NOT NULL,
    medical_certificate_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    medical_certificate_comment TEXT NULL,
    medical_certificate_checked TINYINT(1) NOT NULL DEFAULT 0,
    resume VARCHAR(500) NOT NULL,
    resume_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    resume_comment TEXT NULL,
    resume_checked TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending','approved','rejected','resubmit') NOT NULL DEFAULT 'pending',
    reviewer_id INT NULL,
    feedback TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_staff_app_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_staff_app_gym_owner
        FOREIGN KEY (gym_owner_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_staff_app_reviewer
        FOREIGN KEY (reviewer_id) REFERENCES users(id)
        ON DELETE SET NULL,
    KEY idx_staff_app_user (user_id),
    KEY idx_staff_app_gym_owner (gym_owner_id),
    KEY idx_staff_app_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS legal_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gym_name VARCHAR(255) NULL,
    gym_logo VARCHAR(500) NULL,
    gym_address TEXT NULL,
    maintenance_count INT DEFAULT 0,
    trainer_count INT DEFAULT 0,
    cert_registration VARCHAR(500) NOT NULL,
    cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    cert_registration_comment TEXT NULL,
    cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0,
    mayors_permit VARCHAR(500) NOT NULL,
    mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    mayors_permit_comment TEXT NULL,
    mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0,
    business_name_cert VARCHAR(500) NOT NULL,
    business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    business_name_cert_comment TEXT NULL,
    business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0,
    fire_safety_cert VARCHAR(500) NOT NULL,
    fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    fire_safety_cert_comment TEXT NULL,
    fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending','verified','resubmit','rejected') NOT NULL DEFAULT 'pending',
    admin_feedback TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_legal_docs_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    KEY idx_legal_docs_user (user_id),
    KEY idx_legal_docs_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position ENUM('maintenance','trainer') NOT NULL,
    hired_by INT NOT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    hired_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_employee_hired_by
        FOREIGN KEY (hired_by) REFERENCES users(id)
        ON DELETE CASCADE,
    KEY idx_employee_user (user_id),
    KEY idx_employee_position (position)
) ENGINE=InnoDB;
