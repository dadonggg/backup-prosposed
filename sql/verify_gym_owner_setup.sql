-- Verification Script for Gym Owner Application System
-- Run this to check if all tables and columns are set up correctly

-- 1. Check if legal_documents table has all required columns
SELECT 'Checking legal_documents table structure...' as status;
DESCRIBE legal_documents;

-- 2. Check if staff_applications table has gym_owner_id column
SELECT 'Checking staff_applications table structure...' as status;
DESCRIBE staff_applications;

-- 3. Check for verified gyms (should appear in staff application list)
SELECT 'Verified Gyms (available for staff applications):' as status;
SELECT 
    ld.id,
    ld.gym_name,
    ld.gym_address,
    ld.maintenance_count,
    ld.trainer_count,
    u.fullname as owner_name,
    u.role as owner_role
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.status = 'verified';

-- 4. Check pending gym owner applications
SELECT 'Pending Gym Owner Applications:' as status;
SELECT 
    ld.id,
    u.fullname as applicant_name,
    u.email,
    ld.gym_name,
    ld.cert_registration_status,
    ld.mayors_permit_status,
    ld.business_name_cert_status,
    ld.fire_safety_cert_status,
    ld.status as overall_status,
    ld.created_at
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.status IN ('pending', 'resubmit')
ORDER BY ld.created_at DESC;

-- 5. Check staff applications
SELECT 'Staff Applications:' as status;
SELECT 
    sa.id,
    u.fullname as applicant_name,
    sa.application_type,
    sa.status,
    go.fullname as gym_owner_name,
    ld.gym_name,
    sa.created_at
FROM staff_applications sa
JOIN users u ON u.id = sa.user_id
LEFT JOIN users go ON go.id = sa.gym_owner_id
LEFT JOIN legal_documents ld ON ld.user_id = sa.gym_owner_id
ORDER BY sa.created_at DESC
LIMIT 10;

-- 6. Check employees and their gym owners
SELECT 'Employees and Their Gym Owners:' as status;
SELECT 
    e.id,
    u.fullname as employee_name,
    e.position,
    go.fullname as hired_by_name,
    ld.gym_name,
    e.is_available,
    e.hired_at
FROM employees e
JOIN users u ON u.id = e.user_id
JOIN users go ON go.id = e.hired_by
LEFT JOIN legal_documents ld ON ld.user_id = go.id
ORDER BY e.hired_at DESC;

-- 7. Check for any issues
SELECT 'Potential Issues:' as status;

-- Check for staff applications without gym_owner_id
SELECT 'Staff applications without gym_owner_id:' as issue, COUNT(*) as count
FROM staff_applications 
WHERE gym_owner_id IS NULL;

-- Check for gym owners without legal documents
SELECT 'Gym owners without legal documents:' as issue, COUNT(*) as count
FROM users u
LEFT JOIN legal_documents ld ON ld.user_id = u.id
WHERE u.role = 'gym_owner' AND ld.id IS NULL;

-- Check for negative staff counts
SELECT 'Gyms with negative staff counts:' as issue, COUNT(*) as count
FROM legal_documents
WHERE maintenance_count < 0 OR trainer_count < 0;

-- 8. Summary Statistics
SELECT 'System Summary:' as status;
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'gym_owner') as total_gym_owners,
    (SELECT COUNT(*) FROM legal_documents WHERE status = 'verified') as verified_gyms,
    (SELECT COUNT(*) FROM legal_documents WHERE status = 'pending') as pending_applications,
    (SELECT COUNT(*) FROM staff_applications WHERE status = 'pending') as pending_staff_apps,
    (SELECT COUNT(*) FROM employees) as total_employees,
    (SELECT COUNT(*) FROM employees WHERE position = 'trainer') as total_trainers,
    (SELECT COUNT(*) FROM employees WHERE position = 'maintenance') as total_maintenance;
