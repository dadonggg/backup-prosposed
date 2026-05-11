## 🔧 FIX: Database Connection & Document Status Issues

## 🎯 Problem

Document status changes (approved/flagged) are not showing up on the applicant's side after admin reviews them.

## ✅ Solution - Run These Steps

### Step 1: Verify Database Columns Exist

**Run this SQL in phpMyAdmin:**

```bash
# Open phpMyAdmin → Select 'webdev' database → SQL tab
# Copy and paste this:
```

```sql
USE webdev;

-- Check if columns exist
DESCRIBE legal_documents;
```

**Expected Result:** You should see these columns:
- `cert_registration_status`
- `cert_registration_comment`
- `cert_registration_checked`
- `mayors_permit_status`
- `mayors_permit_comment`
- `mayors_permit_checked`
- `business_name_cert_status`
- `business_name_cert_comment`
- `business_name_cert_checked`
- `fire_safety_cert_status`
- `fire_safety_cert_comment`
- `fire_safety_cert_checked`

### Step 2: If Columns Are Missing, Run This

**If you DON'T see the columns above, run this SQL:**

```sql
-- Run this file in phpMyAdmin
SOURCE sql/fix_document_status_columns.sql;
```

**OR copy/paste from:** `sql/fix_document_status_columns.sql`

### Step 3: Diagnose Current Status

**Run the diagnostic script:**

```sql
SOURCE sql/diagnose_document_status.sql;
```

**OR copy/paste from:** `sql/diagnose_document_status.sql`

This will show you:
- Which columns exist
- Current applications and their statuses
- Any mismatches between document status and overall status

### Step 4: Fix Existing Data (If Needed)

**If you have existing applications with wrong status, run this:**

```sql
USE webdev;

-- Fix applications where documents are flagged but overall status is not 'resubmit'
UPDATE legal_documents
SET status = 'resubmit',
    admin_feedback = 'Please review and resubmit flagged documents.'
WHERE (cert_registration_status = 'flagged'
    OR mayors_permit_status = 'flagged'
    OR business_name_cert_status = 'flagged'
    OR fire_safety_cert_status = 'flagged')
  AND status != 'resubmit';

-- Fix applications where all documents are approved but overall status is not 'verified'
UPDATE legal_documents
SET status = 'verified',
    admin_feedback = 'All documents verified.'
WHERE cert_registration_status = 'approved'
  AND mayors_permit_status = 'approved'
  AND business_name_cert_status = 'approved'
  AND fire_safety_cert_status = 'approved'
  AND status != 'verified';

SELECT 'Data fixed!' AS result;
```

### Step 5: Clear Browser Cache

1. **Hard Refresh:** Press `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
2. **Or Clear Cache:** Browser Settings → Clear browsing data → Cached images and files

### Step 6: Test the System

#### Test as Admin:

1. Login as Admin
2. Go to "Legal Reviews"
3. Click "Review" on an application
4. For "Certificate of Registration":
   - Add comment: "Test comment"
   - Click "Flag Issue"
5. Check the database:
   ```sql
   SELECT id, cert_registration_status, cert_registration_comment, status
   FROM legal_documents
   WHERE id = [your_application_id];
   ```
6. ✅ **Expected:**
   - `cert_registration_status` = 'flagged'
   - `cert_registration_comment` = 'Test comment'
   - `status` = 'resubmit'

#### Test as Applicant:

1. Login as the applicant (gym owner applicant)
2. Go to "Apply as Gym Owner"
3. Click the "Refresh" button
4. ✅ **Expected:** See "Certificate of Registration: Flagged" with comment "Test comment"

## 🐛 Common Issues & Solutions

### Issue 1: Columns Don't Exist

**Symptom:** SQL error "Unknown column 'cert_registration_status'"

**Solution:**
```sql
-- Run this to add all missing columns
SOURCE sql/fix_document_status_columns.sql;
```

### Issue 2: Status Not Updating

**Symptom:** Admin flags document but applicant still sees "Pending"

**Solution:**
1. Check if `recomputeOverallStatus()` is being called:
   ```sql
   -- Check admin action logs
   SELECT * FROM app/logs/admin_actions.log;
   ```

2. Manually fix the status:
   ```sql
   UPDATE legal_documents
   SET status = 'resubmit'
   WHERE id = [application_id];
   ```

3. Clear browser cache and refresh

### Issue 3: Comments Not Showing

**Symptom:** Admin adds comment but applicant doesn't see it

**Solution:**
1. Verify comment was saved:
   ```sql
   SELECT cert_registration_comment, mayors_permit_comment,
          business_name_cert_comment, fire_safety_cert_comment
   FROM legal_documents
   WHERE id = [application_id];
   ```

2. If NULL, the comment wasn't saved. Check:
   - Form is submitting correctly
   - No JavaScript errors in browser console
   - PHP errors in error log

### Issue 4: Page Shows Old Data

**Symptom:** Applicant refreshes but still sees old status

**Solution:**
1. **Hard refresh:** `Ctrl + Shift + R`
2. **Check cache headers:** View page source, should see:
   ```html
   <!-- Cache-Control: no-cache, no-store, must-revalidate -->
   ```
3. **Clear all browser data** for localhost
4. **Try incognito/private window**

## 📊 Verification Queries

### Check Specific Application:

```sql
SELECT 
    id,
    gym_name,
    cert_registration_status,
    cert_registration_comment,
    mayors_permit_status,
    mayors_permit_comment,
    business_name_cert_status,
    business_name_cert_comment,
    fire_safety_cert_status,
    fire_safety_cert_comment,
    status AS overall_status,
    admin_feedback
