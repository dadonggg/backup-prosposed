# 💳 PayMongo Setup Guide

## 🎯 Your PayMongo API Keys

I've received your PayMongo test API keys:

- **Public Key:** `pk_test_YOUR_PUBLIC_KEY`
- **Secret Key:** `sk_test_YOUR_SECRET_KEY`
- **Mode:** Test Mode (safe for development)

---

## 🚀 Quick Setup (2 Methods)

### Method 1: Using SQL (Fastest)

1. **Open phpMyAdmin**
2. **Select your database:** `webdev`
3. **Click SQL tab**
4. **Copy and paste** the contents of `QUICK_INSERT_PAYMONGO.sql`
5. **Click Go**
6. ✅ Done! Keys are now configured

### Method 2: Using the Web Interface

1. **Login** as gym owner
2. **Go to Dashboard**
3. **Click** "Configure PayMongo" card (blue card with credit card icon)
4. **Paste your keys:**
   - Public Key: `pk_test_YOUR_PUBLIC_KEY`
   - Secret Key: `sk_test_YOUR_SECRET_KEY`
5. **Check** "Enable online payments"
6. **Click** "Save Configuration"
7. ✅ Done!

---

## 📍 Where to Find PayMongo Configuration

### In Gym Owner Dashboard:

```
Dashboard
  └─ Plans & Services Quick Links
      └─ PayMongo Setup (Blue Card)
          └─ Click "Configure PayMongo"
```

**Direct URL:** `index.php?r=gymowner/paymongo`

---

## ✅ Verify Installation

After inserting the keys, verify they're working:

### Check in Database:
```sql
SELECT * FROM paymongo_config;
```

**Expected Result:**
```
id | gym_owner_id | public_key                        | secret_key                        | is_active
---|--------------|-----------------------------------|-----------------------------------|----------
1  | 5            | pk_test_YOUR_PUBLIC_KEY | sk_test_YOUR_SECRET_KEY | 1
```

### Check in Web Interface:
1. Login as gym owner
2. Go to `index.php?r=gymowner/paymongo`
3. You should see:
   - ✅ "Current Configuration - Active" (green header)
   - ✅ Public Key displayed
   - ✅ Secret Key masked (shows `****...NzzH`)
   - ✅ Status: "Active - Online payments enabled"

---

## 🧪 Test PayMongo Integration

### Test Card Details (Test Mode Only):

**Card Number:** `4343 4343 4343 4345`  
**Expiry Date:** Any future date (e.g., `12/25`)  
**CVC:** Any 3 digits (e.g., `123`)  
**Name:** Any name

### Test Flow:

1. **As Customer:**
   - Apply for membership
   - Select a service
   - Choose "Online Payment (PayMongo)"
   - Submit application

2. **Payment Page:**
   - Enter test card details above
   - Complete payment

3. **Expected Result:**
   - Payment succeeds
   - Application status updates to "paid"
   - Revenue recorded in gym owner dashboard

---

## 🔒 Security Notes

### Your Keys Are Safe Because:

1. ✅ **Test Mode Keys:** These are test keys (`pk_test_` / `sk_test_`)
   - No real money is processed
   - Safe for development and testing
   - Can be shared for development purposes

2. ✅ **Stored Securely:** 
   - Secret key is stored in database (not in code)
   - Secret key is masked in the UI (shows `****...NzzH`)
   - Only gym owner can view/edit their keys

3. ✅ **Easy to Change:**
   - Can update keys anytime via web interface
   - Can disable PayMongo without deleting keys
   - Can delete configuration completely

### When to Switch to Live Keys:

⚠️ **DO NOT use live keys yet!** Wait until:
- [ ] All testing is complete
- [ ] System is working perfectly
- [ ] You're ready to accept real payments
- [ ] Your PayMongo account is fully verified

**Live keys start with:** `pk_live_` and `sk_live_`

---

## 🎨 What You'll See in the UI

### PayMongo Configuration Page:

