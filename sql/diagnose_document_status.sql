-- Diagnostic Script: Check Document Status System
-- Run this to see what's happening with document statuses

USE webdev;

-- 1. Check if status columns exist
SELECT '=== STEP 1: Checking if status columns exist ===' AS step;
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'webdev' 
  AND TABLE_NAME = 'legal_documents'
  AND (COLUMN_NAME LIKE '%_status' OR COLUMN_NAME LIKE '%_comment' OR COLUMN_NAME LIKE '%_checked')
ORDER BY ORDINAL_POSITION;

-- 2. Check current legal document applications
SELECT '=== STEP 2: Current Legal Document Applications ===' AS step;
SELECT 
    id,
    user_id,
    gym_name,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status AS overall_status,
    created_at
FROM legal_documents
ORDER BY created_at DESC
LIMIT 5;

-- 3. Check for any applications with flagged documents
SELECT '=== STEP 3: Applications with Flagged Documents ===' AS step;
SELECT 
    ld.id,
    u.fullname AS applicant_name,
    u.email,
    ld.gym_name,
    ld.cert_registration_status,
    ld.cert_registration_comment,
    ld.mayors_permit_status,
    ld.mayors_permit_comment,
    ld.business_name_cert_status,
    ld.business_name_cert_comment,
    ld.fire_safety_cert_status,
    ld.fire_safety_cert_comment,
    ld.status AS overall_status,
    ld.admin_feedback
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.cert_registration_status = 'flagged'
   OR ld.mayors_permit_status = 'flagged'
   OR ld.business_name_cert_status = 'flagged'
   OR ld.fire_safety_cert_status = 'flagged';

-- 4. Check for applications with approved documents
SELECT '=== STEP 4: Applications with Approved Documents ===' AS step;
SELECT 
    ld.id,
    u.fullname AS applicant_name,
    ld.gym_name,
    ld.cert_registration_status,
    ld.mayors_permit_status,
    ld.business_name_cert_status,
    ld.fire_safety_cert_status,
    ld.status AS overall_status
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.cert_registration_status = 'approved'
   OR ld.mayors_permit_status = 'approved'
   OR ld.business_name_cert_status = 'approved'
   OR ld.fire_safety_cert_status = 'approved';

-- 5. Check for mismatched statuses (overall status doesn't match document statuses)
SELECT '=== STEP 5: Potential Status Mismatches ===' AS step;
SELECT 
    ld.id,
    u.fullname AS applicant_name,
    ld.cert_registration_status,
    ld.mayors_permit_status,
    ld.business_name_cert_status,
    ld.fire_safety_cert_status,
    ld.status AS overall_status,
    CASE 
        WHEN ld.cert_registration_status = 'flagged' 
          OR ld.mayors_permit_status = 'flagged'
          OR ld.business_name_cert_status = 'flagged'
          OR ld.fire_safety_cert_status = 'flagged'
        THEN 'Should be resubmit'
        WHEN ld.cert_registration_status = 'approved'
         AND ld.mayors_permit_status = 'approved'
         AND ld.business_name_cert_status = 'approved'
         AND ld.fire_safety_cert_status = 'approved'
        THEN 'Should be verified'
        ELSE 'Should be pending'
    END AS expected_status
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.status != CASE 
        WHEN ld.cert_registration_status = 'flagged' 
          OR ld.mayors_permit_status = 'flagged'
          OR ld.business_name_cert_status = 'flagged'
          OR ld.fire_safety_cert_status = 'flagged'
        THEN 'resubmit'
        WHEN ld.cert_registration_status = 'approved'
         AND ld.mayors_permit_status = 'approved'
         AND ld.business_name_cert_status = 'approved'
         AND ld.fire_safety_cert_status = 'approved'
        THEN 'verified'
        ELSE 'pending'
    END;

-- 6. Check recent notifications
SELECT '=== STEP 6: Recent Notifications ===' AS step;
SELECT 
    n.id,
    u.fullname AS recipient,
    n.title,
    n.message,
    n.type,
    n.is_read,
    n.created_at
FROM notifications n
JOIN users u ON u.id = n.user_id
WHERE n.title LIKE '%Certificate%' OR n.title LIKE '%Permit%' OR n.title LIKE '%Document%'
ORDER BY n.created_at DESC
LIMIT 10;

-- 7. Summary
SELECT '=== SUMMARY ===' AS step;
SELECT 
    COUNT(*) AS total_applications,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified_count,
    SUM(CASE WHEN status = 'resubmit' THEN 1 ELSE 0 END) AS resubmit_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
    SUM(CASE WHEN cert_registration_status = 'flagged' 
              OR mayors_permit_status = 'flagged'
              OR business_name_cert_status = 'flagged'
              OR fire_safety_cert_status = 'flagged' THEN 1 ELSE 0 END) AS has_flagged_docs
FROM legal_documents;
