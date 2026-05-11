# Deployment Checklist - Gym Management System

## 🚀 Pre-Deployment Steps

### 1. Database Migration
Run this SQL in phpMyAdmin or MySQL command line:

```sql
USE webdev;

-- Add gym details to legal_documents table
ALTER TABLE legal_documents 
ADD COLUMN IF NOT EXISTS gym_name VARCHAR(255) DEFAULT NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS gym_logo VARCHAR(500) DEFAULT NULL AFTER gym_name,
ADD COLUMN IF NOT EXISTS gym_address TEXT DEFAULT NULL AFTER gym_logo,
ADD COLUMN IF NOT EXISTS maintenance_count INT DEFAULT 0 AFTER gym_address,
ADD COLUMN IF NOT EXISTS trainer_count INT DEFAULT 0 AFTER maintenance_count;

-- Verify the changes
DESCRIBE legal_documents;
```

**Expected Output**: Should show 5 new columns (gym_name, gym_logo, gym_address, maintenance_count, trainer_count)

---

### 2. File Permissions
Ensure these directories are writable:

```bash
chmod -R 755 app/logs
chmod -R 755 public/uploads/legal_documents
chmod -R 755 public/uploads/staff_applications
chmod -R 755 public/uploads/student_proof
```

Or in Windows: Right-click → Properties → Security → Edit → Allow "Write" for web server user

---

### 3. Configuration Check

#### Database Configuration (`app/config/config.php`):
```php
'db' => [
    'host' => '127.0.0.1',
    'name' => 'webdev',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
],
```

#### PayMongo Configuration (Optional - for online payments):
Add to `app/config/config.php`:
```php
'paymongo' => [
    'secret_key' => 'sk_test_YOUR_SECRET_KEY_HERE',
    'public_key' => 'pk_test_YOUR_PUBLIC_KEY_HERE',
],
```

---

## ✅ Testing Checklist

### Test 1: Gym Owner Application (5 minutes)
1. [ ] Register as customer
2. [ ] Navigate to "Gym Owner → Apply"
3. [ ] Fill in gym details:
   - Gym Name: "Test Gym"
   - Gym Address: "123 Test Street"
   - Maintenance Staff: 2
   - Trainers: 3
4. [ ] Upload gym logo (optional)
5. [ ] Upload 4 legal documents (PDF/JPG/PNG)
6. [ ] Submit application
7. [ ] **Expected**: Success message, application shows "Under Review"

### Test 2: Admin Review & Status Persistence (5 minutes)
1. [ ] Login as admin
2. [ ] Navigate to "Admin → Legal Reviews"
3. [ ] Click "Review" on test application
4. [ ] Approve "Certificate of Registration"
5. [ ] **Press F5 to refresh page**
6. [ ] **Expected**: Certificate still shows "Approved" ✅
7. [ ] Flag "Mayor's Permit" with comment "Document expired"
8. [ ] **Press F5 to refresh page**
9. [ ] **Expected**: Mayor's Permit still shows "Flagged" ✅

### Test 3: Gym Owner Sees Updates (3 minutes)
1. [ ] Logout from admin
2. [ ] Login as gym owner (test customer)
3. [ ] Navigate to "Gym Owner → Apply"
4. [ ] **Expected**: 
   - Certificate of Registration: "Accepted" (green badge) ✅
   - Mayor's Permit: "Rejected" (red badge) with comment ✅
   - Resubmit button appears for Mayor's Permit ✅

### Test 4: Selective Resubmission (3 minutes)
1. [ ] Click "Resubmit" for Mayor's Permit
2. [ ] Upload new document
3. [ ] Submit
4. [ ] **Expected**: 
   - Success message
   - Mayor's Permit status changes to "Pending"
   - Certificate of Registration still "Accepted" ✅

### Test 5: Gym Listing (3 minutes)
1. [ ] Admin approves all documents for test gym
2. [ ] Admin converts account to Gym Owner
3. [ ] Logout and login as different customer
4. [ ] Navigate to "Membership → Gyms"
5. [ ] **Expected**: 
   - Test Gym appears in listing ✅
   - Shows gym name, logo, address ✅
   - "Apply Membership" button visible ✅

### Test 6: Revenue Tracking (3 minutes)
1. [ ] Login as gym owner
2. [ ] Approve a membership application
3. [ ] Navigate to Dashboard
4. [ ] **Expected**:
   - Total Revenue updated ✅
   - Revenue Tracking shows breakdown:
     - Membership Revenue: ₱700 (or plan price)
     - Trainer Sessions: ₱0
     - Others: ₱0

### Test 7: Equipment Inventory (2 minutes)
1. [ ] Navigate to "Equipment → Inventory"
2. [ ] Add equipment with "Others" option
3. [ ] Enter custom equipment name
4. [ ] **Expected**: 
   - Equipment added successfully ✅
   - Custom name appears in dropdown ✅
