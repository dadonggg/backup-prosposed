# Quick Start: Testing the Bug Fix

## 🎯 What Was Fixed?

**Problem:** When admin approved or flagged documents, the status changes didn't save to the database.

**Solution:** Added transaction management to ensure all database updates are properly committed.

---

## ✅ Quick Test (5 minutes)

### Step 1: Start Your Server
```bash
# Start XAMPP/WAMP or your web server
# Make sure MySQL is running
```

### Step 2: Login as Admin
1. Go to: `http://localhost/webdev/`
2. Login with admin credentials
3. Navigate to: **Admin → Legal Reviews**

### Step 3: Test Approve Function
1. Click **"Review"** on any pending application
2. Find "Certificate of Registration" section
3. Select **"Approved"** from dropdown
4. Click **"Update Status"**
5. ✅ You should see: "Document approved and gym owner notified"

### Step 4: Verify It Persisted
1. **Press F5** to refresh the page
2. ✅ Certificate of Registration should still show **"Approved"** (green checkmark)
3. ❌ Before the fix, it would revert to "Pending"

### Step 5: Test Flag Function
1. Find "Mayor's Permit" section
2. Select **"Flagged"** from dropdown
3. Add comment: "Document expired"
4. Click **"Update Status"**
5. ✅ You should see: "Document flagged and gym owner notified"
6. **Press F5** to refresh
7. ✅ Mayor's Permit should still show **"Flagged"** with your comment

### Step 6: Check Gym Owner View
1. Logout from admin
2. Login as the gym owner (the customer who submitted documents)
3. Navigate to: **Gym Owner → Apply**
4. ✅ You should see:
   - Certificate of Registration: **"Accepted"** (green badge)
   - Mayor's Permit: **"Rejected"** (red badge) with comment "Document expired"
   - Resubmit button appears for Mayor's Permit

---

## 🔍 Verify in Database

Run this SQL query in phpMyAdmin:

```sql
SELECT 
    id,
    cert_registration_status,
    mayors_permit_status,
    business_name_cert_status,
    fire_safety_cert_status,
    status,
    admin_feedback
FROM legal_documents
ORDER BY id DESC
LIMIT 1;
```

**Expected Result:**
- `cert_registration_status`: `approved`
- `mayors_permit_status`: `flagged`
- `status`: `resubmit` (because one doc is flagged)
- `admin_feedback`: Contains the flagged document comment

---

## 📋 Check Logs

### Database Log
Location: `app/logs/database.log`

Should contain:
- Any database errors (hopefully none!)
- Transaction failures (hopefully none!)

### Admin Actions Log
Location: `app/logs/admin_actions.log`

Should contain:
```
[2026-04-30 XX:XX:XX] Admin ID 1: approve_document on target ID X - cert_registration
[2026-04-30 XX:XX:XX] Admin ID 1: flag_document on target ID X - mayors_permit
```

---

## ✅ Success Checklist

- [ ] Admin can approve documents
- [ ] Status persists after page refresh
- [ ] Admin can flag documents with comments
- [ ] Flagged status persists after page refresh
- [ ] Gym owner sees approved documents (green badge)
- [ ] Gym owner sees flagged documents (red badge)
- [ ] Gym owner sees admin comments for flagged docs
- [ ] Resubmit button appears only for flagged docs
- [ ] Database shows correct status values
- [ ] Logs are being created

---

## 🐛 Troubleshooting

### If status doesn't persist:

1. **Check database connection:**
   - Open phpMyAdmin
   - Verify `webdev` database exists
   - Verify `legal_documents` table has status columns

2. **Check file permissions:**
   ```bash
   # Make sure app/logs directory is writable
   chmod -R 755 app/logs  # Linux/Mac
   # Or set permissions in Windows Explorer
   ```

3. **Check PHP errors:**
   - Look in `app/logs/database.log`
   - Check PHP error log
   - Enable error display (for testing only):
     ```php
     // Add to top of public/index.php
     ini_set('display_errors', '1');
     error_reporting(E_ALL);
     ```

4. **Clear browser cache:**
   - Press `Ctrl+Shift+Delete`
   - Clear cached images and files
   - Or try incognito/private window

### If notifications don't appear:

1. Check `notifications` table exists in database
2. Check `app/logs/admin_actions.log` for notification failures
3. Verify gym owner user ID matches in database

---

## 🎉 What's Next?

Once testing is complete and everything works:

1. **Deploy to Production**
   - Backup database first!
   - Upload modified files
   - Test in production environment

2. **Additional Features** (Optional)
   - Staff application system
   - Trainer assignment
   - Financial dashboard
   - Payment integration (PayMongo)
   - Equipment inventory

3. **Monitor Logs**
   - Check `app/logs/database.log` daily
   - Check `app/logs/admin_actions.log` for audit trail
   - Set up log rotation if needed

---

## 📞 Need Help?

If you encounter any issues:

1. Check `PHASE_1_COMPLETE.md` for detailed information
2. Check `IMPLEMENTATION_STATUS.md` for overall progress
3. Review log files in `app/logs/`
4. Check database directly in phpMyAdmin

---

## 🎯 Key Files Modified

- `app/core/Database.php` - Transaction management
- `app/models/LegalDocument.php` - Fixed write methods
- `app/controllers/AdminController.php` - Error handling

**All changes are backward compatible and safe to deploy!** ✅
