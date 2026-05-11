-- Add gym details to legal_documents table
-- Run this migration to add gym information fields

USE webdev;

-- Add gym details columns if they don't exist
ALTER TABLE legal_documents 
ADD COLUMN IF NOT EXISTS gym_name VARCHAR(255) DEFAULT NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS gym_logo VARCHAR(500) DEFAULT NULL AFTER gym_name,
ADD COLUMN IF NOT EXISTS gym_address TEXT DEFAULT NULL AFTER gym_logo,
ADD COLUMN IF NOT EXISTS maintenance_count INT DEFAULT 0 AFTER gym_address,
ADD COLUMN IF NOT EXISTS trainer_count INT DEFAULT 0 AFTER maintenance_count;

-- Verify the changes
DESCRIBE legal_documents;
