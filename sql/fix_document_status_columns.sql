-- Fix Document Status Columns for Legal Documents
-- This ensures all per-document status columns exist

USE webdev;

-- Check if columns exist, if not add them
SET @dbname = 'webdev';
SET @tablename = 'legal_documents';

-- Certificate of Registration columns
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'cert_registration_status');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER cert_registration',
    'SELECT ''Column cert_registration_status already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'cert_registration_comment');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_comment TEXT NULL AFTER cert_registration_status',
    'SELECT ''Column cert_registration_comment already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'cert_registration_checked');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment',
    'SELECT ''Column cert_registration_checked already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mayor's Permit columns
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'mayors_permit_status');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER mayors_permit',
    'SELECT ''Column mayors_permit_status already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'mayors_permit_comment');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_comment TEXT NULL AFTER mayors_permit_status',
    'SELECT ''Column mayors_permit_comment already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'mayors_permit_checked');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment',
    'SELECT ''Column mayors_permit_checked already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Business Name Certificate columns
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'business_name_cert_status');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER business_name_cert',
    'SELECT ''Column business_name_cert_status already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'business_name_cert_comment');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_comment TEXT NULL AFTER business_name_cert_status',
    'SELECT ''Column business_name_cert_comment already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'business_name_cert_checked');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment',
    'SELECT ''Column business_name_cert_checked already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Fire Safety Certificate columns
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'fire_safety_cert_status');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER fire_safety_cert',
    'SELECT ''Column fire_safety_cert_status already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'fire_safety_cert_comment');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_comment TEXT NULL AFTER fire_safety_cert_status',
    'SELECT ''Column fire_safety_cert_comment already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = 'fire_safety_cert_checked');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment',
    'SELECT ''Column fire_safety_cert_checked already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify all columns exist
SELECT 'Verification: Checking all columns...' AS status;

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'webdev' 
  AND TABLE_NAME = 'legal_documents'
  AND COLUMN_NAME LIKE '%_status'
ORDER BY ORDINAL_POSITION;

SELECT 'All document status columns have been verified/created!' AS result;
