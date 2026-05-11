-- =========================================================
-- Migration V6: Connect Admin Decisions → Applicant Status View
-- PURPOSE: Ensures per-document status columns exist in legal_documents
--          so admin approve/flag actions are visible to the applicant.
-- Run this ONCE in phpMyAdmin → Import tab.
-- Safe to run on existing databases (uses ALTER IGNORE / IF NOT EXISTS).
-- =========================================================

USE webdev;

-- ---------------------------------------------------------
-- 1. Ensure per-document status columns exist (cert_registration)
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    MODIFY COLUMN status ENUM('pending','verified','resubmit','rejected') NOT NULL DEFAULT 'pending';

-- Add cert_registration per-doc columns if missing
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS cert_registration_status  ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER cert_registration,
    ADD COLUMN IF NOT EXISTS cert_registration_comment  TEXT NULL                                                       AFTER cert_registration_status,
    ADD COLUMN IF NOT EXISTS cert_registration_checked  TINYINT(1) NOT NULL DEFAULT 0                                  AFTER cert_registration_comment;

-- Add mayors_permit per-doc columns if missing
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS mayors_permit_status   ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER mayors_permit,
    ADD COLUMN IF NOT EXISTS mayors_permit_comment  TEXT NULL                                                       AFTER mayors_permit_status,
    ADD COLUMN IF NOT EXISTS mayors_permit_checked  TINYINT(1) NOT NULL DEFAULT 0                                  AFTER mayors_permit_comment;

-- Add business_name_cert per-doc columns if missing
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS business_name_cert_status   ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER business_name_cert,
    ADD COLUMN IF NOT EXISTS business_name_cert_comment  TEXT NULL                                                       AFTER business_name_cert_status,
    ADD COLUMN IF NOT EXISTS business_name_cert_checked  TINYINT(1) NOT NULL DEFAULT 0                                  AFTER business_name_cert_comment;

-- Add fire_safety_cert per-doc columns if missing
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS fire_safety_cert_status   ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER fire_safety_cert,
    ADD COLUMN IF NOT EXISTS fire_safety_cert_comment  TEXT NULL                                                       AFTER fire_safety_cert_status,
    ADD COLUMN IF NOT EXISTS fire_safety_cert_checked  TINYINT(1) NOT NULL DEFAULT 0                                  AFTER fire_safety_cert_comment;

-- ---------------------------------------------------------
-- 2. Add gym info columns if missing (submitted during application)
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS gym_name         VARCHAR(255) NULL AFTER user_id,
    ADD COLUMN IF NOT EXISTS gym_logo         VARCHAR(500) NULL AFTER gym_name,
    ADD COLUMN IF NOT EXISTS gym_address      TEXT NULL         AFTER gym_logo,
    ADD COLUMN IF NOT EXISTS maintenance_count INT DEFAULT 0    AFTER gym_address,
    ADD COLUMN IF NOT EXISTS trainer_count     INT DEFAULT 0    AFTER maintenance_count;

-- ---------------------------------------------------------
-- 3. Add admin_feedback column if missing
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS admin_feedback TEXT NULL;

-- ---------------------------------------------------------
-- 4. Ensure updated_at exists for change tracking
-- ---------------------------------------------------------
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ---------------------------------------------------------
-- 5. Verify — show all relevant columns
-- ---------------------------------------------------------
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'webdev'
  AND TABLE_NAME   = 'legal_documents'
ORDER BY ORDINAL_POSITION;

SELECT 'Migration V6 complete! Per-document status columns are now connected.' AS result;
