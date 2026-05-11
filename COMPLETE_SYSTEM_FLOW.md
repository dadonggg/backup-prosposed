# 🔄 Complete Payment & Service Management System Flow

## 📊 System Overview

```
┌─────────────────┐
│   GYM OWNER     │
│  Adds Services  │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────┐
│     gym_services table          │
│  - Regular Monthly: ₱700        │
│  - Student Monthly: ₱600        │
│  - Personal Training: ₱1,500    │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────┐
│    CUSTOMER     │
│ Applies for     │
│  Membership     │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────┐
│  Membership Application Form    │
│  - Select Service (dropdown)    │
│  - See Price (auto-calculated)  │
│  - Choose Payment Mode          │
│    • Cash (pay at gym)          │
│    • Online (PayMongo)          │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│  membership_applications table  │
│  - service_id                   │
│  - payment_amount               │
│  - payment_mode                 │
│  - payment_status: pending      │
│  - gym_owner_id                 │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────┐
│  ADMIN OFFICER  │
│  Reviews App    │
└────────┬────────┘
         │
         ├─── Assign Trainer (if needed)
         │
         ├─── Verify Application
         │
         ▼
┌─────────────────────────────────┐
│  Customer Pays                  │
│  - Cash: Pay at gym             │
│  - Online: PayMongo (Phase 3)   │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────┐
│  ADMIN OFFICER  │
│ Confirms Payment│
└────────┬────────┘
         │
         ▼
┌─────────────────────────────────┐
│  System Actions:                │
│  1. Generate membership code    │
│  2. Create gym_members record   │
│  3. Record revenue in           │
│     financial_records           │
│  4. Update payment_status: paid │
└────────┬────────────────────────┘
         │
         ▼
┌─────────────────┐
│   GYM OWNER     │
│ Sees Revenue in │
│    Dashboard    │
└─────────────────┘
```

## 🎯 Detailed Flow Breakdown

### 1️⃣ Gym Owner Setup (One-time)

**Page:** `index.php?r=gymowner/services`

**Actions:**
1. Login as gym owner
2. Click "Gym Services" in dashboard
3. Add services:
   - Service Name: "Regular Monthly Membership"
   - Member Price: ₱700
   - Non-Member Price: ₱800
4. Click "Add Service"

**Database:**
```sql
INSERT INTO gym_services (gym_owner_id, name, member_price, non_member_price)
VALUES (5, 'Regular Monthly Membership', 700.00, 800.00);
```

**Result:** Service is now available for customers to select

---

### 2️⃣ Customer Application

**Page:** `index.php?r=membership/gyms` → `index.php?r=membership/apply&gym_id=X`

