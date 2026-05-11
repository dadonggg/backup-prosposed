# 🧪 Testing Guide - Phase 1: Service Selection & Payment System

## 📋 Pre-Testing Checklist

- [ ] Run `RUN_THIS_SQL_FIRST.sql` in phpMyAdmin
- [ ] Verify all columns were added successfully
- [ ] Hard refresh browser (`Ctrl + Shift + R`)
- [ ] Clear browser cache if needed

---

## 🧪 Test Case 1: Gym Owner Adds Services

### Steps:
1. Login with gym owner credentials
2. Go to Dashboard
3. Click "Gym Services" card
4. Fill out the form:
   - Service Name: `Regular Monthly Membership`
   - Description: `Access to all gym facilities`
   - Member Price: `700`
   - Non-Member Price: `800`
5. Click "Add Service"

### Expected Result:
- ✅ Success message: "Service added."
- ✅ Service appears in the table below
- ✅ Service shows name, prices, and action buttons

### Verify in Database:
```sql
SELECT * FROM gym_services WHERE gym_owner_id = [YOUR_GYM_OWNER_ID];
```

---

## 🧪 Test Case 2: Customer Applies for Membership

### Steps:
1. Login with customer credentials
2. Go to "Apply for Membership"
3. Select a gym from the list
4. Fill out the form:
   - First Name: `John`
   - Last Name: `Doe`
   - Phone: `09123456789`
   - **Select Service:** Choose "Regular Monthly Membership — ₱700.00"
   - **Payment Mode:** Choose "Cash Payment (Pay at Gym)"
5. Click "Submit Application"

### Expected Result:
- ✅ Price display shows: "Amount to Pay: ₱700.00"
- ✅ Success message: "Membership application submitted! Waiting for approval."
- ✅ Application status shows "Pending"

### Verify in Database:
```sql
SELECT * FROM membership_applications 
WHERE user_id = [YOUR_CUSTOMER_ID] 
ORDER BY id DESC LIMIT 1;
```

**Check these columns:**
- `service_id` should NOT be NULL
- `payment_amount` should be `700.00`
- `payment_mode` should be `cash`
- `payment_status` should be `pending`
- `gym_owner_id` should NOT be NULL
- `status` should be `pending`

---

## 🧪 Test Case 3: Admin Officer Reviews Application

### Steps:
1. Login with admin officer credentials
2. Go to "Membership Applications"
3. Find the application from Test Case 2
4. Click "Review"
5. **Optional:** Select a trainer from dropdown
6. Click "Verify"

### Expected Result:
- ✅ Success message: "Application verified!"
- ✅ Application status changes to "Verified"
- ✅ Customer receives notification

### Verify in Database:
```sql
SELECT * FROM membership_applications WHERE id = [APPLICATION_ID];
```

**Check:**
- `status` should be `verified`
- `reviewer_id` should be the admin officer's ID

---

## 🧪 Test Case 4: Admin Officer Confirms Payment

### Steps:
1. Still on the review page (or go back to it)
2. Application should show "Payment Confirmation" section
3. **Optional:** Assign trainer if not already assigned
4. Click "Confirm Payment & Generate Code"

### Expected Result:
- ✅ Success message: "Payment confirmed! Code: GYM-XXXXX"
- ✅ Application status changes to "Approved"
- ✅ Membership code is generated
- ✅ Customer receives notification with code

### Verify in Database:

**Check membership_applications:**
```sql
SELECT * FROM membership_applications WHERE id = [APPLICATION_ID];
```
- `status` should be `approved`
- `payment_status` should be `paid`
- `paid_at` should have a timestamp

**Check gym_members:**
```sql
SELECT * FROM gym_members WHERE user_id = [CUSTOMER_ID] ORDER BY id DESC LIMIT 1;
```
- New record should exist
- `membership_code` should be generated (e.g., GYM-ABC123)
- `payment_amount` should be `700.00`

**Check financial_records (IMPORTANT!):**
```sql
SELECT * FROM financial_records 
WHERE gym_owner_id = [GYM_OWNER_ID] 
AND record_type = 'revenue' 
ORDER BY id DESC LIMIT 1;
```
- New revenue record should exist
- `description` should mention the customer's name
- `amount` should be `700.00`
- `category` should be `Membership Revenue`

---

## 🧪 Test Case 5: Gym Owner Sees Revenue

### Steps:
1. Login with gym owner credentials
2. Go to Dashboard (should load automatically)
3. Look at the financial summary cards
4. Look at "Revenue Tracking" section
5. Look at "Monthly Revenue Breakdown" table

### Expected Result:
- ✅ "Total Revenue" card shows updated amount (includes ₱700)
- ✅ "Revenue Tracking" section shows:
  - Membership Revenue: ₱700.00 (or more if multiple members)
- ✅ "Monthly Revenue Breakdown" shows current month with:
  - New Members: 1 (or more)
  - Revenue: ₱700.00 (or more)

### Verify in Database:
```sql
-- Total Revenue
SELECT COALESCE(SUM(amount), 0) as total_revenue
FROM financial_records
WHERE gym_owner_id = [GYM_OWNER_ID] AND record_type = 'revenue';

-- Revenue Breakdown
SELECT category, COALESCE(SUM(amount), 0) as total
FROM financial_records
WHERE gym_owner_id = [GYM_OWNER_ID] AND record_type = 'revenue'
GROUP BY category;
```

---

## 🧪 Test Case 6: Service Price Updates Dynamically

### Steps:
1. Login as customer
2. Go to membership application form
3. Select different services from the dropdown
4. Watch the "Amount to Pay" display

### Expected Result:
- ✅ When you select "Regular Monthly — ₱700", display shows "₱700.00"
- ✅ When you select "Student Monthly — ₱600", display shows "₱600.00"
- ✅ When you select "Personal Training — ₱1,500", display shows "₱1,500.00"
- ✅ Price updates instantly without page reload