```
┌─────────────────────────────────────────────────────────┐
│  PayMongo Configuration                                 │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌───────────────────────────────────────────────────┐ │
│  │  Current Configuration - Active                   │ │
│  ├───────────────────────────────────────────────────┤ │
│  │  Public Key:  pk_test_YOUR_PUBLIC_KEY   │ │
│  │               [Test Mode]                         │ │
│  │                                                   │ │
│  │  Secret Key:  ****************************NzzH   │ │
│  │               (hidden for security)               │ │
│  │                                                   │ │
│  │  Status:      ✓ Active - Online payments enabled │ │
│  │                                                   │ │
│  │  [Disable PayMongo] [Update Keys] [Delete]       │ │
│  └───────────────────────────────────────────────────┘ │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Membership Application Form (Customer View):

```
┌─────────────────────────────────────────────────────────┐
│  Select Service *                                       │
│  ▼ Regular Monthly — ₱700.00                           │
├─────────────────────────────────────────────────────────┤
│  Amount to Pay: ₱700.00                                │
├─────────────────────────────────────────────────────────┤
│  Payment Mode *                                         │
│  ○ Cash Payment (Pay at Gym)                           │
│  ● Online Payment (PayMongo)  ← NEW!                   │
└─────────────────────────────────────────────────────────┘
```

---

## 🔧 Troubleshooting

### Problem: "Please run RUN_THIS_SQL_FIRST.sql"
**Solution:** The `paymongo_config` table doesn't exist
- Run `RUN_THIS_SQL_FIRST.sql` in phpMyAdmin first
- Then run `QUICK_INSERT_PAYMONGO.sql`

### Problem: "Invalid public key format"
**Solution:** Make sure the key starts with `pk_test_` or `pk_live_`
- Your key: `pk_test_YOUR_PUBLIC_KEY` ✓ Correct

### Problem: "Invalid secret key format"
**Solution:** Make sure the key starts with `sk_test_` or `sk_live_`
- Your key: `sk_test_YOUR_SECRET_KEY` ✓ Correct

### Problem: Can't find PayMongo configuration page
**Solution:** 
- Make sure you're logged in as gym owner
- Go to dashboard
- Look for "PayMongo Setup" card (blue card)
- Or go directly to: `index.php?r=gymowner/paymongo`

### Problem: Keys inserted but not showing in UI
**Solution:**
- Hard refresh browser: `Ctrl + Shift + R`
- Check database: `SELECT * FROM paymongo_config;`
- Make sure `gym_owner_id` matches your user ID

---

## 📊 Database Structure

### `paymongo_config` Table:

```sql
CREATE TABLE paymongo_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_owner_id INT NOT NULL,           -- Links to users.id
    public_key VARCHAR(255) NOT NULL,    -- pk_test_... or pk_live_...
    secret_key VARCHAR(255) NOT NULL,    -- sk_test_... or sk_live_...
    is_active TINYINT(1) DEFAULT 1,      -- 1 = enabled, 0 = disabled
    created_at DATETIME,
    updated_at DATETIME
);
```

### Your Data:

```sql
INSERT INTO paymongo_config VALUES (
    NULL,                                    -- Auto-increment ID
    [YOUR_GYM_OWNER_ID],                    -- Your user ID
    'pk_test_YOUR_PUBLIC_KEY',   -- Public key
    'sk_test_YOUR_SECRET_KEY',   -- Secret key
    1,                                       -- Active
    NOW(),                                   -- Created now
    NOW()                                    -- Updated now
);
```

---

## 🎯 Next Steps After Setup

1. ✅ **Insert Keys** (using SQL or web interface)
2. ✅ **Verify Configuration** (check database and UI)
3. ⏳ **Test Payment Flow** (Phase 3 - not yet implemented)
4. ⏳ **Go Live** (switch to live keys when ready)

---

## 📞 Need Help?

### Files to Check:
- `QUICK_INSERT_PAYMONGO.sql` - Quick SQL insert script
- `insert_paymongo_keys.sql` - Manual SQL insert script
- `app/views/gymowner/paymongo.php` - Configuration page UI
- `app/models/PayMongoConfig.php` - Database model
- `app/controllers/GymownerController.php` - Controller logic

### Common Issues:
1. Table doesn't exist → Run `RUN_THIS_SQL_FIRST.sql`
2. Keys not showing → Hard refresh browser
3. Can't access page → Make sure you're logged in as gym owner
4. Wrong gym owner → Check `gym_owner_id` in database

---

## ✅ Success Checklist

After setup, verify:

- [ ] `paymongo_config` table exists
- [ ] Keys are inserted in database
- [ ] Can access `index.php?r=gymowner/paymongo`
- [ ] Configuration page shows "Active" status
- [ ] Public key is displayed correctly
- [ ] Secret key is masked (shows `****...NzzH`)
- [ ] "PayMongo Setup" card appears in dashboard
- [ ] Can toggle active/inactive status
- [ ] Can update keys via web interface

---

## 🎉 You're All Set!

Your PayMongo test keys are ready to use:

✅ **Public Key:** `pk_test_YOUR_PUBLIC_KEY`  
✅ **Secret Key:** `sk_test_YOUR_SECRET_KEY`  
✅ **Mode:** Test (safe for development)  
✅ **Status:** Ready to integrate

**Next:** Implement Phase 3 (PayMongo Payment Integration) to actually process payments!

---

**Last Updated:** May 4, 2026  
**Status:** Keys Configured ✅  
**Next:** Phase 3 - Payment Processing
