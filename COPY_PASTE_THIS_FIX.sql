-- =========================================================
-- COPY AND PASTE THIS ENTIRE FILE INTO PHPMYADMIN
-- Select webdev database first, then paste this in SQL tab
-- =========================================================

USE webdev;

-- Step 1: Add the missing columns (ignore errors if they already exist)
ALTER TABLE legal_documents ADD COLUMN cert_registration_status ENUM('pending','approved','flagged') DEFAULT 'pending';
ALTER TABLE legal_documents ADD COLUMN cert_registration_comment TEXT;
ALTER TABLE legal_documents ADD COLUMN cert_registration_checked TINYINT(1) DEFAULT 0;

ALTER TABLE legal_documents ADD COLUMN mayors_permit_status ENUM('pending','approved','flagged') DEFAULT 'pending';
ALTER TABLE legal_documents ADD COLUMN mayors_permit_comment TEXT;
ALTER TABLE legal_documents ADD COLUMN mayors_permit_checked TINYINT(1) DEFAULT 0;

ALTER TABLE legal_documents ADD COLUMN business_name_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending';
ALTER TABLE legal_documents ADD COLUMN business_name_cert_comment TEXT;
ALTER TABLE legal_documents ADD COLUMN business_name_cert_checked TINYINT(1) DEFAULT 0;

ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending';
ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_comment TEXT;
ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_checked TINYINT(1) DEFAULT 0;

-- Step 2: Set the correct statuses for document ID 39 (based on your screenshot)
UPDATE legal_documents 
SET 
    cert_registration_status = 'approved',
    cert_registration_checked = 1,
    mayors_permit_status = 'approved',
    mayors_permit_checked = 1,
    business_name_cert_status = 'flagged',
    business_name_cert_comment = 'Flagged for resubmission',
    fire_safety_cert_status = 'flagged',
    fire_safety_cert_comment = 'Flagged for resubmission'
WHERE id = 39;

-- Step 3: Check the result
SELECT 
    id,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents 
WHERE id = 39;
