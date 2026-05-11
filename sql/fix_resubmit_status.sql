-- Fix: Update overall status to 'resubmit' when documents are flagged
-- This will make the resubmit buttons appear for applicants

USE webdev;

-- Step 1: Check current status
SELECT 'BEFORE FIX:' AS step;
SELECT 
    id,
    gym_name,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status AS overall_status,
    admin_feedback
FROM legal_documents
WHERE cert_registration_status = 'flagged'
   OR mayors_permit_status = 'flagged'
   OR business_name_cert_status = 'flagged'
   OR fire_safety_cert_status = 'flagged';

-- Step 2: Fix the overall status
UPDATE legal_documents
SET status = 'resubmit',
    admin_feedback = CONCAT(
        CASE WHEN cert_registration_status = 'flagged' 
             THEN CONCAT('Certificate of Registration: ', COALESCE(cert_registration_comment, 'Flagged for resubmission'))
             ELSE '' END,
        CASE WHEN cert_registration_status = 'flagged' AND mayors_permit_status = 'flagged' THEN ' | ' ELSE '' END,
        CASE WHEN mayors_permit_status = 'flagged' 
             THEN CONCAT('Mayor\'s Permit: ', COALESCE(mayors_permit_comment, 'Flagged for resubmission'))
             ELSE '' END,
        CASE WHEN mayors_permit_status = 'flagged' AND business_name_cert_status = 'flagged' THEN ' | ' ELSE '' END,
        CASE WHEN business_name_cert_status = 'flagged' 
             THEN CONCAT('Business Name Certificate: ', COALESCE(business_name_cert_comment, 'Flagged for resubmission'))
             ELSE '' END,
        CASE WHEN business_name_cert_status = 'flagged' AND fire_safety_cert_status = 'flagged' THEN ' | ' ELSE '' END,
        CASE WHEN fire_safety_cert_status = 'flagged' 
             THEN CONCAT('Fire Safety Certificate: ', COALESCE(fire_safety_cert_comment, 'Flagged for resubmission'))
             ELSE '' END
    )
WHERE (cert_registration_status = 'flagged'
    OR mayors_permit_status = 'flagged'
    OR business_name_cert_status = 'flagged'
    OR fire_safety_cert_status = 'flagged')
  AND status != 'resubmit';

-- Step 3: Check after fix
SELECT 'AFTER FIX:' AS step;
SELECT 
    id,
    gym_name,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status AS overall_status,
    admin_feedback
FROM legal_documents
WHERE cert_registration_status = 'flagged'
   OR mayors_permit_status = 'flagged'
   OR business_name_cert_status = 'flagged'
   OR fire_safety_cert_status = 'flagged';

SELECT 'Status updated! Now refresh the applicant page.' AS result;
