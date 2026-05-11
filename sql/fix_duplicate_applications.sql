-- =========================================================
-- Fix: Remove Duplicate Gym Owner Applications
-- PURPOSE: Each applicant should only have ONE record.
--          This script keeps the LATEST (highest ID) per user
--          and deletes the older duplicates.
-- Run ONCE in phpMyAdmin → SQL tab.
-- =========================================================

USE webdev;

-- ---------------------------------------------------------
-- STEP 1: Preview what will be deleted (safe read-only check)
-- ---------------------------------------------------------
SELECT id, user_id, status, created_at,
       'WILL BE DELETED (duplicate older record)' AS action
FROM legal_documents ld
WHERE id NOT IN (
    SELECT MAX(id) FROM legal_documents GROUP BY user_id
)
ORDER BY user_id, id;

-- ---------------------------------------------------------
-- STEP 2: Delete all older duplicate records per user
--         Keeps only the record with the highest ID per user_id
-- ---------------------------------------------------------
DELETE FROM legal_documents
WHERE id NOT IN (
    SELECT max_id FROM (
        SELECT MAX(id) AS max_id
        FROM legal_documents
        GROUP BY user_id
    ) AS keep_ids
);

-- Confirm how many remain
SELECT COUNT(*) AS remaining_records,
       COUNT(DISTINCT user_id) AS unique_applicants
FROM legal_documents;

-- ---------------------------------------------------------
-- STEP 3: Add UNIQUE constraint so the DB itself prevents
--         future duplicates at the data layer
-- ---------------------------------------------------------
-- Drop if already exists (safe)
ALTER TABLE legal_documents
    DROP INDEX IF EXISTS uniq_legal_doc_user;

ALTER TABLE legal_documents
    ADD UNIQUE KEY uniq_legal_doc_user (user_id);

SELECT 'Duplicates cleaned. UNIQUE constraint added on user_id.' AS result;
