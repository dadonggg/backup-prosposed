-- Phase 1: Service Selection & Payment System
-- Add columns to membership_applications table for service selection and payment tracking

-- Add service_id column to link to gym_services
ALTER TABLE membership_applications
ADD COLUMN service_id INT NULL AFTER payment_type,
ADD CONSTRAINT fk_membership_service FOREIGN KEY (service_id) REFERENCES gym_services(id) ON DELETE SET NULL;

-- Add payment mode and status columns
ALTER TABLE membership_applications
ADD COLUMN payment_mode ENUM('cash','online') DEFAULT 'cash' AFTER payment_amount,
ADD COLUMN payment_status ENUM('pending','paid','failed') DEFAULT 'pending' AFTER payment_mode,
ADD COLUMN payment_reference VARCHAR(255) NULL AFTER payment_status,
ADD COLUMN paymongo_payment_id VARCHAR(255) NULL AFTER payment_reference,
ADD COLUMN paid_at DATETIME NULL AFTER paymongo_payment_id;

-- Add gym_owner_id to membership_applications to track which gym they're applying to
ALTER TABLE membership_applications
ADD COLUMN gym_owner_id INT NULL AFTER user_id,
ADD CONSTRAINT fk_membership_gym_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE SET NULL;

-- Create PayMongo configuration table
CREATE TABLE IF NOT EXISTS paymongo_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,
    public_key VARCHAR(255) NOT NULL,
    secret_key VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_paymongo_gym_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_gym_owner (gym_owner_id)
) ENGINE=InnoDB;

-- Create payment transactions table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_application_id INT NOT NULL,
    gym_owner_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_mode ENUM('cash','online') NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    paymongo_payment_id VARCHAR(255) NULL,
    paymongo_source_id VARCHAR(255) NULL,
    payment_reference VARCHAR(255) NULL,
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_membership FOREIGN KEY (membership_application_id) REFERENCES membership_applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_gym_owner FOREIGN KEY (gym_owner_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_payment_status (payment_status),
    KEY idx_paymongo_id (paymongo_payment_id)
) ENGINE=InnoDB;
