# 🚨 URGENT FIX - Run This NOW!

## Problem: Document Status Not Updating

## ✅ Quick Fix (5 Minutes)

### Step 1: Open phpMyAdmin

Go to: `http://localhost/phpmyadmin`

### Step 2: Select Database

Click on **`webdev`** database in the left sidebar

### Step 3: Click SQL Tab

Click the **"SQL"** tab at the top

### Step 4: Copy & Paste This SQL

```sql
USE webdev;

-- Check if columns exist
SELECT COUNT(*) as column_count
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'webdev' 
  AND TABLE_NAME = 'legal_documents'
  AND COLUMN_NAME LIKE '%_status';
```

### Step 5: Click "Go"

- **If result shows 4 or more:** Columns exist ✅ Go to Step 6
- **If result shows 0:** Columns missing ❌ Run this SQL:

```sql
-- Add missing columns
ALTER TABLE legal_documents 
ADD COLUMN IF NOT EXISTS cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER cert_registration,
ADD COLUMN IF NOT EXISTS cert_registration_comment TEXT NULL AFTER cert_registration_status,
ADD COLUMN IF NOT EXISTS cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_registration_comment,
ADD COLUMN IF NOT EXISTS mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER mayors_permit,
ADD COLUMN IF NOT EXISTS mayors_permit_comment TEXT NULL AFTER mayors_permit_status,
ADD COLUMN IF NOT EXISTS mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER mayors_permit_comment,
ADD COLUMN IF NOT EXISTS business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER business_name_cert,
ADD COLUMN IF NOT EXISTS business_name_cert_comment TEXT NULL AFTER business_name_cert_status,
ADD COLUMN IF NOT EXISTS business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER business_name_cert_comment,
ADD COLUMN IF NOT EXISTS fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending' AFTER fire_safety_cert,
ADD COLUMN IF NOT EXISTS fire_safety_cert_comment TEXT NULL AFTER fire_safety_cert_status,
ADD COLUMN IF NOT EXISTS fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0 AFTER fire_safety_cert_comment;
```

### Step 6: Fix Existing Data

```sql
-- Fix applications with flagged documents
UPDATE legal_documents
SET status = 'resubmit'
WHERE (cert_registration_status = 'flagged'
    OR mayors_permit_status = 'flagged'
    OR business_name_cert_status = 'flagged'
    OR fire_safety_cert_status = 'flagged')
  AND status != 'resubmit';

-- Fix applications with all approved documents
UPDATE legal_documents
SET status = 'verified'
WHERE cert_registration_status = 'approved'
  AND mayors_permit_status = 'approved'
  AND business_name_cert_status = 'approved'
  AND fire_safety_cert_status = 'approved'
  AND status != 'verified';

SELECT 'FIXED!' AS result;
```

### Step 7: Clear Browser Cache

**Press:** `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)

### Step 8: Test

1. **Login as Admin**
2. Go to "Legal Reviews" → Click "Review"
3. Flag a document → Add comment → Click "Flag Issue"
4. **Login as Applicant**
5. Go to "Apply as Gym Owner"
6. Click "Refresh" button
7. ✅ **You should see:** Flagged status with comment

## 🎯 Quick Verification

Run this to check if it's working:

```sql
SELECT 
    id,
    gym_name,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status
FROM legal_documents
ORDER BY updated_at DESC
LIMIT 5;
```

**Expected:** You should see status values like 'pending', 'approved', 'flagged'

## ❌ Still Not Working?

### Check 1: Verify Columns Exist

```sql
DESCRIBE legal_documents;
```

Look for columns ending with `_status`, `_comment`, `_checked`

### Check 2: Check Specific Application

```sql
SELECT * FROM legal_documents WHERE id = 29;
```

Replace `29` with your application ID from the URL

### Check 3: Hard Reset Browser

1. Close ALL browser windows
2. Reopen browser
3. Go to application page
4. Press `Ctrl + Shift + Delete`
5. Clear "Cached images and files"
6. Refresh page

## 📞 Emergency Contact

If nothing works, check:

1. **PHP Errors:** Look in `app/logs/database.log`
2. **Browser Console:** Press F12 → Console tab
3. **Database Connection:** Make sure MySQL is running

---

## 🎉 Success!

Once you see the flagged status and comments on the applicant's side, everything is working!

**The system is now connected and working properly.** ✅
