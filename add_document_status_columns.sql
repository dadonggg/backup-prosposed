-- =========================================================
-- Add Per-Document Status Columns to legal_documents Table
-- Run this in phpMyAdmin SQL tab or MySQL CLI
-- =========================================================

USE webdev;

-- Add status, comment, and checked columns for Certificate of Registration
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER cert_registration,
    ADD COLUMN IF NOT EXISTS cert_registration_comment TEXT DEFAULT NULL AFTER cert_registration_status,
    ADD COLUMN IF NOT EXISTS cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment;

-- Add status, comment, and checked columns for Mayor's Permit
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER mayors_permit,
    ADD COLUMN IF NOT EXISTS mayors_permit_comment TEXT DEFAULT NULL AFTER mayors_permit_status,
    ADD COLUMN IF NOT EXISTS mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment;

-- Add status, comment, and checked columns for Business Name Certificate
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER business_name_cert,
    ADD COLUMN IF NOT EXISTS business_name_cert_comment TEXT DEFAULT NULL AFTER business_name_cert_status,
    ADD COLUMN IF NOT EXISTS business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment;

-- Add status, comment, and checked columns for Fire Safety Certificate
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER fire_safety_cert,
    ADD COLUMN IF NOT EXISTS fire_safety_cert_comment TEXT DEFAULT NULL AFTER fire_safety_cert_status,
    ADD COLUMN IF NOT EXISTS fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment;

-- Verify the columns were added
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'webdev' 
AND TABLE_NAME = 'legal_documents'
AND COLUMN_NAME LIKE '%_status'
ORDER BY ORDINAL_POSITION;
