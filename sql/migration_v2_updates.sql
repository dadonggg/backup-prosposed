-- =========================================================
-- Migration V2: Registration, Permits, Financial, Inventory
-- Run this in phpMyAdmin (Import tab) or MySQL CLI.
-- =========================================================

USE webdev;

-- ---------------------------------------------------------
-- 1. Registration: Replace "age" with "birth_date"
-- ---------------------------------------------------------
ALTER TABLE users ADD COLUMN birth_date DATE NULL DEFAULT NULL AFTER middle_initial;

-- Keep the old age column for backward compatibility; new registrations use birth_date
-- UPDATE users SET birth_date = DATE_SUB(CURDATE(), INTERVAL age YEAR) WHERE age IS NOT NULL AND birth_date IS NULL;

-- ---------------------------------------------------------
-- 2. Permit Review: Per-document status, checklist, comments
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    ADD COLUMN cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER cert_registration,
    ADD COLUMN cert_registration_comment TEXT DEFAULT NULL AFTER cert_registration_status,
    ADD COLUMN cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment,

    ADD COLUMN mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER mayors_permit,
    ADD COLUMN mayors_permit_comment TEXT DEFAULT NULL AFTER mayors_permit_status,
    ADD COLUMN mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment,

    ADD COLUMN business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER business_name_cert,
    ADD COLUMN business_name_cert_comment TEXT DEFAULT NULL AFTER business_name_cert_status,
    ADD COLUMN business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment,

    ADD COLUMN fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER fire_safety_cert,
    ADD COLUMN fire_safety_cert_comment TEXT DEFAULT NULL AFTER fire_safety_cert_status,
    ADD COLUMN fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment;

-- ---------------------------------------------------------
-- 3. Financial: Rename budget→investment, add operational_expenses, revenue, monthly_profit
-- ---------------------------------------------------------

-- Update record_type ENUM to include 'investment' and 'operational_expense'
ALTER TABLE financial_records
    MODIFY COLUMN record_type ENUM('budget','expense','investment','investment_usage','operational_expense','revenue') NOT NULL,
    ADD COLUMN month_year VARCHAR(7) DEFAULT NULL AFTER notes;

-- ---------------------------------------------------------
-- 4. Equipment Inventory: Replace buy concept with listing
-- ---------------------------------------------------------

-- Add new columns to gym_equipment for detailed listing
ALTER TABLE gym_equipment
    ADD COLUMN brand VARCHAR(255) DEFAULT NULL AFTER category,
    ADD COLUMN dimensions VARCHAR(255) DEFAULT NULL AFTER brand,
    ADD COLUMN weight_kg DECIMAL(8,2) DEFAULT NULL AFTER dimensions,
    ADD COLUMN quantity INT NOT NULL DEFAULT 0 AFTER weight_kg,
    ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER quantity,
    ADD COLUMN listed_by INT DEFAULT NULL AFTER image_path,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER listed_by;

-- Make supplier_id nullable since gym owners list their own equipment
ALTER TABLE gym_equipment MODIFY COLUMN supplier_id INT DEFAULT NULL;

-- Drop the foreign key constraint on supplier_id so it can be nullable
-- Note: The constraint name may vary; if this fails, check your constraint name
-- ALTER TABLE gym_equipment DROP FOREIGN KEY fk_equip_supplier;
-- ALTER TABLE gym_equipment ADD CONSTRAINT fk_equip_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL;

