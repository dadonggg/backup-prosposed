-- =========================================================
-- CONVERT USER TO GYM OWNER
-- Use this to manually convert a user to gym_owner role
-- =========================================================

USE webdev;

-- Step 1: Find users with verified legal documents
SELECT 
    u.id,
    u.fullname,
    u.email,
    u.role as current_role,
    ld.status as document_status
FROM users u
JOIN legal_documents ld ON ld.user_id = u.id
WHERE ld.status = 'verified' OR ld.status = 'pending'
ORDER BY ld.id DESC;

-- Step 2: Convert specific user to gym_owner (replace USER_ID with actual ID)
-- UPDATE users SET role = 'gym_owner' WHERE id = USER_ID;

-- Example: If user ID is 5, uncomment and run this:
-- UPDATE users SET role = 'gym_owner' WHERE id = 5;

-- Step 3: Verify the change
-- SELECT id, fullname, email, role FROM users WHERE id = USER_ID;

-- =========================================================
-- INSTRUCTIONS:
-- 1. Look at the results from Step 1
-- 2. Find the user you want to convert
-- 3. Note their ID number
-- 4. Uncomment the UPDATE line in Step 2
-- 5. Replace USER_ID with the actual ID
-- 6. Run the UPDATE statement
-- 7. Run the SELECT in Step 3 to verify
-- =========================================================

-- Quick fix for most recent application:
UPDATE users 
SET role = 'gym_owner' 
WHERE id = (
    SELECT user_id 
    FROM legal_documents 
    ORDER BY id DESC 
    LIMIT 1
);

-- Verify it worked:
SELECT 
    u.id,
    u.fullname,
    u.email,
    u.role,
    'User should now be gym_owner' as note
FROM users u
JOIN legal_documents ld ON ld.user_id = u.id
ORDER BY ld.id DESC
LIMIT 1;