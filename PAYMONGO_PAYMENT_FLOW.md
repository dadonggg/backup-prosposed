# 💳 PayMongo Payment Flow - Complete Guide

## 🔄 The Complete Flow

```
1. CUSTOMER applies for membership
   ├─ Selects service (e.g., Regular Monthly - ₱700)
   ├─ Chooses payment mode: Cash OR Online
   └─ Submits application (Status: PENDING)
        │
        ▼
2. ADMIN OFFICER reviews application
   ├─ Assigns trainer (if needed)
   ├─ Clicks "VERIFY" button
   └─ Application status changes to: VERIFIED
        │
        ▼
3. CUSTOMER sees payment options
   │
   ├─ IF payment mode = CASH:
   │   └─ Message: "Please pay at the gym"
   │
   └─ IF payment mode = ONLINE:
       └─ PayMongo "Pay Now" button appears ✨
            │
            ▼
4. CUSTOMER clicks "Pay Now via PayMongo"
   ├─ Redirected to PayMongo checkout page
   ├─ Enters card details
   ├─ Completes payment
   └─ Payment status updates automatically
        │
        ▼
5. ADMIN OFFICER confirms payment
   ├─ Sees payment status: PAID
   ├─ Clicks "Confirm Payment & Generate Code"
   └─ Membership APPROVED ✅
```

---

## 📍 When Does PayMongo Appear?

### ❌ PayMongo Does NOT Appear When:
- Application status is "pending" (waiting for admin review)
- Application status is "rejected"
- Payment mode is "cash"
- PayMongo is not configured by gym owner

### ✅ PayMongo APPEARS When:
1. **Application status = "verified"** (admin officer clicked "Verify")
2. **Payment mode = "online"** (customer selected online payment)
3. **PayMongo is configured** (gym owner added API keys)

---

## 🎯 Step-by-Step Example

### Step 1: Customer Applies
**Page:** `index.php?r=membership/apply&gym_id=1`

Customer fills form:
- Name: John Doe
- Service: Regular Monthly - ₱700
- **Payment Mode: Online Payment (PayMongo)** ← Important!
- Clicks "Submit Application"

**Result:** Application status = "pending"

---

### Step 2: Admin Officer Verifies
**Page:** `index.php?r=admofficer/review&id=15`

Admin officer:
- Reviews application
- Assigns trainer (optional)
- Clicks **"Verify"** button

**Result:** Application status = "verified"

---

### Step 3: Customer Sees PayMongo Button
**Page:** `index.php?r=membership/apply`

Customer refreshes page and sees:

```
┌─────────────────────────────────────────────────┐
│ ✓ Application Verified – Awaiting Payment      │
├─────────────────────────────────────────────────┤
│                                                 │
│ Your application has been verified.             │
│ Please complete your payment online.            │
│                                                 │
│ Payment Details:                                │
│ Service: Regular Monthly                        │
│ Amount Due: ₱700.00                            │
│ Payment Mode: Online Payment (PayMongo)         │
│                                                 │
│ ┌─────────────────────────────────────────┐   │
│ │  💳 Pay Now via PayMongo                │   │
│ └─────────────────────────────────────────┘   │
│                                                 │
│ 🔒 Secure payment powered by PayMongo          │
└─────────────────────────────────────────────────┘
```

---

### Step 4: Customer Pays via PayMongo
Customer clicks "Pay Now via PayMongo":
- Redirected to PayMongo checkout page
- Enters card details:
  - **Test Card:** 4343 4343 4343 4345
  - **Expiry:** 12/25
  - **CVC:** 123
- Completes payment
- Redirected back to application page

**Result:** Payment status = "paid"

---

### Step 5: Admin Officer Confirms
**Page:** `index.php?r=admofficer/review&id=15`

Admin officer:
- Sees payment status: "PAID"
- Clicks **"Confirm Payment & Generate Code"**

**Result:** 
- Membership approved
- Code generated (e.g., GYM-ABC123)
- Revenue recorded in gym owner dashboard

---

## 🔧 Technical Implementation

### What Happens Behind the Scenes:

#### When Admin Clicks "Verify":
```php
// In AdmofficerController.php
$appModel->updateStatus($id, 'verified', $feedback, (int)$user['id']);
// Status changes from "pending" to "verified"
```

#### When Customer Views Application:
```php
// In MembershipController.php
if ($existing['status'] === 'verified' && $existing['payment_mode'] === 'online') {
    // Generate PayMongo payment link
    $paymongoLink = $this->generatePayMongoLink($existing, $gymOwnerId);
}
```

#### PayMongo API Call:
```php
// Create payment link
POST https://api.paymongo.com/v1/links
{
    "data": {
        "attributes": {
            "amount": 70000,  // ₱700 in centavos
            "description": "Gym Membership - Regular Monthly",
            "remarks": "membership_app_15"
        }
    }
}

// Response:
{
    "data": {
        "id": "link_abc123",
        "attributes": {
            "checkout_url": "https://checkout.paymongo.com/..."
        }
    }
}
```

---

## 🎨 UI Changes