---

## 🧪 Test Case 7: Payment Mode Selection

### Steps:
1. Login as customer
2. Go to membership application form
3. Try selecting different payment modes

### Expected Result:
- ✅ "Cash Payment (Pay at Gym)" option is available
- ✅ "Online Payment (PayMongo)" option is available
- ✅ Can switch between options
- ⚠️ Note: Online payment doesn't work yet (Phase 3)

---

## 🧪 Test Case 8: Multiple Services for Same Gym

### Steps:
1. Login as gym owner
2. Add multiple services:
   - Regular Monthly — ₱700
   - Student Monthly — ₱600
   - Personal Training — ₱1,500
   - Group Classes — ₱500
3. Logout and login as customer
4. Go to membership application
5. Check service dropdown

### Expected Result:
- ✅ All 4 services appear in dropdown
- ✅ Each service shows correct price
- ✅ Selecting each service updates the price display

---

## 🧪 Test Case 9: Resubmit Application with Service

### Steps:
1. Login as admin officer
2. Review an application
3. Click "Resubmit" and add feedback
4. Logout and login as customer
5. Go to membership application page
6. Should see resubmit form
7. Change service selection
8. Click "Resubmit Application"

### Expected Result:
- ✅ Resubmit form shows with current data
- ✅ Service dropdown shows all services
- ✅ Can select different service
- ✅ Price updates when service changes
- ✅ Application resubmits successfully

---

## 🧪 Test Case 10: Revenue Accumulation

### Steps:
1. Create 3 membership applications (as customer)
2. Approve all 3 (as admin officer)
3. Check gym owner dashboard

### Expected Result:
- ✅ Total Revenue shows sum of all 3 payments
- ✅ Membership Revenue category shows sum of all 3
- ✅ Monthly Revenue Breakdown shows:
  - New Members: 3
  - Revenue: [sum of all 3 payments]

### Verify in Database:
```sql
SELECT COUNT(*) as total_members, SUM(payment_amount) as total_revenue
FROM gym_members
WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01');
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "No services available" in dropdown
**Cause:** Gym owner hasn't added services yet
**Solution:** Login as gym owner and add services first

### Issue 2: Price doesn't update when selecting service
**Cause:** JavaScript error or browser cache
**Solution:** 
1. Hard refresh: `Ctrl + Shift + R`
2. Check browser console for errors (F12)
3. Clear browser cache

### Issue 3: Revenue doesn't appear in dashboard
**Cause:** Payment not confirmed by admin officer
**Solution:** 
1. Check `membership_applications.payment_status` should be `paid`
2. Check `financial_records` table for revenue entry
3. Make sure admin officer clicked "Confirm Payment"

### Issue 4: SQL error when running migration
**Cause:** Columns already exist or foreign key constraint fails
**Solution:**
1. Check if columns already exist:
   ```sql
   DESCRIBE membership_applications;
   ```
2. If columns exist, skip those ALTER statements
3. If foreign key fails, check if `gym_services` table exists

### Issue 5: Application doesn't submit
**Cause:** Missing required fields or validation error
**Solution:**
1. Check browser console for errors
2. Make sure service is selected
3. Make sure payment mode is selected
4. Check all required fields are filled

---

## ✅ Final Verification Checklist

After completing all test cases:

- [ ] Gym owner can add services
- [ ] Services appear in membership form
- [ ] Customer can select service
- [ ] Price calculates correctly
- [ ] Payment mode selection works
- [ ] Application submits successfully
- [ ] Admin officer can review
- [ ] Admin officer can assign trainer
- [ ] Admin officer can confirm payment
- [ ] Revenue is recorded in database
- [ ] Revenue appears in gym owner dashboard
- [ ] Revenue breakdown shows "Membership Revenue"
- [ ] Monthly revenue shows correct data
- [ ] Multiple applications accumulate revenue correctly

---

## 📊 Database Verification Queries

Run these queries to verify everything is working:

```sql
-- Check if columns were added
DESCRIBE membership_applications;

-- Check if tables were created
SHOW TABLES LIKE 'paymongo_config';
SHOW TABLES LIKE 'payment_transactions';

-- Check gym services
SELECT * FROM gym_services;

-- Check membership applications with service data
SELECT id, user_id, service_id, payment_amount, payment_mode, payment_status, status
FROM membership_applications
ORDER BY id DESC LIMIT 5;

-- Check revenue records
SELECT * FROM financial_records
WHERE record_type = 'revenue'
ORDER BY id DESC LIMIT 5;

-- Check gym members
SELECT * FROM gym_members
ORDER BY id DESC LIMIT 5;

-- Revenue summary by gym owner
SELECT gym_owner_id, 
       COUNT(*) as total_transactions,
       SUM(amount) as total_revenue
FROM financial_records
WHERE record_type = 'revenue'
GROUP BY gym_owner_id;
```

---

## 🎉 Success Criteria

Phase 1 is successfully implemented if:

1. ✅ All SQL migrations run without errors
2. ✅ Gym owner can add services
3. ✅ Services appear in membership form
4. ✅ Customer can apply with service selection
5. ✅ Admin officer can approve applications
6. ✅ Revenue is automatically recorded
7. ✅ Revenue appears in gym owner dashboard
8. ✅ All test cases pass

---

**Happy Testing! 🚀**

If you encounter any issues not covered here, check:
- `PHASE_1_SERVICE_PAYMENT_IMPLEMENTATION.md` for detailed implementation info
- `COMPLETE_SYSTEM_FLOW.md` for system flow diagrams
- Browser console (F12) for JavaScript errors
- PHP error logs for server-side errors
