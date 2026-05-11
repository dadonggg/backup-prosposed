-- =========================================================
-- Migration V4: Application Workflow Fixes & Payment Tracking
-- Run this in phpMyAdmin (Import tab) or MySQL CLI.
-- =========================================================

USE webdev;

-- ---------------------------------------------------------
-- 1. Fix staff_applications: allow reviewer_id to be NULL properly
--    and add FK constraint if missing
-- ---------------------------------------------------------

-- Fix any existing reviewer_id = 0 rows → set to NULL
UPDATE staff_applications SET reviewer_id = NULL WHERE reviewer_id = 0;

-- Drop existing FK on reviewer_id if it exists (safe to ignore errors)
-- ALTER TABLE staff_applications DROP FOREIGN KEY fk_staff_app_reviewer;

-- Add proper FK constraint for reviewer_id
-- ALTER TABLE staff_applications ADD CONSTRAINT fk_staff_app_reviewer
--     FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL;

-- ---------------------------------------------------------
-- 2. Membership applications: add resubmit + verified statuses,
--    payment_type, and reviewer tracking
-- ---------------------------------------------------------

ALTER TABLE membership_applications
    MODIFY COLUMN status ENUM('pending','verified','approved','rejected','resubmit') NOT NULL DEFAULT 'pending';

ALTER TABLE membership_applications
    ADD COLUMN payment_type VARCHAR(50) DEFAULT NULL AFTER preferred_trainer_id,
    ADD COLUMN payment_amount DECIMAL(10,2) DEFAULT NULL AFTER payment_type,
    ADD COLUMN student_proof VARCHAR(500) DEFAULT NULL AFTER payment_amount,
    ADD COLUMN reviewer_id INT DEFAULT NULL AFTER admin_feedback;

-- ---------------------------------------------------------
-- 3. Gym members: add payment tracking, start/expiration dates
-- ---------------------------------------------------------

ALTER TABLE gym_members
    ADD COLUMN payment_type VARCHAR(50) DEFAULT NULL AFTER assigned_trainer_id,
    ADD COLUMN payment_amount DECIMAL(10,2) DEFAULT NULL AFTER payment_type,
    ADD COLUMN start_date DATE DEFAULT NULL AFTER payment_amount,
    ADD COLUMN expiration_date DATE DEFAULT NULL AFTER start_date;

-- ---------------------------------------------------------
-- 4. Prevent duplicate active membership applications
--    (one active application per user)
-- ---------------------------------------------------------

-- We'll enforce this at the application level, not DB level,
-- since we need to allow resubmissions of the same record.

-- ---------------------------------------------------------
-- 5. Legal documents: add 'rejected' to status ENUM for consistency
-- ---------------------------------------------------------

ALTER TABLE legal_documents
    MODIFY COLUMN status ENUM('pending','verified','resubmit','rejected') NOT NULL DEFAULT 'pending';