**Actions:**
1. Login as customer
2. Click "Apply for Membership"
3. Select a gym from the list
4. Fill out form:
   - First Name, Last Name, Phone
   - **Select Service** (dropdown shows gym's services)
   - **Payment Mode** (Cash or Online)
5. Click "Submit Application"

**Database:**
```sql
INSERT INTO membership_applications 
(user_id, gym_owner_id, first_name, last_name, phone_number, 
 service_id, payment_type, payment_amount, payment_mode, payment_status, status)
VALUES 
(10, 5, 'John', 'Doe', '09123456789', 
 3, 'Regular Monthly Membership', 700.00, 'cash', 'pending', 'pending');
```

**Result:** Application is submitted and waiting for admin review

---

### 3️⃣ Admin Officer Review

**Page:** `index.php?r=admofficer/memberships` → `index.php?r=admofficer/review&id=X`

**Actions:**
1. Login as admin officer
2. Click "Membership Applications"
3. Click "Review" on an application
4. **Optional:** Assign trainer (if service includes training)
5. Click "Verify" button
6. Customer pays at gym (if cash mode)
7. Click "Confirm Payment & Generate Code"

**Database:**
```sql
-- Step 1: Verify
UPDATE membership_applications 
SET status = 'verified', reviewer_id = 2
WHERE id = 15;

-- Step 2: Confirm Payment
UPDATE membership_applications 
SET status = 'approved', payment_status = 'paid', paid_at = NOW()
WHERE id = 15;

-- Step 3: Create Member
INSERT INTO gym_members (user_id, membership_code, payment_type, payment_amount)
VALUES (10, 'GYM-ABC123', 'Regular Monthly Membership', 700.00);

-- Step 4: Record Revenue
INSERT INTO financial_records 
(gym_owner_id, record_type, description, amount, category)
VALUES 
(5, 'revenue', 'Membership Payment - John Doe', 700.00, 'Membership Revenue');
```

**Result:** 
- Membership approved
- Code generated
- Revenue recorded
- Customer notified

---

### 4️⃣ Gym Owner Dashboard

**Page:** `index.php?r=home/index` (auto-redirects to gym owner dashboard)

**What Gym Owner Sees:**

```
┌─────────────────────────────────────────────────────────┐
│  GYM OWNER DASHBOARD                                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ Total Budget │  │   Expenses   │  │Total Revenue │ │
│  │  ₱50,000.00  │  │  ₱15,000.00  │  │  ₱8,400.00   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                         │
│  ┌──────────────────────────────────────────────────┐  │
│  │  REVENUE TRACKING                                │  │
│  ├──────────────────────────────────────────────────┤  │
│  │  Membership Revenue:    ₱7,000.00               │  │
│  │  Trainer Sessions:      ₱1,200.00               │  │
│  │  Others:                ₱200.00                  │  │
│  └──────────────────────────────────────────────────┘  │
│                                                         │
│  ┌──────────────────────────────────────────────────┐  │
│  │  MONTHLY REVENUE BREAKDOWN                       │  │
│  ├──────────────────────────────────────────────────┤  │
│  │  Month        │ New Members │ Revenue            │  │
│  │  May 2026     │     10      │ ₱7,000.00         │  │
│  │  April 2026   │      8      │ ₱5,600.00         │  │
│  │  March 2026   │      5      │ ₱3,500.00         │  │
│  └──────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Database Query:**
```sql
-- Total Revenue
SELECT COALESCE(SUM(amount), 0) as total
FROM financial_records
WHERE gym_owner_id = 5 AND record_type = 'revenue';

-- Revenue Breakdown
SELECT category, COALESCE(SUM(amount), 0) as total
FROM financial_records
WHERE gym_owner_id = 5 AND record_type = 'revenue'
GROUP BY category;

-- Monthly Revenue
SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
       COUNT(*) as member_count,
       SUM(payment_amount) as total
FROM gym_members
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
GROUP BY month
ORDER BY month DESC;
```

---

## 🔑 Key Database Tables

### 1. `gym_services`
```sql
id | gym_owner_id | name                      | member_price | non_member_price
---|--------------|---------------------------|--------------|------------------
1  | 5            | Regular Monthly           | 700.00       | 800.00
2  | 5            | Student Monthly           | 600.00       | 700.00
3  | 5            | Personal Training Session | 1500.00      | 1800.00
```

### 2. `membership_applications`
```sql
id | user_id | gym_owner_id | service_id | payment_amount | payment_mode | payment_status | status
---|---------|--------------|------------|----------------|--------------|----------------|--------
15 | 10      | 5            | 1          | 700.00         | cash         | paid           | approved
16 | 11      | 5            | 3          | 1500.00        | online       | pending        | pending
```

### 3. `financial_records`
```sql
id | gym_owner_id | record_type | description                  | amount  | category
---|--------------|-------------|------------------------------|---------|------------------
50 | 5            | revenue     | Membership Payment - John    | 700.00  | Membership Revenue
51 | 5            | revenue     | Membership Payment - Jane    | 600.00  | Membership Revenue
52 | 5            | revenue     | Trainer Session - Mike       | 1500.00 | Trainer Sessions
```

### 4. `gym_members`
```sql
id | user_id | membership_code | payment_type              | payment_amount | created_at
---|---------|-----------------|---------------------------|----------------|------------
20 | 10      | GYM-ABC123      | Regular Monthly           | 700.00         | 2026-05-03
21 | 11      | GYM-XYZ789      | Personal Training Session | 1500.00        | 2026-05-03
```

---

## 🎨 User Interface Changes

### Before (Hardcoded Plans)
```
┌─────────────────────────────────────┐
│ Membership Plan *                   │
├─────────────────────────────────────┤
│ ▼ Student Monthly — ₱600            │
│   Regular Monthly — ₱700            │
│   With Personal Trainer — ₱1,500    │
└─────────────────────────────────────┘
```

### After (Dynamic Services)
```
┌─────────────────────────────────────┐
│ Select Service *                    │
├─────────────────────────────────────┤
│ ▼ — Select a service —              │
│   Regular Monthly — ₱700            │
│   Student Monthly — ₱600            │
│   Personal Training — ₱1,500        │
│   Group Classes — ₱500              │
│   (Loaded from gym_services table)  │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Amount to Pay: ₱700.00              │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│ Payment Mode *                      │
├─────────────────────────────────────┤
│ ▼ Cash Payment (Pay at Gym)         │
│   Online Payment (PayMongo)         │
└─────────────────────────────────────┘
```

---

## 📈 Revenue Flow

```
Customer Pays ₱700
       │
       ▼
Admin Officer Confirms
       │
       ▼
┌──────────────────────────────────┐
│  financial_records table         │
│  + ₱700 (Membership Revenue)     │
└──────────────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│  Gym Owner Dashboard             │
│  Total Revenue: ₱8,400           │
│  Membership Revenue: ₱7,000      │
│  Monthly Profit: ₱6,400          │
└──────────────────────────────────┘
```

---

## ✅ What's Working Now

- ✅ Gym owner adds services with custom names and prices
- ✅ Services appear in membership application form
- ✅ Customer selects service and sees calculated price
- ✅ Customer chooses payment mode (cash/online)
- ✅ Admin officer reviews and approves applications
- ✅ Admin officer can assign trainers
- ✅ Revenue is automatically recorded when payment is confirmed
- ✅ Revenue appears in gym owner dashboard
- ✅ Revenue is categorized (Membership Revenue, Trainer Sessions, Others)
- ✅ Monthly revenue breakdown shows last 6 months
- ✅ Monthly profit calculation (Revenue - Expenses)

---

## ⚠️ What's NOT Working Yet

- ❌ Online payment with PayMongo (Phase 3)
- ❌ Payment transactions table usage (Phase 4)
- ❌ Trainer filtering by gym owner (Phase 2)
- ❌ Automatic payment status update from PayMongo webhook (Phase 3)

---

## 🚀 Next Steps

### Option 1: Phase 2 - Trainer Assignment by Gym
- Add `gym_owner_id` to `employees` table
- Filter trainers by gym owner in admin review page
- Only show trainers hired by that gym owner

### Option 2: Phase 3 - PayMongo Integration
- Create PayMongo configuration page for gym owners
- Implement payment link creation
- Handle payment webhook
- Update payment status automatically
- Redirect to PayMongo when "Online Payment" is selected

### Option 3: Phase 4 - Payment Transactions
- Record all payments in `payment_transactions` table
- Show payment history to customers
- Show payment history to gym owners
- Generate payment receipts

---

## 📞 Support

If you encounter any issues:
1. Check `PHASE_1_SERVICE_PAYMENT_IMPLEMENTATION.md` for detailed instructions
2. Run `RUN_THIS_SQL_FIRST.sql` in phpMyAdmin
3. Hard refresh browser with `Ctrl + Shift + R`
4. Check browser console for JavaScript errors
5. Check database for missing columns or tables

---

**Last Updated:** May 3, 2026
**Status:** Phase 1 Complete ✅
**Next:** Phase 2 or Phase 3 (your choice!)