5. [ ] Click delete button
6. [ ] **Expected**: Item fades out and disappears ✅

---

## 🔍 Database Verification

Run these queries to verify everything is working:

### Check Gym Details:
```sql
SELECT id, gym_name, gym_address, maintenance_count, trainer_count, status
FROM legal_documents
ORDER BY id DESC LIMIT 5;
```

### Check Document Statuses:
```sql
SELECT 
    id,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status
FROM legal_documents
ORDER BY id DESC LIMIT 5;
```

### Check Revenue Categories:
```sql
SELECT 
    category,
    COUNT(*) as count,
    SUM(amount) as total
FROM financial_records
WHERE record_type = 'revenue'
GROUP BY category;
```

### Check Verified Gyms:
```sql
SELECT id, gym_name, gym_address, status
FROM legal_documents
WHERE status = 'verified';
```

---

## 📋 Log Files to Monitor

### 1. Database Log (`app/logs/database.log`)
**Check for**:
- Transaction errors
- UPDATE failures
- Connection issues

**Expected**: Should be empty or minimal entries

### 2. Admin Actions Log (`app/logs/admin_actions.log`)
**Check for**:
- Document approvals
- Document flags
- Status updates

**Expected**: Should show all admin actions with timestamps

### 3. PHP Error Log
**Location**: Check your PHP configuration for error_log location

**Expected**: No critical errors

---

## 🐛 Troubleshooting

### Issue: Status doesn't persist after refresh
**Solution**:
1. Check `app/logs/database.log` for errors
2. Verify database connection in `app/config/config.php`
3. Run: `SHOW VARIABLES LIKE 'autocommit';` (should be ON)
4. Clear browser cache (Ctrl+Shift+Delete)

### Issue: Gym doesn't appear in listing
**Solution**:
1. Verify gym status is "verified" in database
2. Check all 4 documents are approved
3. Verify admin converted account to gym_owner role

### Issue: Revenue not categorized correctly
**Solution**:
1. Check `financial_records` table has `category` column
2. Verify membership approval code uses correct category
3. Check revenue breakdown query in FinancialRecord model

### Issue: Equipment dropdown doesn't update
**Solution**:
1. Check JavaScript console for errors
2. Verify AJAX request completes successfully
3. Clear browser cache

### Issue: File upload fails
**Solution**:
1. Check directory permissions (755 or 777)
2. Verify upload directories exist
3. Check PHP upload_max_filesize setting
4. Check file extension is allowed

---

## 🎯 Production Deployment

### 1. Backup Database
```bash
mysqldump -u root -p webdev > webdev_backup_$(date +%Y%m%d).sql
```

### 2. Upload Files
Upload these modified files to production:
- `app/core/Database.php`
- `app/models/LegalDocument.php`
- `app/models/FinancialRecord.php`
- `app/controllers/AdminController.php`
- `app/controllers/GymownerController.php`
- `app/controllers/MembershipController.php`
- `app/controllers/HomeController.php`
- `app/views/gymowner/apply.php`
- `app/views/membership/gyms.php`
- `app/views/dashboard/gymowner.php`

### 3. Run Migration
Execute `sql/add_gym_details_to_legal_documents.sql` on production database

### 4. Test Critical Features
- [ ] Admin approve/flag documents
- [ ] Status persists after refresh
- [ ] Gym owner sees updates
- [ ] Gym listing works
- [ ] Revenue tracking works

### 5. Monitor Logs
- Check `app/logs/database.log` for errors
- Check `app/logs/admin_actions.log` for activity
- Monitor PHP error log

---

## 📊 Success Metrics

After deployment, verify:
- [ ] 0 database errors in logs
- [ ] 100% status update persistence
- [ ] < 2 second page load time
- [ ] All admin actions logged
- [ ] Revenue correctly categorized
- [ ] Gym listing displays all verified gyms

---

## 🎉 Post-Deployment

### 1. User Training
- Train admins on document review process
- Train gym owners on application process
- Train staff on membership approval

### 2. Documentation
- Share `COMPLETE_IMPLEMENTATION_SUMMARY.md` with team
- Share `QUICK_START_TESTING.md` for quick reference
- Keep `DEPLOYMENT_CHECKLIST.md` for future deployments

### 3. Monitoring
- Set up daily log review
- Monitor database performance
- Track user feedback

---

## 📞 Support

If you encounter issues:
1. Check relevant log files
2. Review troubleshooting section above
3. Verify database migration completed
4. Check file permissions
5. Review `COMPLETE_IMPLEMENTATION_SUMMARY.md` for feature details

---

## ✅ Deployment Complete!

Once all tests pass:
- ✅ Database migration successful
- ✅ All features working correctly
- ✅ Logs show no errors
- ✅ Status updates persist
- ✅ Revenue tracking accurate
- ✅ Gym listing functional

**Your gym management system is ready for production use!** 🚀

---

**Last Updated**: April 30, 2026
**Version**: 2.0 (Complete Implementation)