FROM legal_documents
WHERE id = [your_application_id];
```

### Check All Flagged Documents:

```sql
SELECT 
    ld.id,
    u.fullname,
    ld.gym_name,
    ld.cert_registration_status,
    ld.mayors_permit_status,
    ld.business_name_cert_status,
    ld.fire_safety_cert_status,
    ld.status
FROM legal_documents ld
JOIN users u ON u.id = ld.user_id
WHERE ld.cert_registration_status = 'flagged'
   OR ld.mayors_permit_status = 'flagged'
   OR ld.business_name_cert_status = 'flagged'
   OR ld.fire_safety_cert_status = 'flagged';
```

### Check Recent Updates:

```sql
SELECT 
    id,
    gym_name,
    status,
    updated_at
FROM legal_documents
ORDER BY updated_at DESC
LIMIT 10;
```

## 🔍 Debug Mode

### Enable PHP Error Display:

Add to `public/index.php` (temporarily):

```php
<?php
// Add at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
```

### Check Database Connection:

Create `test_db.php` in root:

```php
<?php
require_once __DIR__ . '/app/core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    echo "✅ Database connected successfully!<br>";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as count FROM legal_documents");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['count']} legal document applications<br>";
    
    // Check columns
    $stmt = $db->query("DESCRIBE legal_documents");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = [
        'cert_registration_status',
        'mayors_permit_status',
        'business_name_cert_status',
        'fire_safety_cert_status'
    ];
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "✅ Column '$col' exists<br>";
        } else {
            echo "❌ Column '$col' MISSING!<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
```

Then visit: `http://localhost/webdev/test_db.php`

## ✅ Success Checklist

- [ ] All status columns exist in database
- [ ] Diagnostic script runs without errors
- [ ] Admin can flag documents
- [ ] Database shows flagged status
- [ ] Overall status changes to 'resubmit'
- [ ] Applicant sees flagged status after refresh
- [ ] Comments are visible to applicant
- [ ] Resubmit button appears for flagged documents
- [ ] Notifications are sent

## 📞 Still Not Working?

If you've tried everything above and it's still not working:

1. **Check PHP error logs:**
   - `app/logs/database.log`
   - `app/logs/admin_actions.log`

2. **Check browser console:**
   - Press F12 → Console tab
   - Look for JavaScript errors

3. **Verify file permissions:**
   ```bash
   chmod -R 755 app/
   chmod -R 777 app/logs/
   chmod -R 777 public/uploads/
   ```

4. **Test with fresh data:**
   - Create a new gym owner application
   - Flag a document
   - Check if it shows up

---

**Run the diagnostic script first, then follow the steps based on what it shows!**

```sql
SOURCE sql/diagnose_document_status.sql;
```
