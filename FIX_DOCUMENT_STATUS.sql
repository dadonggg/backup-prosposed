-- =========================================================
-- FIX DOCUMENT STATUS DISPLAY ISSUE
-- This SQL adds per-document status columns and updates existing records
-- Run this in phpMyAdmin SQL tab
-- =========================================================

USE webdev;

-- Step 1: Add columns for Certificate of Registration (if they don't exist)
SET @sql1 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'cert_registration_status') = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending''',
    'SELECT ''Column cert_registration_status already exists'' AS message'
);
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @sql2 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'cert_registration_comment') = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_comment TEXT DEFAULT NULL',
    'SELECT ''Column cert_registration_comment already exists'' AS message'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @sql3 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'cert_registration_checked') = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT ''Column cert_registration_checked already exists'' AS message'
);
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- Step 2: Add columns for Mayor's Permit (if they don't exist)
SET @sql4 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'mayors_permit_status') = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending''',
    'SELECT ''Column mayors_permit_status already exists'' AS message'
);
PREPARE stmt4 FROM @sql4;
EXECUTE stmt4;
DEALLOCATE PREPARE stmt4;

SET @sql5 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'mayors_permit_comment') = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_comment TEXT DEFAULT NULL',
    'SELECT ''Column mayors_permit_comment already exists'' AS message'
);
PREPARE stmt5 FROM @sql5;
EXECUTE stmt5;
DEALLOCATE PREPARE stmt5;

SET @sql6 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'mayors_permit_checked') = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT ''Column mayors_permit_checked already exists'' AS message'
);
PREPARE stmt6 FROM @sql6;
EXECUTE stmt6;
DEALLOCATE PREPARE stmt6;

-- Step 3: Add columns for Business Name Certificate (if they don't exist)
SET @sql7 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'business_name_cert_status') = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending''',
    'SELECT ''Column business_name_cert_status already exists'' AS message'
);
PREPARE stmt7 FROM @sql7;
EXECUTE stmt7;
DEALLOCATE PREPARE stmt7;

SET @sql8 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'business_name_cert_comment') = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_comment TEXT DEFAULT NULL',
    'SELECT ''Column business_name_cert_comment already exists'' AS message'
);
PREPARE stmt8 FROM @sql8;
EXECUTE stmt8;
DEALLOCATE PREPARE stmt8;

SET @sql9 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'business_name_cert_checked') = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT ''Column business_name_cert_checked already exists'' AS message'
);
PREPARE stmt9 FROM @sql9;
EXECUTE stmt9;
DEALLOCATE PREPARE stmt9;

-- Step 4: Add columns for Fire Safety Certificate (if they don't exist)
SET @sql10 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'fire_safety_cert_status') = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending''',
    'SELECT ''Column fire_safety_cert_status already exists'' AS message'
);
PREPARE stmt10 FROM @sql10;
EXECUTE stmt10;
DEALLOCATE PREPARE stmt10;

SET @sql11 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'fire_safety_cert_comment') = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_comment TEXT DEFAULT NULL',
    'SELECT ''Column fire_safety_cert_comment already exists'' AS message'
);
PREPARE stmt11 FROM @sql11;
EXECUTE stmt11;
DEALLOCATE PREPARE stmt11;

SET @sql12 = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS 
     WHERE TABLE_SCHEMA = 'webdev' 
     AND TABLE_NAME = 'legal_documents' 
     AND COLUMN_NAME = 'fire_safety_cert_checked') = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT ''Column fire_safety_cert_checked already exists'' AS message'
);
PREPARE stmt12 FROM @sql12;
EXECUTE stmt12;
DEALLOCATE PREPARE stmt12;

-- Step 5: Update any NULL values to 'pending' (for existing records)
UPDATE legal_documents 
SET 
    cert_registration_status = COALESCE(cert_registration_status, 'pending'),
    mayors_permit_status = COALESCE(mayors_permit_status, 'pending'),
    business_name_cert_status = COALESCE(business_name_cert_status, 'pending'),
    fire_safety_cert_status = COALESCE(fire_safety_cert_status, 'pending'),
    cert_registration_checked = COALESCE(cert_registration_checked, 0),
    mayors_permit_checked = COALESCE(mayors_permit_checked, 0),
    business_name_cert_checked = COALESCE(business_name_cert_checked, 0),
    fire_safety_cert_checked = COALESCE(fire_safety_cert_checked, 0);

-- Step 6: Verify the fix
SELECT 
    'Fix completed! Checking results...' AS message;

SELECT 
    id,
    user_id,
    status AS overall_status,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents
ORDER BY id DESC;

-- =========================================================
-- DONE! Now test:
-- 1. Login as admin
-- 2. Go to Legal Document Reviews
-- 3. Click on an application
-- 4. Flag or approve individual documents
-- 5. Login as gym owner (customer)
-- 6. Go to Apply as Gym Owner page
-- 7. You should see the correct status badges!
-- =========================================================