### Before Verification (Status: Pending):
```
┌─────────────────────────────────────┐
│ ⏳ Membership Application Pending  │
│                                     │
│ Your application is waiting for     │
│ approval.                           │
└─────────────────────────────────────┘
```

### After Verification - Cash Mode:
```
┌─────────────────────────────────────┐
│ ✓ Application Verified              │
│                                     │
│ Please complete your payment at     │
│ the gym.                            │
│                                     │
│ Amount Due: ₱700.00                │
│ Payment Mode: Cash (Pay at Gym)     │
└─────────────────────────────────────┘
```

### After Verification - Online Mode:
```
┌─────────────────────────────────────┐
│ ✓ Application Verified              │
│                                     │
│ Please complete your payment online.│
│                                     │
│ Amount Due: ₱700.00                │
│ Payment Mode: Online Payment        │
│                                     │
│ ┌─────────────────────────────────┐ │
│ │  💳 Pay Now via PayMongo        │ │
│ └─────────────────────────────────┘ │
│                                     │
│ 🔒 Secure payment powered by PayMongo│
└─────────────────────────────────────┘
```

---

## ⚠️ Troubleshooting

### Problem: PayMongo button doesn't appear
**Check:**
1. ✅ Application status is "verified" (not "pending")
2. ✅ Payment mode is "online" (not "cash")
3. ✅ PayMongo is configured by gym owner
4. ✅ Gym owner's PayMongo config is "active"

**Solution:**
```sql
-- Check application status and payment mode
SELECT id, status, payment_mode, payment_status 
FROM membership_applications 
WHERE id = [APPLICATION_ID];

-- Check PayMongo configuration
SELECT * FROM paymongo_config WHERE gym_owner_id = [GYM_OWNER_ID];
```

### Problem: "Online payment is being set up" message
**Cause:** Gym owner hasn't configured PayMongo yet

**Solution:**
1. Login as gym owner
2. Go to `index.php?r=gymowner/paymongo`
3. Insert API keys
4. Enable PayMongo

### Problem: PayMongo link doesn't work
**Check:**
1. ✅ API keys are correct
2. ✅ Using test keys for test mode
3. ✅ Amount is greater than 0

**Debug:**
```php
// Check error logs
tail -f app/logs/database.log
```

---

## 🧪 Testing the Flow

### Test Case: Complete Online Payment Flow

1. **Setup:**
   - Run `insert_paymongo_keys.php` to configure PayMongo
   - Make sure you have a gym owner and services set up

2. **As Customer:**
   - Apply for membership
   - Select service: "Regular Monthly - ₱700"
   - Select payment mode: "Online Payment (PayMongo)"
   - Submit application

3. **As Admin Officer:**
   - Go to membership applications
   - Click "Review" on the application
   - Click "Verify" button

4. **As Customer:**
   - Refresh membership application page
   - You should see "Pay Now via PayMongo" button
   - Click the button
   - You'll be redirected to PayMongo checkout

5. **On PayMongo Page:**
   - Enter test card: 4343 4343 4343 4345
   - Expiry: 12/25
   - CVC: 123
   - Complete payment

6. **As Admin Officer:**
   - Go back to review page
   - Payment status should show "paid"
   - Click "Confirm Payment & Generate Code"

7. **Verify:**
   - Membership should be approved
   - Code should be generated
   - Revenue should appear in gym owner dashboard

---

## 📊 Database Changes

### `membership_applications` table:
```sql
payment_mode ENUM('cash','online')      -- Customer's choice
payment_status ENUM('pending','paid')   -- Updated by PayMongo
paymongo_payment_id VARCHAR(255)        -- PayMongo link ID
```

### Flow in Database:
```sql
-- 1. Customer applies
INSERT INTO membership_applications 
(status, payment_mode, payment_status)
VALUES ('pending', 'online', 'pending');

-- 2. Admin verifies
UPDATE membership_applications 
SET status = 'verified' 
WHERE id = 15;

-- 3. PayMongo link created
UPDATE membership_applications 
SET paymongo_payment_id = 'link_abc123' 
WHERE id = 15;

-- 4. Customer pays (webhook updates this)
UPDATE membership_applications 
SET payment_status = 'paid', paid_at = NOW() 
WHERE id = 15;

-- 5. Admin confirms
UPDATE membership_applications 
SET status = 'approved' 
WHERE id = 15;
```

---

## ✅ Success Checklist

- [ ] PayMongo keys are configured
- [ ] Customer can select "Online Payment" mode
- [ ] Admin officer can verify applications
- [ ] PayMongo button appears after verification
- [ ] Clicking button redirects to PayMongo
- [ ] Test payment completes successfully
- [ ] Payment status updates to "paid"
- [ ] Admin can confirm payment
- [ ] Revenue is recorded
- [ ] Membership is approved

---

## 🎉 Summary

**The Flow:**
1. Customer applies with "Online Payment" mode
2. Admin officer **verifies** application
3. **PayMongo button appears** for customer
4. Customer pays via PayMongo
5. Admin officer confirms payment
6. Membership approved!

**Key Point:** PayMongo only appears **AFTER** admin officer clicks "Verify"!

---

**Last Updated:** May 4, 2026  
**Status:** PayMongo Integration Complete ✅  
**Next:** Test the complete flow!
