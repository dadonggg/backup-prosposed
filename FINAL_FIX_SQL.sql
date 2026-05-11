-- =========================================================
-- FINAL FIX - Per-Document Status System
-- This SQL will ensure the system works correctly
-- Run this in phpMyAdmin SQL tab
-- =========================================================

USE webdev;

-- Step 1: Add all required columns (ignore duplicate column errors)
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

-- Step 2: Set default values for existing records
UPDATE legal_documents 
SET 
    cert_registration_status = COALESCE(cert_registration_status, 'pending'),
    mayors_permit_status = COALESCE(mayors_permit_status, 'pending'),
    business_name_cert_status = COALESCE(business_name_cert_status, 'pending'),
    fire_safety_cert_status = COALESCE(fire_safety_cert_status, 'pending'),
    cert_registration_checked = COALESCE(cert_registration_checked, 0),
    mayors_permit_checked = COALESCE(mayors_permit_checked, 0),
    business_name_cert_checked = COALESCE(business_name_checked, 0),
    fire_safety_cert_checked = COALESCE(fire_safety_cert_checked, 0);

-- Step 3: Verify the structure
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'webdev' 
AND TABLE_NAME = 'legal_documents'
AND (COLUMN_NAME LIKE '%_status' OR COLUMN_NAME LIKE '%_comment' OR COLUMN_NAME LIKE '%_checked')
ORDER BY ORDINAL_POSITION;

-- Step 4: Show current document statuses
SELECT 
    id,
    user_id,
    status as overall_status,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents
ORDER BY id DESC
LIMIT 10;

-- =========================================================
-- DONE! Now test the system:
-- 
-- 1. Go to: http://localhost/webdev/COMPLETE_DIAGNOSTIC_AND_FIX.php
--    This will show you if everything is working
--
-- 2. Login as admin
-- 3. Go to Legal Document Reviews
-- 4. Click on an application
-- 5. Click "Approve" or "Flag Issue" on a document
-- 6. You should see success message
-- 7. Login as gym owner
-- 8. Go to Apply as Gym Owner page
-- 9. Press Ctrl + Shift + R (hard refresh)
-- 10. You should see the correct status colors!
-- =========================================================
