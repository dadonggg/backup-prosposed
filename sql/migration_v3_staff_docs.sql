-- =========================================================
-- Migration V3: Per-document review for Staff Applications
-- =========================================================

USE webdev;

-- Add per-document status, comment, and checked columns for staff applications
ALTER TABLE staff_applications
    ADD COLUMN medical_certificate_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER medical_certificate,
    ADD COLUMN medical_certificate_comment TEXT DEFAULT NULL AFTER medical_certificate_status,
    ADD COLUMN medical_certificate_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER medical_certificate_comment,

    ADD COLUMN resume_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER resume,
    ADD COLUMN resume_comment TEXT DEFAULT NULL AFTER resume_status,
    ADD COLUMN resume_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER resume_comment;

-- Add 'resubmit' to the status ENUM so applicants can fix specific documents
ALTER TABLE staff_applications
    MODIFY COLUMN status ENUM('pending','approved','rejected','resubmit') NOT NULL DEFAULT 'pending';
