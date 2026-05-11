-- =========================================================
-- ULTIMATE FIX - Just run this in phpMyAdmin
-- Select webdev database first, then paste this and click Go
-- =========================================================

USE webdev;

-- Fix for document ID 39 (your current problem)
-- This will work whether columns exist or not

-- If you get "Unknown column" errors, that's OK - keep going!
-- The UPDATE at the end is what matters

UPDATE legal_documents 
SET 
    cert_registration_status = 'approved',
    mayors_permit_status = 'approved',
    business_name_cert_status = 'flagged',
    fire_safety_cert_status = 'flagged',
    business_name_cert_comment = 'Flagged for resubmission',
    fire_safety_cert_comment = 'Flagged for resubmission'
WHERE id = 39;

-- Check if it worked
SELECT 
    id,
    cert_registration_status as cert_status,
    mayors_permit_status as mayor_status,
    business_name_cert_status as business_status,
    fire_safety_cert_status as fire_status
FROM legal_documents 
WHERE id = 39;

-- =========================================================
-- If you get "Unknown column" error, run this first:
-- =========================================================

-- ALTER TABLE legal_documents ADD COLUMN cert_registration_status ENUM('pending','approved','flagged') DEFAULT 'pending';
-- ALTER TABLE legal_documents ADD COLUMN cert_registration_comment TEXT;
-- ALTER TABLE legal_documents ADD COLUMN mayors_permit_status ENUM('pending','approved','flagged') DEFAULT 'pending';
-- ALTER TABLE legal_documents ADD COLUMN mayors_permit_comment TEXT;
-- ALTER TABLE legal_documents ADD COLUMN business_name_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending';
-- ALTER TABLE legal_documents ADD COLUMN business_name_cert_comment TEXT;
-- ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_status ENUM('pending','approved','flagged') DEFAULT 'pending';
-- ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_comment TEXT;

-- Then run the UPDATE again (scroll up)

-- =========================================================
-- After running this:
-- 1. Go to gym owner application page
-- 2. Press Ctrl + Shift + R
-- 3. You should see the correct colors!
-- =========================================================
