# 🔧 Fix Document Status Display Issue

## 🎯 Problem
Admin can flag/approve individual documents, but gym owner sees all documents as "Pending" instead of seeing the actual "Flagged" or "Approved" status.

## 🔍 Root Cause
The per-document status columns (`cert_registration_status`, `mayors_permit_status`, etc.) may not exist in the database, or they exist but have NULL values instead of proper status values.

## ✅ Solution Steps

### Step 1: Run Diagnostic Script
1. Open your browser
2. Go to: `http://localhost/webdev/debug_document_status.php`
3. This will show you:
   - Which columns exist
   - Current status values
   - What SQL needs to be run

### Step 2: Add Missing Columns (if needed)
If the diagnostic shows missing columns, run this SQL in phpMyAdmin:

```sql
USE webdev;

-- Add per-document status columns
ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS cert_registration_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS cert_registration_comment TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS cert_registration_checked TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS mayors_permit_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS mayors_permit_comment TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS mayors_permit_checked TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS business_name_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS business_name_cert_comment TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS business_name_cert_checked TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE legal_documents
    ADD COLUMN IF NOT EXISTS fire_safety_cert_status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS fire_safety_cert_comment TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS fire_safety_cert_checked TINYINT(1) NOT NULL DEFAULT 0;
```

### Step 3: Update Existing Records
If columns exist but have NULL values, run this SQL:

```sql
USE webdev;

-- Set default 'pending' status for any NULL values
UPDATE legal_documents 
SET 
    cert_registration_status = COALESCE(cert_registration_status, 'pending'),
    mayors_permit_status = COALESCE(mayors_permit_status, 'pending'),
    business_name_cert_status = COALESCE(business_name_cert_status, 'pending'),
    fire_safety_cert_status = COALESCE(fire_safety_cert_status, 'pending')
WHERE 
    cert_registration_status IS NULL 
    OR mayors_permit_status IS NULL 
    OR business_name_cert_status IS NULL 
    OR fire_safety_cert_status IS NULL;
```

### Step 4: Test the Fix
1. **As Admin**:
   - Go to: Admin Dashboard → Legal Document Reviews
   - Click on an application
   - Flag or approve individual documents
   - Click "Approve" or "Flag Issue" button
   - You should see success message

2. **As Gym Owner (Customer)**:
   - Logout from admin
   - Login as the customer who submitted the application
   - Go to: Apply as Gym Owner page
   - Click the "Refresh" button (top right)
   - You should now see:
     - ✅ Green "Approved" badge for approved documents
     - 🚩 Red "Flagged" badge for flagged documents
     - ⏳ Yellow "Pending" badge for pending documents

### Step 5: Clear Browser Cache
If you still see "Pending" after the fix:
1. Press `Ctrl + Shift + R` (hard refresh)
2. Or clear browser cache completely
3. Or try in incognito/private window

---

## 🔍 How to Verify It's Working

### Check in Database:
```sql
SELECT 
    id,
    user_id,
    status as overall_status,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents
ORDER BY id DESC;
```

You should see:
- `overall_status`: pending, verified, resubmit, or rejected
- Individual statuses: pending, approved, or flagged

### Check in Admin View:
- Go to admin review page
- Each document should show its status badge (Approved/Flagged/Pending)
- When you click "Approve" or "Flag Issue", the badge should update

### Check in Gym Owner View:
- Go to gym owner application page
- Each document should show:
  - Green "Approved" badge if approved
  - Red "Flagged" badge if flagged (with resubmit button)
  - Yellow "Pending" badge if pending

---

## 🎨 Expected Visual Result

### Admin Review Page:
```
┌─────────────────────────────────────────┐
│ Certificate of Registration   [Approved]│
│ [View Document]                         │
│ ☑ Verified / Reviewed                  │
│ Comment: Looks good                     │
│ [✓ Approve] [🚩 Flag] [↻ Reset]       │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ Business Name Certificate      [Flagged]│
│ [View Document]                         │
│ ☐ Verified / Reviewed                  │
│ Comment: Please resubmit clearer copy   │
│ [✓ Approve] [🚩 Flag] [↻ Reset]       │
└─────────────────────────────────────────┘
```

### Gym Owner Application Page:
```
┌─────────────────────────────────────────┐
│ ✅ Certificate of Registration          │
│                            [Approved]    │
│ [View Document]                         │
│ ✓ Document approved                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ 🚩 Business Name Certificate             │
│                            [Flagged]     │
│ [View Document]                         │
│ ⚠ Reason: Please resubmit clearer copy │
│ [Choose File] [↻ Resubmit]             │
└─────────────────────────────────────────┘
```

---

## 🐛 Troubleshooting

### Issue: Still showing "Pending" after SQL
**Solution**: 
1. Check if admin actually clicked "Approve" or "Flag Issue" button
2. Run diagnostic script to verify column values
3. Clear browser cache (Ctrl + Shift + R)

### Issue: "Column already exists" error
**Solution**: 
- This is OK! It means columns already exist
- Skip to Step 3 (Update Existing Records)

### Issue: Admin changes not saving
**Solution**:
1. Check PHP error logs
2. Verify `updateDocStatus()` method in `LegalDocument.php` is working
3. Check database permissions

### Issue: Resubmit button not appearing
**Solution**:
1. Make sure document status is actually "flagged" in database
2. Check the view file has the resubmit form code
3. Clear browser cache

---

## 📊 Quick SQL Checks

### Check if columns exist:
```sql
SHOW COLUMNS FROM legal_documents LIKE '%_status';
```

### Check current values:
```sql
SELECT 
    id,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status
FROM legal_documents;
```

### Manually set a document to flagged (for testing):
```sql
UPDATE legal_documents 
SET business_name_cert_status = 'flagged',
    business_name_cert_comment = 'Test flagged document'
WHERE id = 39;  -- Replace with your document ID
```

### Manually set a document to approved (for testing):
```sql
UPDATE legal_documents 
SET cert_registration_status = 'approved',
    cert_registration_comment = 'Test approved document'
WHERE id = 39;  -- Replace with your document ID
```

---

## ✅ Success Checklist

- [ ] Ran diagnostic script (`debug_document_status.php`)
- [ ] All required columns exist in database
- [ ] Ran SQL to add missing columns (if needed)
- [ ] Ran SQL to update NULL values (if needed)
- [ ] Admin can flag/approve documents
- [ ] Admin sees correct status badges
- [ ] Gym owner sees correct status badges
- [ ] Flagged documents show resubmit button
- [ ] Approved documents show green checkmark
- [ ] Cleared browser cache
- [ ] Tested with actual data

---

## 📁 Files Involved

- **Diagnostic**: `debug_document_status.php` (NEW - run this first!)
- **SQL Migration**: `add_document_status_columns.sql`
- **Model**: `app/models/LegalDocument.php`
- **Admin View**: `app/views/admin/review_legal.php`
- **Gym Owner View**: `app/views/gymowner/apply.php`
- **Controller**: `app/controllers/AdminController.php`

---

## 🎯 Summary

The issue is that per-document status columns either:
1. Don't exist in the database (need to run SQL migration)
2. Exist but have NULL values (need to update existing records)
3. Exist and have values but browser cache is showing old data (need to clear cache)

**Quick Fix**:
1. Run `debug_document_status.php` to diagnose
2. Run the SQL provided in Step 2 and Step 3
3. Clear browser cache (Ctrl + Shift + R)
4. Test as admin and gym owner

---

**Status**: Ready to fix!
**Time to Fix**: 5 minutes
**Difficulty**: Easy
