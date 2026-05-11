-- Check and fix legal_documents table structure
-- Run this SQL script to ensure all required columns exist

-- Check if columns exist and add them if missing
SET @dbname = 'webdev';
SET @tablename = 'legal_documents';

-- Add cert_registration_status if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'cert_registration_status';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER cert_registration',
    'SELECT ''Column cert_registration_status already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add cert_registration_comment if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'cert_registration_comment';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_comment TEXT DEFAULT NULL AFTER cert_registration_status',
    'SELECT ''Column cert_registration_comment already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add cert_registration_checked if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'cert_registration_checked';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment',
    'SELECT ''Column cert_registration_checked already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add mayors_permit_status if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'mayors_permit_status';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER mayors_permit',
    'SELECT ''Column mayors_permit_status already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add mayors_permit_comment if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'mayors_permit_comment';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_comment TEXT DEFAULT NULL AFTER mayors_permit_status',
    'SELECT ''Column mayors_permit_comment already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add mayors_permit_checked if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'mayors_permit_checked';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment',
    'SELECT ''Column mayors_permit_checked already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add business_name_cert_status if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'business_name_cert_status';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER business_name_cert',
    'SELECT ''Column business_name_cert_status already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add business_name_cert_comment if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'business_name_cert_comment';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_comment TEXT DEFAULT NULL AFTER business_name_cert_status',
    'SELECT ''Column business_name_cert_comment already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add business_name_cert_checked if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'business_name_cert_checked';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment',
    'SELECT ''Column business_name_cert_checked already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add fire_safety_cert_status if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'fire_safety_cert_status';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_status ENUM(''pending'',''approved'',''flagged'') NOT NULL DEFAULT ''pending'' AFTER fire_safety_cert',
    'SELECT ''Column fire_safety_cert_status already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add fire_safety_cert_comment if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'fire_safety_cert_comment';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_comment TEXT DEFAULT NULL AFTER fire_safety_cert_status',
    'SELECT ''Column fire_safety_cert_comment already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add fire_safety_cert_checked if not exists
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = @dbname 
AND TABLE_NAME = @tablename 
AND COLUMN_NAME = 'fire_safety_cert_checked';

SET @query = IF(@col_exists = 0,
    'ALTER TABLE legal_documents ADD COLUMN fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment',
    'SELECT ''Column fire_safety_cert_checked already exists'' AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the structure
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'webdev' 
AND TABLE_NAME = 'legal_documents'
ORDER BY ORDINAL_POSITION;
