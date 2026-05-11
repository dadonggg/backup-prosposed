A-- =========================================================
-- INSTANT FIX - Run this RIGHT NOW in phpMyAdmin
-- This will immediately fix the document status display
-- =========================================================

USE webdev;

-- First, let's see what we have (for document ID 39)
SELECT 
    id,
    status as overall_status,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents 
WHERE id = 39;

-- Now let's check if the status columns even exist
SELECT COLUMN_NAME 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'webdev' 
AND TABLE_NAME = 'legal_documents'
AND COLUMN_NAME LIKE '%_status';

-- If the columns don't exist, add them NOW
ALTER TABLE legal_documents 
ADD COLUMN IF NOT EXISTS cert_registration_status ENUM('pending','approved','flagged') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS cert_registration_comment TEXT,
ADD COLUMN IF NOT EXISTS cert_registration_checked TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS mayors_permit_status ENUM('pending','approved','flagged') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS mayors_permit_comment TEXT,
ADD COLUMN IF NOT EXISTS mayors_permit_checked TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS business_name_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS business_name_cert_comment TEXT,
ADD COLUMN IF NOT EXISTS business_name_cert_checked TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS fire_safety_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS fire_safety_cert_comment TEXT,
ADD COLUMN IF NOT EXISTS fire_safety_cert_checked TINYINT(1) DEFAULT 0;

-- Based on your screenshot, set the correct statuses for document ID 39
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

-- Verify the fix
SELECT 
    id,
    status as overall_status,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    business_name_cert_comment,
    fire_safety_cert_comment
FROM legal_documents 
WHERE id = 39;

-- =========================================================
-- DONE! Now:
-- 1. Go to gym owner application page
-- 2. Press Ctrl + Shift + R (hard refresh)
-- 3. You should see:
--    - Certificate of Registration: GREEN (Approved)
--    - Mayor's Permit: GREEN (Approved)
--    - Business Name Certificate: RED (Flagged) with Resubmit button
--    - Fire Safety Certificate: RED (Flagged) with Resubmit button
-- =========================================================
