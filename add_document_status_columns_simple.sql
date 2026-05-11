-- =========================================================
-- Add Per-Document Status Columns to legal_documents Table
-- Simple version for older MySQL (without IF NOT EXISTS)
-- Run this in phpMyAdmin SQL tab
-- =========================================================

USE webdev;

-- Certificate of Registration columns
ALTER TABLE legal_documents
    ADD COLUMN cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER cert_registration;

ALTER TABLE legal_documents
    ADD COLUMN cert_registration_comment TEXT DEFAULT NULL AFTER cert_registration_status;

ALTER TABLE legal_documents
    ADD COLUMN cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment;

-- Mayor's Permit columns
ALTER TABLE legal_documents
    ADD COLUMN mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER mayors_permit;

ALTER TABLE legal_documents
    ADD COLUMN mayors_permit_comment TEXT DEFAULT NULL AFTER mayors_permit_status;

ALTER TABLE legal_documents
    ADD COLUMN mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment;

-- Business Name Certificate columns
ALTER TABLE legal_documents
    ADD COLUMN business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER business_name_cert;

ALTER TABLE legal_documents
    ADD COLUMN business_name_cert_comment TEXT DEFAULT NULL AFTER business_name_cert_status;

ALTER TABLE legal_documents
    ADD COLUMN business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment;

-- Fire Safety Certificate columns
ALTER TABLE legal_documents
    ADD COLUMN fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER fire_safety_cert;

ALTER TABLE legal_documents
    ADD COLUMN fire_safety_cert_comment TEXT DEFAULT NULL AFTER fire_safety_cert_status;

ALTER TABLE legal_documents
    ADD COLUMN fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment;
